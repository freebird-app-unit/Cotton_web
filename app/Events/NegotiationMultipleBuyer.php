<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NegotiationMultipleBuyer implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $negotiationMultipleBuyer;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($negotiationMultipleBuyerData)
    {
        $this->negotiationMultipleBuyer = $negotiationMultipleBuyerData;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('NegotiationToMultipleBuyer'.$this->negotiationMultipleBuyer->buyer_id);
    }

    public function broadcastAs()
    {
        return 'NegotiationMultipleBuyer';
    }

    public function broadcastWith()
    {
        return ['title'=>'Join Channel', 'negotiationMultipleBuyer'=> $this->negotiationMultipleBuyer];
    }
}
