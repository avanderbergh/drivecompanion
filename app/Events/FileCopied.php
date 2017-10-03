<?php

namespace App\Events;

use App\Enrollment;
use App\Events\Event;
use App\AssignmentFile;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class FileCopied extends Event implements ShouldBroadcast
{
    use SerializesModels;

    public $count;
    public $queue_no;
    public $section_id;
    public $pusher_channel;
    public $assignment_file;

    /**
     * Create a new event instance.
     *
     * @param AssignmentFile $assignment_file
     * @param $section_id
     * @param $count
     * @param $pusher_channel
     * @param $queue_no
     * @internal param Enrollment $enrollment
     */
    public function __construct($assignment_file, $section_id, $count, $pusher_channel, $queue_no)
    {
        $this->count = $count;
        $this->queue_no = $queue_no;
        $this->section_id = $section_id;
        $this->pusher_channel = $pusher_channel;
        $this->assignment_file = $assignment_file;
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
            'file' => $this->assignment_file,
            'count' => $this->count,
        ];
    }
}
