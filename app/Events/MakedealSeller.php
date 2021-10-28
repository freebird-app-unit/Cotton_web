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

class MakedealSeller implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $makeDealSeller;
        /**
         * Create a new event instance.
         *
         * @return void
         */
        public function __construct($makeDealSellerData)
        {
            $this->makeDealSeller = $makeDealSellerData;
        }

        /**
         * Get the channels the event should broadcast on.
         *
         * @return \Illuminate\Broadcasting\Channel|array
         */
        public function broadcastOn()
        {
            return new Channel('MakeDealToSeller'.$this->makeDealSeller->seller_id);
        }

        public function broadcastAs()
        {
            return 'MakeDealSeller';
        }

        public function broadcastWith()
        {
            return ['title'=>'Join Channel', 'makeDealSeller'=> $this->makeDealSeller];
        }
}
