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
class NegotiationSeller implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $negotiationSeller;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($negotiationSellerData)
    {
        $this->negotiationSeller = $negotiationSellerData;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('NegotiationToSeller'.$this->negotiationSeller->seller_id);
    }

    public function broadcastAs()
    {
        return 'NegotiationSeller';
    }

    public function broadcastWith()
    {
        return ['title'=>'Join Channel', 'negotiationSeller'=> $this->negotiationSeller];
    }
}
