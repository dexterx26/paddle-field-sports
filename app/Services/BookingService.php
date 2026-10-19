<?php

namespace App\Services;

use App\Events\CourtSlotsUpdated;
use App\Models\Booking;
use App\Models\BookingSlot;
use App\Models\Court;
use App\Models\CreditTransaction;
use App\Models\User;
use App\Models\VenueSetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BookingService
{
    /**
     * Release all expired holds and broadcast updates
     */
    public function releaseExpiredHolds(): int
    {
        $expiredHolds = Booking::where('booking_status', 'held')
            ->where('held_until', '<=', now())
            ->with('slots')
            ->get();

        $count = 0;
        foreach ($expiredHolds as $booking) {
            // Safety check: if user paid in PayMongo while hold was ticking down, confirm instead of expiring
            if ($booking->payment_method === 'paymongo' && !empty($booking->paymongo_checkout_id)) {
                try {
                    $paymongo = app(PayMongoService::class);
                    $check = $paymongo->checkCheckoutSessionStatus($booking->paymongo_checkout_id);
                    if (!empty($check['paid'])) {
                        $this->confirmBooking($booking, null, 'paymongo');
                        continue;
                    }
                } catch (\Exception $e) {
                    Log::warning("PayMongo check failed during releaseExpiredHolds for {$booking->booking_reference}: " . $e->getMessage());
                }
            }

            $slotTimes = $booking->slots->pluck('slot_time')->toArray();
            $courtId = $booking->court_id;
            $date = $booking->booking_date->format('Y-m-d');

            $booking->update([
                'booking_status' => 'expired',
            ]);

            // Expire PayMongo checkout session so it can no longer be paid
            if ($booking->payment_method === 'paymongo' && !empty($booking->paymongo_checkout_id)) {
                try {
                    $paymongo = app(PayMongoService::class);
                    $paymongo->expireCheckoutSession($booking->paymongo_checkout_id);
                } catch (\Exception $e) {}
            }

            BookingSlot::where('booking_id', $booking->id)->update([
                'status' => 'released',
            ]);

            // Broadcast real-time release event to all listeners
            try {
                broadcast(new CourtSlotsUpdated(
                    $date,
                    $courtId,
                    $slotTimes,
                    'released',
                    null,
                    "Held timeslots on Court {$courtId} expired and are now available."
                ));
            } catch (\Exception $e) {
                Log::warning('Reverb broadcast error on releaseExpiredHolds: ' . $e->getMessage());
            }

            $count++;
        }

        // Clean up any orphan held slots whose held_until has passed
        $orphanSlots = BookingSlot::where('status', 'held')
            ->where('held_until', '<=', now())
            ->get();

        if ($orphanSlots->isNotEmpty()) {
            foreach ($orphanSlots->groupBy(fn($s) => $s->court_id . '_' . $s->date) as $group) {
                $first = $group->first();
                $slotTimes = $group->pluck('slot_time')->toArray();

                BookingSlot::whereIn('id', $group->pluck('id'))->update(['status' => 'released']);

                try {
                    broadcast(new CourtSlotsUpdated(
                        $first->date,
                        $first->court_id,
                        $slotTimes,
                        'released',
                        null,
                        "Held timeslots on Court {$first->court_id} expired and are now available."
                    ));
                } catch (\Exception $e) {}
            }
        }

        return $count;
    }

    /**
     * Get availability map for all active courts on a given date
     */
    public function getAvailability(string $date): array
    {
        $this->releaseExpiredHolds();

        $settings = VenueSetting::getSettings();
        $courts = Court::where('is_active', true)->orderBy('court_number')->get();

        // Fetch all non-released slots for this date
        $occupiedSlots = BookingSlot::whereDate('date', $date)
            ->whereIn('status', ['held', 'pending_approval', 'confirmed'])
            ->with(['booking' => function ($q) {
                $q->select('id', 'booking_reference', 'customer_name', 'customer_phone', 'booking_status', 'held_until');
            }])
            ->get()
            ->groupBy('court_id');

        $result = [];

        foreach ($courts as $court) {
            $courtOccupied = $occupiedSlots->get($court->id, collect())->keyBy('slot_time');
            $hours = [];

            // Per-court operating hours: defaults to venue settings if not set
            $courtStartHour = $court->start_hour;
            $courtEndHour = $court->end_hour;

            $todayStr = now()->toDateString();
            $curHourStr = now()->format('H:00');

            for ($h = $courtStartHour; $h < $courtEndHour; $h++) {
                $timeKey = sprintf('%02d:00', $h);
                $slot = $courtOccupied->get($timeKey);

                $isPast = false;
                if ($date < $todayStr) {
                    $isPast = true;
                } elseif ($date === $todayStr && $timeKey < $curHourStr) {
                    $isPast = true;
                }

                $displayTime = Carbon::createFromFormat('H:i', $timeKey)->format('g:i A');
                $nextHour = $h + 1;
                $endLabel = ($nextHour === 24) ? '12:00 AM' : Carbon::createFromFormat('H:i', sprintf('%02d:00', $nextHour))->format('g:i A');
                $displayRange = $displayTime . ' - ' . $endLabel;

                $startShort = Carbon::createFromFormat('H:i', $timeKey)->format('ga');
                $endShort = ($nextHour === 24) ? '12am' : Carbon::createFromFormat('H:i', sprintf('%02d:00', $nextHour))->format('ga');
                $shortRange = $startShort . ' - ' . $endShort;

                if ($slot) {
                    $isHeld = ($slot->status === 'held' && $slot->held_until && now()->lt($slot->held_until));
                    $isExpired = ($slot->status === 'held' && (!$slot->held_until || now()->gte($slot->held_until)));

                    if ($isExpired) {
                        $status = $isPast ? 'past' : 'available';
                        $remainingSeconds = 0;
                    } else {
                        $status = $slot->status; // 'held', 'pending_approval', 'confirmed'
                        $remainingSeconds = $isHeld ? max(0, (int) now()->diffInSeconds($slot->held_until, false)) : 0;
                    }

                    $hours[$timeKey] = [
                        'time' => $timeKey,
                        'display_time' => $displayTime,
                        'display_range' => $displayRange,
                        'short_range' => $shortRange,
                        'status' => $status,
                        'is_past' => $isPast,
                        'remaining_seconds' => $remainingSeconds,
                        'held_until' => $slot->held_until ? $slot->held_until->toISOString() : null,
                        'booking_reference' => $slot->booking ? $slot->booking->booking_reference : null,
                    ];
                } else {
                    $hours[$timeKey] = [
                        'time' => $timeKey,
                        'display_time' => $displayTime,
                        'display_range' => $displayRange,
                        'short_range' => $shortRange,
                        'status' => $isPast ? 'past' : 'available',
                        'is_past' => $isPast,
                        'remaining_seconds' => 0,
                        'held_until' => null,
                        'booking_reference' => null,
                    ];
                }
            }

            $result[] = [
                'court' => [
                    'id' => $court->id,
                    'name' => $court->name,
                    'court_number' => $court->court_number,
                    'type' => $court->type,
                    'surface_type' => $court->surface_type,
                    'price_per_hour' => (float) $court->price_per_hour,
                    'formatted_price' => $court->formatted_price,
                    'credits_per_hour' => (float) ($court->credits_per_hour ?: 1.0),
                    'formatted_credits_price' => (!empty($settings->credits_enabled)) ? $court->formatted_credits_price : null,
                    'max_players' => $court->max_players,
                    'display_image' => $court->display_image,
                    'opening_time' => $court->effective_opening_time,
                    'closing_time' => $court->effective_closing_time,
                    'operating_hours' => $court->operating_hours_label,
                ],
                'slots' => $hours,
            ];
        }

        return $result;
    }

    /**
     * Check if a set of slots is free and within court operating hours
     */
    public function areSlotsAvailable(int $courtId, string $date, array $slots): bool
    {
        $this->releaseExpiredHolds();

        $todayStr = now()->toDateString();
        $curHourStr = now()->format('H:00');

        // Do not allow reserving dates or timeslots that have already passed
        if ($date < $todayStr) {
            return false;
        }

        if ($date === $todayStr) {
            foreach ($slots as $slot) {
                if ($slot < $curHourStr) {
                    return false;
                }
            }
        }

        $court = Court::find($courtId);
        if ($court) {
            $courtStart = $court->start_hour;
            $courtEnd = $court->end_hour;
            foreach ($slots as $slot) {
                $h = (int) substr($slot, 0, 2);
                if ($h < $courtStart || $h >= $courtEnd) {
                    return false;
                }
            }
        }

        $conflict = BookingSlot::where('court_id', $courtId)
            ->whereDate('date', $date)
            ->whereIn('slot_time', $slots)
            ->where(function ($q) {
                $q->whereIn('status', ['pending_approval', 'confirmed'])
                  ->orWhere(function ($sub) {
                      $sub->where('status', 'held')
                          ->where('held_until', '>', now());
                  });
            })
            ->exists();

        return !$conflict;
    }

    /**
     * Hold slots for 2 minutes (Xendit flow)
     */
    public function holdSlots(int $courtId, string $date, array $slots, array $customerData, ?int $userId = null): array
    {
        $this->releaseExpiredHolds();

        if (!$this->areSlotsAvailable($courtId, $date, $slots)) {
            throw new \Exception('One or more selected timeslots have just been held or reserved by another customer. Please choose alternative slots.');
        }

        $court = Court::findOrFail($courtId);
        $settings = VenueSetting::getSettings();
        $holdingSeconds = $settings->holding_duration_seconds ?: 120; // 2 minutes
        $heldUntil = now()->addSeconds($holdingSeconds);

        sort($slots);
        $startTime = reset($slots);
        $lastSlot = end($slots);
        $endHour = (int) substr($lastSlot, 0, 2) + 1;
        $endTime = sprintf('%02d:00', $endHour);
        $totalHours = count($slots);

        // Snapshot pricing: use current court's price per hour
        $ratePerHour = $court->price_per_hour;
        $totalAmount = $ratePerHour * $totalHours;

        $reference = 'PF-' . date('Ymd') . '-' . strtoupper(Str::random(5));

        return DB::transaction(function () use (
            $reference, $userId, $customerData, $court, $date,
            $startTime, $endTime, $totalHours, $ratePerHour, $totalAmount,
            $slots, $heldUntil, $holdingSeconds
        ) {
            if (!$this->areSlotsAvailable($court->id, $date, $slots)) {
                throw new \Exception('One or more selected timeslots have just been held or reserved by another customer. Please choose alternative slots.');
            }

            $booking = Booking::create([
                'booking_reference' => $reference,
                'user_id' => $userId,
                'customer_name' => $customerData['name'],
                'customer_phone' => $customerData['phone'],
                'customer_email' => $customerData['email'] ?? null,
                'court_id' => $court->id,
                'booking_date' => $date,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'total_hours' => $totalHours,
                'rate_per_hour' => $ratePerHour, // Snapshot preserved
                'total_amount' => $totalAmount,
                'players_count' => $customerData['players_count'] ?? $court->max_players,
                'notes' => $customerData['notes'] ?? null,
                'payment_method' => 'xendit',
                'payment_status' => 'unpaid',
                'booking_status' => 'held',
                'held_until' => $heldUntil,
            ]);

            foreach ($slots as $time) {
                BookingSlot::create([
                    'booking_id' => $booking->id,
                    'court_id' => $court->id,
                    'date' => $date,
                    'slot_time' => $time,
                    'rate_per_hour' => $ratePerHour,
                    'status' => 'held',
                    'held_until' => $heldUntil,
                ]);
            }

            // Real-time broadcast: inform other users that these slots are held with countdown
            $holdDurationMins = max(1, (int) round($holdingSeconds / 60));
            try {
                broadcast(new CourtSlotsUpdated(
                    $date,
                    $court->id,
                    $slots,
                    'held',
                    $heldUntil->timestamp,
                    "Slots on {$court->name} are currently held for checkout ({$holdDurationMins}-minute window)."
                ))->toOthers();
            } catch (\Exception $e) {
                Log::warning('Reverb broadcast error: ' . $e->getMessage());
            }

            return [
                'booking' => $booking,
                'reference' => $reference,
                'held_until' => $heldUntil->toISOString(),
                'remaining_seconds' => $holdingSeconds,
                'total_amount' => $totalAmount,
                'formatted_amount' => '₱' . number_format($totalAmount, 2),
            ];
        });
    }

    /**
     * Submit booking with manual receipt upload
     */
    public function createManualReceiptBooking(
        int $courtId,
        string $date,
        array $slots,
        array $customerData,
        string $receiptPath,
        ?int $userId = null
    ): Booking {
        $this->releaseExpiredHolds();

        if (!$this->areSlotsAvailable($courtId, $date, $slots)) {
            throw new \Exception('One or more selected slots are no longer available. Please select different slots.');
        }

        $court = Court::findOrFail($courtId);

        sort($slots);
        $startTime = reset($slots);
        $lastSlot = end($slots);
        $endHour = (int) substr($lastSlot, 0, 2) + 1;
        $endTime = sprintf('%02d:00', $endHour);
        $totalHours = count($slots);

        // Snapshot pricing
        $ratePerHour = $court->price_per_hour;
        $totalAmount = $ratePerHour * $totalHours;

        $reference = 'PF-' . date('Ymd') . '-' . strtoupper(Str::random(5));

        return DB::transaction(function () use (
            $reference, $userId, $customerData, $court, $date,
            $startTime, $endTime, $totalHours, $ratePerHour, $totalAmount,
            $slots, $receiptPath
        ) {
            if (!$this->areSlotsAvailable($court->id, $date, $slots)) {
                throw new \Exception('One or more selected slots are no longer available. Please select different slots.');
            }

            $booking = Booking::create([
                'booking_reference' => $reference,
                'user_id' => $userId,
                'customer_name' => $customerData['name'],
                'customer_phone' => $customerData['phone'],
                'customer_email' => $customerData['email'] ?? null,
                'court_id' => $court->id,
                'booking_date' => $date,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'total_hours' => $totalHours,
                'rate_per_hour' => $ratePerHour,
                'total_amount' => $totalAmount,
                'players_count' => $customerData['players_count'] ?? $court->max_players,
                'notes' => $customerData['notes'] ?? null,
                'payment_method' => 'manual_receipt',
                'payment_status' => 'unpaid',
                'booking_status' => 'pending_approval',
                'receipt_image_path' => $receiptPath,
                'receipt_uploaded_at' => now(),
            ]);

            foreach ($slots as $time) {
                BookingSlot::create([
                    'booking_id' => $booking->id,
                    'court_id' => $court->id,
                    'date' => $date,
                    'slot_time' => $time,
                    'rate_per_hour' => $ratePerHour,
                    'status' => 'pending_approval',
                ]);
            }

            // Real-time broadcast
            try {
                broadcast(new CourtSlotsUpdated(
                    $date,
                    $court->id,
                    $slots,
                    'pending_approval',
                    null,
                    "Reservation pending owner approval on {$court->name}."
                ))->toOthers();
            } catch (\Exception $e) {
                Log::warning('Reverb broadcast error: ' . $e->getMessage());
            }

            return $booking;
        });
    }

    /**
     * Instant booking using User's prepaid Court Credits
     */
    public function createCreditsBooking(
        int $courtId,
        string $date,
        array $slots,
        array $customerData,
        User $user
    ): Booking {
        $this->releaseExpiredHolds();

        $settings = VenueSetting::getSettings();
        if (empty($settings->credits_enabled)) {
            throw new \Exception('Paying with Court Credits is currently disabled by venue administration. Please choose an alternative payment option.');
        }

        if (!$this->areSlotsAvailable($courtId, $date, $slots)) {
            throw new \Exception('One or more selected slots are no longer available. Please select different slots.');
        }

        $court = Court::findOrFail($courtId);
        $creditsRate = (float) ($court->credits_per_hour ?: 1.0);
        $totalHours = count($slots);
        $creditsNeeded = $creditsRate * $totalHours;

        if (!$user->hasEnoughCredits($creditsNeeded)) {
            $userCredits = (float) ($user->credits ?? 0);
            throw new \Exception("Insufficient credits. You need {$creditsNeeded} credits for this booking, but your balance is {$userCredits} credits. Please top up your wallet.");
        }

        sort($slots);
        $startTime = reset($slots);
        $lastSlot = end($slots);
        $endHour = (int) substr($lastSlot, 0, 2) + 1;
        $endTime = sprintf('%02d:00', $endHour);

        $ratePerHour = $court->price_per_hour;
        $totalAmount = $ratePerHour * $totalHours;
        $reference = 'PF-CR-' . date('Ymd') . '-' . strtoupper(Str::random(5));

        return DB::transaction(function () use (
            $reference, $user, $customerData, $court, $date,
            $startTime, $endTime, $totalHours, $ratePerHour, $totalAmount,
            $slots, $creditsNeeded
        ) {
            if (!$this->areSlotsAvailable($court->id, $date, $slots)) {
                throw new \Exception('One or more selected slots are no longer available. Please select different slots.');
            }

            // Deduct credits from user wallet
            $user->decrement('credits', $creditsNeeded);
            $newBalance = (float) $user->fresh()->credits;

            $booking = Booking::create([
                'booking_reference' => $reference,
                'user_id' => $user->id,
                'customer_name' => $customerData['name'] ?? $user->name,
                'customer_phone' => $customerData['phone'] ?? $user->phone ?? 'N/A',
                'customer_email' => $customerData['email'] ?? $user->email,
                'court_id' => $court->id,
                'booking_date' => $date,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'total_hours' => $totalHours,
                'rate_per_hour' => $ratePerHour,
                'total_amount' => $totalAmount,
                'credits_used' => $creditsNeeded,
                'players_count' => $customerData['players_count'] ?? $court->max_players,
                'notes' => trim(($customerData['notes'] ?? '') . " [Paid with {$creditsNeeded} Court Credits]"),
                'payment_method' => 'credits',
                'payment_status' => 'paid',
                'booking_status' => 'confirmed',
                'approved_by' => null,
                'approved_at' => now(),
            ]);

            // Record transaction in ledger
            CreditTransaction::create([
                'user_id' => $user->id,
                'type' => 'booking_payment',
                'amount_credits' => -$creditsNeeded,
                'balance_after' => $newBalance,
                'reference_id' => $booking->id,
                'reference_type' => Booking::class,
                'description' => "Court reservation ({$booking->booking_reference}) for {$court->name} ({$totalHours} hrs)",
            ]);

            foreach ($slots as $time) {
                BookingSlot::create([
                    'booking_id' => $booking->id,
                    'court_id' => $court->id,
                    'date' => $date,
                    'slot_time' => $time,
                    'rate_per_hour' => $ratePerHour,
                    'status' => 'confirmed',
                    'held_until' => null,
                ]);
            }

            // Real-time broadcast
            try {
                broadcast(new CourtSlotsUpdated(
                    $date,
                    $court->id,
                    $slots,
                    'confirmed',
                    null,
                    "Court {$court->name} reserved instantly using credits by {$booking->customer_name}.",
                    $booking->booking_reference
                ))->toOthers();
            } catch (\Exception $e) {
                Log::warning('Reverb broadcast error: ' . $e->getMessage());
            }

            // Send instant confirmation email for credit booking (registered user)
            EmailNotificationService::sendBookingApproved($booking);

            return $booking;
        });
    }

    /**
     * Confirm a booking (called by Xendit webhook/simulation or Owner approval)
     */
    public function confirmBooking(int|Booking $booking, ?int $approverId = null, string $paymentMethod = null): Booking
    {
        if (is_numeric($booking)) {
            $booking = Booking::findOrFail($booking);
        }

        $booking->update([
            'booking_status' => 'confirmed',
            'payment_status' => 'paid',
            'approved_by' => $approverId,
            'approved_at' => now(),
            'payment_method' => $paymentMethod ?? $booking->payment_method,
            'held_until' => null,
        ]);

        BookingSlot::where('booking_id', $booking->id)->update([
            'status' => 'confirmed',
            'held_until' => null,
        ]);

        $slotTimes = $booking->slots()->pluck('slot_time')->toArray();
        $date = $booking->booking_date->format('Y-m-d');

        try {
            broadcast(new CourtSlotsUpdated(
                $date,
                $booking->court_id,
                $slotTimes,
                'confirmed',
                null,
                "Booking {$booking->booking_reference} confirmed on {$booking->court->name}!",
                $booking->booking_reference
            ));
        } catch (\Exception $e) {
            Log::warning('Reverb broadcast error: ' . $e->getMessage());
        }

        // Send booking approval / confirmation email to customer (logged in user or guest with email)
        EmailNotificationService::sendBookingApproved($booking);

        return $booking;
    }

    /**
     * Reject a booking (called by Owner)
     */
    public function rejectBooking(int|Booking $booking, ?string $reason = null, ?int $approverId = null): Booking
    {
        if (is_numeric($booking)) {
            $booking = Booking::findOrFail($booking);
        }

        $booking->update([
            'booking_status' => 'rejected',
            'rejection_reason' => $reason ?: 'Payment receipt could not be verified.',
            'approved_by' => $approverId,
            'held_until' => null,
        ]);

        BookingSlot::where('booking_id', $booking->id)->update([
            'status' => 'released',
            'held_until' => null,
        ]);

        $slotTimes = $booking->slots()->pluck('slot_time')->toArray();
        $date = $booking->booking_date->format('Y-m-d');

        try {
            broadcast(new CourtSlotsUpdated(
                $date,
                $booking->court_id,
                $slotTimes,
                'released',
                null,
                "Booking rejected; slots on {$booking->court->name} released."
            ))->toOthers();
        } catch (\Exception $e) {
            Log::warning('Reverb broadcast error: ' . $e->getMessage());
        }

        return $booking;
    }

    /**
     * Release held booking if user manually cancels or closes checkout
     */
    public function cancelHeldBooking(string $reference): bool
    {
        $booking = Booking::where('booking_reference', $reference)
            ->whereIn('booking_status', ['held', 'expired'])
            ->first();

        if (!$booking) {
            return false;
        }

        // Safety check: if user completed payment in PayMongo while cancel was triggered, confirm instead of cancelling
        if ($booking->payment_method === 'paymongo' && !empty($booking->paymongo_checkout_id)) {
            try {
                $paymongo = app(PayMongoService::class);
                $check = $paymongo->checkCheckoutSessionStatus($booking->paymongo_checkout_id);
                if (!empty($check['paid'])) {
                    $this->confirmBooking($booking, null, 'paymongo');
                    return false;
                }
            } catch (\Exception $e) {
                Log::warning("PayMongo check failed during cancelHeldBooking for {$booking->booking_reference}: " . $e->getMessage());
            }
        }

        $booking->update([
            'booking_status' => 'cancelled',
        ]);

        // Expire PayMongo checkout session on manual cancellation
        if ($booking->payment_method === 'paymongo' && !empty($booking->paymongo_checkout_id)) {
            try {
                $paymongo = app(PayMongoService::class);
                $paymongo->expireCheckoutSession($booking->paymongo_checkout_id);
            } catch (\Exception $e) {}
        }

        BookingSlot::where('booking_id', $booking->id)->update([
            'status' => 'released',
        ]);

        $slotTimes = $booking->slots()->pluck('slot_time')->toArray();
        $date = $booking->booking_date->format('Y-m-d');

        try {
            broadcast(new CourtSlotsUpdated(
                $date,
                $booking->court_id,
                $slotTimes,
                'released',
                null,
                "Hold released on {$booking->court->name}."
            ));
        } catch (\Exception $e) {
            Log::warning('Reverb broadcast error: ' . $e->getMessage());
        }

        return true;
    }

    /**
     * Reserve court manually by Court Owner / Admin (offline / whole-court rental)
     */
    public function manualReserveCourt(array $data, int $approvedByUserId): Booking
    {
        $this->releaseExpiredHolds();

        $courtId = (int) $data['court_id'];
        $date = $data['date'];
        $slots = $data['slots'];
        $court = Court::findOrFail($courtId);

        if (empty($slots)) {
            throw new \Exception('Please select at least one timeslot for this reservation.');
        }

        // Conflict check against active reservations
        $conflicts = BookingSlot::where('court_id', $courtId)
            ->whereDate('date', $date)
            ->whereIn('slot_time', $slots)
            ->where(function ($q) {
                $q->whereIn('status', ['confirmed', 'pending_approval'])
                  ->orWhere(function ($sub) {
                      $sub->where('status', 'held')
                          ->where('held_until', '>', now());
                  });
            })
            ->pluck('slot_time')
            ->toArray();

        if (!empty($conflicts)) {
            $formattedConflicts = array_map(function ($s) {
                return Carbon::createFromFormat('H:i', $s)->format('g:i A');
            }, $conflicts);
            throw new \Exception('The following timeslot(s) are already booked: ' . implode(', ', $formattedConflicts) . '. Please unselect them.');
        }

        sort($slots);
        $startTime = reset($slots);
        $lastSlot = end($slots);
        $endHour = (int) substr($lastSlot, 0, 2) + 1;
        $endTime = sprintf('%02d:00', $endHour);
        $totalHours = count($slots);
        $ratePerHour = $court->price_per_hour;

        $totalAmount = (isset($data['total_amount']) && $data['total_amount'] !== '' && $data['total_amount'] !== null)
            ? (float) $data['total_amount']
            : ($ratePerHour * $totalHours);

        $reference = 'PF-OWN-' . date('Ymd') . '-' . strtoupper(Str::random(5));

        return DB::transaction(function () use (
            $reference, $data, $court, $date, $startTime, $endTime,
            $totalHours, $ratePerHour, $totalAmount, $slots, $approvedByUserId
        ) {
            $booking = Booking::create([
                'booking_reference' => $reference,
                'user_id' => $data['user_id'] ?? null,
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'],
                'customer_email' => $data['customer_email'] ?? null,
                'court_id' => $court->id,
                'booking_date' => $date,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'total_hours' => $totalHours,
                'rate_per_hour' => $ratePerHour,
                'total_amount' => $totalAmount,
                'players_count' => $data['players_count'] ?? $court->max_players,
                'notes' => $data['notes'] ?? 'Reserved by Court Owner / Whole-court rental (offline)',
                'payment_method' => $data['payment_method'] ?? 'walk_in_offline',
                'payment_status' => $data['payment_status'] ?? 'paid',
                'booking_status' => 'confirmed',
                'approved_by' => $approvedByUserId,
                'approved_at' => now(),
            ]);

            foreach ($slots as $time) {
                BookingSlot::create([
                    'booking_id' => $booking->id,
                    'court_id' => $court->id,
                    'date' => $date,
                    'slot_time' => $time,
                    'rate_per_hour' => $ratePerHour,
                    'status' => 'confirmed',
                    'held_until' => null,
                ]);
            }

            try {
                broadcast(new CourtSlotsUpdated(
                    $date,
                    $court->id,
                    $slots,
                    'confirmed',
                    null,
                    "Court {$court->name} reserved by Court Owner."
                ));
            } catch (\Throwable $e) {
                Log::warning('Reverb broadcast error: ' . $e->getMessage());
            }

            return $booking;
        });
    }

    /**
     * Cancel an existing reservation of any user (called by Court Owner / Admin)
     */
    public function cancelReservation(int|Booking $booking, ?string $reason = null, ?int $cancelledByUserId = null): Booking
    {
        if (is_numeric($booking)) {
            $booking = Booking::with(['court', 'slots'])->findOrFail($booking);
        }

        $booking->update([
            'booking_status' => 'cancelled',
            'rejection_reason' => $reason ? "Cancelled by Court Owner: {$reason}" : 'Cancelled by Court Owner.',
            'approved_by' => $cancelledByUserId ?? $booking->approved_by,
            'held_until' => null,
        ]);

        BookingSlot::where('booking_id', $booking->id)->update([
            'status' => 'released',
            'held_until' => null,
        ]);

        // Expire PayMongo session if active
        if ($booking->payment_method === 'paymongo' && !empty($booking->paymongo_checkout_id)) {
            try {
                $paymongo = app(PayMongoService::class);
                $paymongo->expireCheckoutSession($booking->paymongo_checkout_id);
            } catch (\Throwable $e) {}
        }

        $slotTimes = $booking->slots()->pluck('slot_time')->toArray();
        $date = $booking->booking_date->format('Y-m-d');

        try {
            broadcast(new CourtSlotsUpdated(
                $date,
                $booking->court_id,
                $slotTimes,
                'released',
                null,
                "Reservation {$booking->booking_reference} on {$booking->court->name} was cancelled by Court Owner."
            ));
        } catch (\Throwable $e) {
            Log::warning('Reverb broadcast error: ' . $e->getMessage());
        }

        return $booking;
    }
}
