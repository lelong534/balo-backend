<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $chat;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($chat)
    {
        $this->chat = $chat;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        $id1 = $this->chat->user_a_id;
        $id2 = $this->chat->user_a_id;
        if ($id1 < $id2) {
            $str = (string) $id1 . (string) $id2;
        } else {
            $str = (string) $id2 . (string) $id1;
        }
        return new PrivateChannel('chat');
    }

    // public function broadcastOn()
    // {
    //     return ['my-channel'];
    // }

    // public function broadcastAs()
    // {
    //     return 'my-event';
    // }
}
