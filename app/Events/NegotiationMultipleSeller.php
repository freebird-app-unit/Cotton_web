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

class NegotiationMultipleSeller implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $negotiationMultipleSeller;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($negotiationMultipleSellerData)
    {
        $this->negotiationMultipleSeller = $negotiationMultipleSellerData;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('NegotiationToMultipleSeller'.$this->negotiationMultipleSeller->seller_id);
    }

    public function broadcastAs()
    {
        return 'NegotiationMultipleSeller';
    }

    public function broadcastWith()
    {
        return ['title'=>'Join Channel', 'negotiationMultipleSeller'=> $this->negotiationMultipleSeller];
    }       
}
