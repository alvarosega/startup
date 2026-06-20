<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DriverLocationUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    // CORRECCIÓN: El ID del conductor es un UUID (string), no un int.
    public function __construct(
        public string $driverId, 
        public float $lat,
        public float $lng
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('admin.logistics'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'driver.moved';
    }
}