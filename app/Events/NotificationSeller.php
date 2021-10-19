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

class NotificationSeller implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $notificationSeller;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($notificationSellerData)
    {
        $this->notificationSeller = $notificationSellerData;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('NotificationToSeller'.$this->notificationSeller->seller_id);
    }

    public function broadcastAs()
    {
        return 'NotificationSeller';
    }

    public function broadcastWith()
    {
        return ['title'=>'Join Channel', 'notificationSeller'=> $this->notificationSeller];
    }
}
