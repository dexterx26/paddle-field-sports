<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CourtSlotsUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $date;
    public int $courtId;
    public array $slots; // array of slot times e.g. ['14:00', '15:00']
    public string $status; // 'held', 'confirmed', 'released', 'pending_approval'
    public ?int $heldUntilTimestamp;
    public string $message;

    /**
     * Create a new event instance.
     */
    public function __construct(
        string $date,
        int $courtId,
        array $slots,
        string $status,
        ?int $heldUntilTimestamp = null,
        string $message = 'Court availability changed'
    ) {
        $this->date = $date;
        $this->courtId = $courtId;
        $this->slots = $slots;
        $this->status = $status;
        $this->heldUntilTimestamp = $heldUntilTimestamp;
        $this->message = $message;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('courts'),
            new Channel('courts.' . $this->date),
        ];
    }

    public function broadcastAs(): string
    {
        return 'CourtSlotsUpdated';
    }

    public function broadcastWith(): array
    {
        $remaining = 0;
        if ($this->heldUntilTimestamp) {
            $remaining = max(0, $this->heldUntilTimestamp - now()->timestamp);
        }

        return [
            'date' => $this->date,
            'court_id' => $this->courtId,
            'slots' => $this->slots,
            'status' => $this->status,
            'held_until_timestamp' => $this->heldUntilTimestamp,
            'remaining_seconds' => $remaining,
            'message' => $this->message,
            'timestamp' => now()->timestamp,
        ];
    }
}
