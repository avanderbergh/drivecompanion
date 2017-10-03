<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ReportError extends Event implements ShouldBroadcast
{
    use SerializesModels;

    public $type;
    public $message;
    public $count;
    public $queue_no;
    public $pusher_channel;

    /**
     * Create a new event instance.
     *
     * @param $type
     * @param $message
     * @param $count
     * @param $queue_no
     * @param $pusher_channel
     */
    public function __construct($type, $message, $count, $queue_no, $pusher_channel)
    {
        $this->type = $type;
        $this->message = $message;
        $this->count = $count;
        $this->queue_no = $queue_no;
        $this->pusher_channel = $pusher_channel;
    }

    /**
     * Return the name of the beanstalk queue the event should broadcast on.
     * A random queue number will be selected.
     * @return string
     */
    public function onQueue()
    {
        $queue = config('queue.name').'BC_'.$this->queue_no;
        return $queue;
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return [$this->pusher_channel];
    }

    public function broadcastWith()
    {
        return
            [
                'type' => $this->type,
                'message' => $this->message,
                'count' => $this->count,
            ];
    }
}
