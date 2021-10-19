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

class NegotiationBuyer implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $negotiationBuyer;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($negotiationBuyerData)
    {
        $this->negotiationBuyer = $negotiationBuyerData;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('NegotiationToBuyer'.$this->negotiationBuyer->buyer_id);
    }

    public function broadcastAs()
    {
        return 'NegotiationBuyer';
    }

    public function broadcastWith()
    {
        return ['title'=>'Join Channel', 'negotiationBuyer'=> $this->negotiationBuyer];
    }
}
