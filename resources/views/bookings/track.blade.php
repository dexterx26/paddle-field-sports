@extends('layouts.app')

@section('content')
@php
    $isOwner = $isOwner ?? (Auth::check() && (Auth::user()->isOwner() || Auth::user()->isAdmin()));
@endphp

<div class="py-12 px-4 sm:px-6 lg:px-8 max-w-4xl mx-auto">
    <!-- Top breadcrumb / back link -->
    <div class="mb-6 flex items-center justify-between">
        @if($isOwner)
            <a href="{{ route('owner.bookings.index') }}" class="text-xs font-semibold text-theme-muted hover:text-cyan-600 dark:hover:text-cyan-400 flex items-center gap-1.5 transition-colors">
                <i class="fa-solid fa-arrow-left"></i> Back to Reservations Management
            </a>
        @else
            <a href="{{ route('home') }}" class="text-xs font-semibold text-theme-muted hover:text-cyan-600 dark:hover:text-cyan-400 flex items-center gap-1.5 transition-colors">
                <i class="fa-solid fa-arrow-left"></i> Back to Schedule
            </a>
        @endif
        <div class="flex items-center gap-2">
            @if($isOwner)
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-500/10 text-amber-700 dark:text-amber-400 border border-amber-500/30">
                    <i class="fa-solid fa-shield-halved"></i> Owner View
                </span>
            @endif
            <div class="reverb-status-indicator">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-cyan-500/10 text-cyan-700 dark:text-cyan-400 border border-cyan-500/30">
                    <span class="w-1.5 h-1.5 rounded-full bg-cyan-400 animate-ping"></span> Live Tracker
                </span>
            </div>
        </div>
    </div>

    <!-- Booking Status Main Banner -->
    <div class="rounded-3xl glass-panel p-6 sm:p-8 shadow-xl relative overflow-hidden mb-8">
        @if($booking->booking_status === 'confirmed')
            <!-- CONFIRMED STATE -->
            <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-cyan-400 via-cyan-500 to-amber-500"></div>
            <div class="text-center space-y-3">
                <div class="w-16 h-16 rounded-full bg-cyan-500/20 text-cyan-600 dark:text-cyan-400 flex items-center justify-center text-3xl mx-auto border border-cyan-500/30">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <span class="px-3.5 py-1 rounded-full text-xs font-extrabold bg-cyan-500/15 text-cyan-700 dark:text-cyan-300 border border-cyan-500/30 uppercase tracking-widest inline-block">
                    Reservation Confirmed
                </span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-theme-heading">Your Court is Ready!</h2>
                <p class="text-xs sm:text-sm text-theme-body max-w-md mx-auto">
                    Payment verified. Present your digital court pass or reference code upon arriving at Paddle Field Sports Center.
                </p>
            </div>

        @elseif($booking->booking_status === 'pending_approval')
            <!-- PENDING OWNER APPROVAL (Manual Receipt Mode) -->
            <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-amber-500 via-amber-600 to-cyan-500"></div>
            <div class="text-center space-y-3">
                <div class="w-16 h-16 rounded-full bg-amber-500/20 text-amber-700 dark:text-amber-400 flex items-center justify-center text-3xl mx-auto border border-amber-500/30 animate-pulse">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </div>
                <span class="px-3.5 py-1 rounded-full text-xs font-extrabold bg-amber-500/15 text-amber-800 dark:text-amber-300 border border-amber-500/30 uppercase tracking-widest inline-block">
                    Payment Receipt Under Review
                </span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-theme-heading">Pending Owner Approval</h2>
                <p class="text-xs sm:text-sm text-theme-body max-w-md mx-auto">
                    We received your payment receipt screenshot! The court owner is currently reviewing your transaction. This page will update automatically once approved.
                </p>
            </div>

        @elseif($booking->booking_status === 'held')
            <!-- HELD STATE (Xendit 2-Minute Checkout Window) -->
            <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-cyan-400 to-amber-500"></div>
            <div class="text-center space-y-3">
                <div class="w-16 h-16 rounded-full bg-cyan-500/20 text-cyan-600 dark:text-cyan-400 flex items-center justify-center text-3xl mx-auto border border-cyan-500/30 animate-pulse">
                    <i class="fa-solid fa-stopwatch"></i>
                </div>
                <span class="px-3.5 py-1 rounded-full text-xs font-extrabold bg-cyan-500/15 text-cyan-700 dark:text-cyan-300 border border-cyan-500/30 uppercase tracking-widest inline-block">
                    2-Minute Reservation Hold Active
                </span>
                <div class="text-4xl sm:text-5xl font-black text-theme-heading font-mono" id="trackerHoldTimer" data-remaining="{{ $booking->remaining_hold_seconds }}">
                    {{ sprintf('%02d:%02d', floor($booking->remaining_hold_seconds / 60), $booking->remaining_hold_seconds % 60) }}
                </div>
                <p class="text-xs sm:text-sm text-theme-body max-w-md mx-auto">
                    Timeslot locked. Please finalize payment before the countdown hits 00:00 to guarantee your spot.
                </p>

                <div class="pt-3 flex flex-wrap items-center justify-center gap-3">
                    @if($booking->xendit_payment_url)
                        <a href="{{ $booking->xendit_payment_url }}" target="_blank"
                            class="px-6 py-3 rounded-xl bg-gradient-to-r from-cyan-500 to-cyan-600 hover:from-cyan-400 hover:to-cyan-500 text-slate-950 font-black text-xs shadow-lg shadow-cyan-500/20 flex items-center gap-2 cursor-pointer">
                            <i class="fa-solid fa-credit-card"></i> Pay with Xendit Now
                        </a>
                    @endif
                    <button type="button" onclick="simulateTestPayment('{{ $booking->booking_reference }}')"
                        class="px-5 py-3 rounded-xl bg-stone-200 dark:bg-stone-800 hover:bg-stone-300 dark:hover:bg-stone-700 text-cyan-700 dark:text-cyan-400 text-xs font-bold border border-cyan-500/30 flex items-center gap-2 cursor-pointer">
                        <i class="fa-solid fa-wand-magic-sparkles"></i> Simulate Instant Payment (Demo)
                    </button>
                </div>
            </div>

        @elseif($booking->booking_status === 'expired')
            <!-- EXPIRED STATE -->
            <div class="absolute top-0 left-0 right-0 h-1.5 bg-rose-500"></div>
            <div class="text-center space-y-3">
                <div class="w-16 h-16 rounded-full bg-rose-500/20 text-rose-500 flex items-center justify-center text-3xl mx-auto border border-rose-500/30">
                    <i class="fa-solid fa-hourglass-end"></i>
                </div>
                <span class="px-3.5 py-1 rounded-full text-xs font-extrabold bg-rose-500/15 text-rose-700 dark:text-rose-300 border border-rose-500/30 uppercase tracking-widest inline-block">
                    Reservation Hold Expired
                </span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-theme-heading">Time Expired</h2>
                <p class="text-xs sm:text-sm text-theme-body max-w-md mx-auto">
                    The 2-minute holding window expired before payment was completed. The timeslots have been released back into the public schedule.
                </p>
                @if(!$isOwner)
                    <div class="pt-3">
                        <a href="{{ route('home') }}#booking-engine" class="px-6 py-3 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold text-xs shadow-lg shadow-cyan-500/20 inline-flex items-center gap-2 cursor-pointer">
                            <i class="fa-solid fa-calendar-plus"></i> Select a New Slot
                        </a>
                    </div>
                @endif
            </div>

        @elseif($booking->booking_status === 'rejected')
            <!-- REJECTED STATE -->
            <div class="absolute top-0 left-0 right-0 h-1.5 bg-rose-500"></div>
            <div class="text-center space-y-3">
                <div class="w-16 h-16 rounded-full bg-rose-500/20 text-rose-500 flex items-center justify-center text-3xl mx-auto border border-rose-500/30">
                    <i class="fa-solid fa-ban"></i>
                </div>
                <span class="px-3.5 py-1 rounded-full text-xs font-extrabold bg-rose-500/15 text-rose-700 dark:text-rose-300 border border-rose-500/30 uppercase tracking-widest inline-block">
                    Reservation Declined
                </span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-theme-heading">Booking Not Approved</h2>
                <p class="text-xs sm:text-sm text-theme-body max-w-md mx-auto">
                    {{ $booking->rejection_reason ?: 'The uploaded receipt was invalid or payment could not be matched. Please contact Paddle Field support.' }}
                </p>
            </div>
        @endif
    </div>

    <!-- Booking Pass / Receipt Card -->
    <div class="rounded-3xl glass-card p-6 sm:p-8 shadow-xl space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-6 border-b border-stone-200 dark:border-stone-800 gap-4">
            <div>
                <span class="text-xs font-semibold text-theme-muted">Booking Reference</span>
                <h3 class="text-xl sm:text-2xl font-mono font-black text-cyan-600 dark:text-cyan-400 mt-0.5 tracking-wider">{{ $booking->booking_reference }}</h3>
            </div>
            @if(!$isOwner)
                <div class="flex items-center gap-2">
                    <button type="button" onclick="window.print()" class="px-3.5 py-2 rounded-xl bg-stone-200 dark:bg-stone-800 hover:bg-stone-300 dark:hover:bg-stone-700 text-xs font-semibold text-theme-heading border border-stone-300 dark:border-stone-700 flex items-center gap-2 transition-colors cursor-pointer">
                        <i class="fa-solid fa-print"></i> Print Pass
                    </button>
                </div>
            @endif
        </div>

        <!-- Grid of Details -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-xs">
            <!-- Court & Timing -->
            <div class="p-4 rounded-2xl bg-stone-100 dark:bg-stone-900 border border-stone-200 dark:border-stone-800 space-y-3">
                <h4 class="font-bold uppercase tracking-wider text-[11px] text-cyan-700 dark:text-cyan-400 flex items-center gap-2">
                    <i class="fa-solid fa-table-tennis-paddle-ball"></i> Court & Timeslot
                </h4>
                <div class="space-y-1.5 text-theme-body">
                    <div class="flex justify-between">
                        <span class="text-theme-muted">Court Name:</span>
                        <span class="font-bold text-theme-heading">{{ $booking->court->name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-theme-muted">Court Type:</span>
                        <span class="capitalize font-semibold text-theme-heading">{{ $booking->court->type }} ({{ $booking->court->surface_type }})</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-theme-muted">Reservation Date:</span>
                        <span class="font-bold text-theme-heading">{{ $booking->booking_date->format('l, F j, Y') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-theme-muted">Time Range:</span>
                        <span class="font-bold text-cyan-600 dark:text-cyan-400">{{ date('g:i A', strtotime($booking->start_time)) }} – {{ date('g:i A', strtotime($booking->end_time)) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-theme-muted">Total Hours:</span>
                        <span class="font-bold text-theme-heading">{{ $booking->total_hours }} {{ $booking->total_hours == 1 ? 'Hour' : 'Hours' }}</span>
                    </div>
                </div>
            </div>

            <!-- Customer & Payment -->
            <div class="p-4 rounded-2xl bg-stone-100 dark:bg-stone-900 border border-stone-200 dark:border-stone-800 space-y-3">
                <h4 class="font-bold uppercase tracking-wider text-[11px] text-amber-700 dark:text-amber-400 flex items-center gap-2">
                    <i class="fa-solid fa-user-check"></i> Player & Payment
                </h4>
                <div class="space-y-1.5 text-theme-body">
                    <div class="flex justify-between">
                        <span class="text-theme-muted">Guest / Player:</span>
                        <span class="font-bold text-theme-heading">{{ $booking->customer_name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-theme-muted">Mobile Phone:</span>
                        <span class="font-mono text-theme-heading">{{ $booking->customer_phone }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-theme-muted">Hourly Rate Snapshot:</span>
                        <span class="font-mono text-theme-body">₱{{ number_format($booking->rate_per_hour, 2) }} / hr</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-theme-muted">Payment Channel:</span>
                        <span class="capitalize font-bold text-theme-heading">{{ str_replace('_', ' ', $booking->payment_method) }}</span>
                    </div>
                    <div class="flex justify-between text-sm pt-1 border-t border-stone-200 dark:border-stone-800">
                        <span class="font-bold text-theme-heading">Total Paid / Due:</span>
                        <span class="font-black text-cyan-600 dark:text-cyan-400 text-base">{{ $booking->formatted_amount }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Uploaded Receipt Preview -->
        @if($booking->receipt_image_path)
            <div class="p-4 rounded-2xl bg-stone-100 dark:bg-stone-900 border border-stone-200 dark:border-stone-800 space-y-3">
                <div class="flex items-center justify-between">
                    <h4 class="font-bold text-theme-heading uppercase tracking-wider text-[11px] flex items-center gap-2">
                        <i class="fa-solid fa-receipt text-amber-600 dark:text-amber-400"></i> Uploaded Payment Receipt Proof
                    </h4>
                    <span class="text-[11px] text-theme-muted">{{ $booking->receipt_uploaded_at?->diffForHumans() }}</span>
                </div>
                <div class="flex items-center gap-4">
                    <a href="{{ $booking->receipt_url }}" target="_blank" class="block max-w-xs overflow-hidden rounded-xl border border-stone-300 dark:border-stone-700 hover:border-cyan-500 transition-colors">
                        <img src="{{ $booking->receipt_url }}" alt="Receipt" class="h-32 w-auto object-cover rounded-xl">
                    </a>
                    <div class="text-xs text-theme-muted space-y-1">
                        <p class="text-theme-heading font-semibold">Screenshot Attached</p>
                        <p>Uploaded during booking submission.</p>
                        <a href="{{ $booking->receipt_url }}" target="_blank" class="inline-flex items-center gap-1 text-cyan-600 dark:text-cyan-400 hover:underline pt-1">
                            <i class="fa-solid fa-magnifying-glass-plus"></i> View Full Resolution
                        </a>
                    </div>
                </div>
            </div>
        @endif

        <!-- Special Notes / Instructions -->
        @if(!$isOwner)
            <div class="p-4 rounded-2xl bg-stone-100/70 dark:bg-stone-900/50 border border-stone-200 dark:border-stone-800 text-xs text-theme-muted space-y-1">
                <div class="font-bold text-theme-heading flex items-center gap-1.5">
                    <i class="fa-solid fa-circle-exclamation text-amber-600 dark:text-amber-400"></i> Facility Reminders:
                </div>
                <ul class="list-disc list-inside space-y-0.5 text-theme-body text-[11px] pl-1">
                    <li>Please arrive 10 minutes before your scheduled start time.</li>
                    <li>Non-marking indoor court shoes are strictly required on all tournament courts.</li>
                    <li>Paddle rentals and ball canisters are available at Ace Pro Shop.</li>
                </ul>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    const bookingRef = "{{ $booking->booking_reference }}";
    let bookingStatus = "{{ $booking->booking_status }}";

    // 1. Live Countdown for Held status
    const timerEl = document.getElementById('trackerHoldTimer');
    if (timerEl && bookingStatus === 'held') {
        let remaining = parseInt(timerEl.getAttribute('data-remaining')) || 120;
        const interval = setInterval(() => {
            if (remaining <= 0) {
                clearInterval(interval);
                timerEl.textContent = '00:00';
                location.reload();
                return;
            }
            remaining--;
            const m = Math.floor(remaining / 60);
            const s = remaining % 60;
            timerEl.textContent = `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
        }, 1000);
    }

    // 2. Poll for status change every 4 seconds as fallback
    if (bookingStatus === 'held' || bookingStatus === 'pending_approval') {
        setInterval(async () => {
            try {
                const res = await fetch(`/api/booking-status/${bookingRef}`);
                const data = await res.json();
                if (data.success && data.booking_status !== bookingStatus) {
                    location.reload();
                }
            } catch (e) {}
        }, 4000);
    }

    // 3. Listen to Reverb Events
    if (typeof window.subscribeCourtUpdates === 'function') {
        window.subscribeCourtUpdates((data) => {
            if (data.status === 'confirmed' || data.status === 'released') {
                setTimeout(() => location.reload(), 800);
            }
        });
    }

    // Demo Test Payment Simulation
    async function simulateTestPayment(ref) {
        try {
            const res = await fetch(`/simulate-payment/${ref}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ payment_channel: 'GCash Xendit' })
            });
            const data = await res.json();
            if (data.success) {
                location.reload();
            }
        } catch (e) {
            location.reload();
        }
    }
</script>
@endpush
