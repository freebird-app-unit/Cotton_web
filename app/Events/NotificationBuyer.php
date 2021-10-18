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
class NotificationBuyer implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $notificationBuyer;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($notificationBuyerData)
    {
        $this->notificationBuyer = $notificationBuyerData;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('NotificationToBuyer'.$this->notificationBuyer->buyer_id);
    }

    public function broadcastAs()
    {
        return 'NotificationBuyer';
    }

    public function broadcastWith()
    {
        return ['title'=>'Join Channel', 'notificationBuyer'=> $this->notificationBuyer];
    }
}
