<?php

namespace App\Events;

use Session;
use App\User;
use App\Enrollment;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

/**
 * Class UserUpdated
 * @package App\Events
 */
class UserUpdated extends Event implements ShouldBroadcast
{
    use SerializesModels;

    public $user;
    public $count;
    public $files;
    public $queue_no;
    public $enrollment;
    public $pusher_channel;

    /**
     * Create a new event instance.
     *
     * @param User $user
     * @param Enrollment $enrollment
     * @param $files
     * @param $count
     * @param $pusher_channel
     */
    public function __construct(User $user, Enrollment $enrollment, $files, $count, $pusher_channel, $queue_no)
    {
        $this->user = $user;
        $this->count = $count;
        $this->files = $files;
        $this->queue_no = $queue_no;
        $this->enrollment = $enrollment;
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

    /**
     * Specify what gets broadcast back to the user
     * @return array
     */
    public function broadcastWith()
    {
        return
            [
                'count' => $this->count,
                'files' => $this->files,
                'id' => $this->user->id,
                'name' => $this->user->name,
                'name_first' => $this->user->name_first,
                'name_last' => $this->user->name_last,
                'name_first_preferred' => $this->user->name_first_preferred,
                'email' => $this->user->email,
                'picture' => $this->user->picture,
                'folder_id' => $this->enrollment->folder_id,
                'enrollment_id' => $this->enrollment->id,
            ];
    }
}
