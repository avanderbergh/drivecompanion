<?php

namespace App\Jobs;

use App\Jobs\Job;
use App\Events\FileCopied;
use App\Facades\AssignmentService;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class CopyFileGroup extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $count;
    protected $email;
    protected $config;
    protected $queue_no;
    protected $assignment;
    protected $group_name;
    protected $section_id;
    protected $enrollments;
    protected $pusher_channel;

    /**
     * Create a new job instance.
     *
     * @param $enrollments
     * @param $assignment
     * @param $group_name
     * @param $email
     * @param $section_id
     * @param $config
     * @param $count
     * @param $queue_no
     * @param $pusher_channel
     */
    public function __construct($enrollments, $assignment, $group_name, $email, $section_id, $config, $count, $queue_no, $pusher_channel)
    {
        $this->count = $count;
        $this->email = $email;
        $this->config = $config;
        $this->queue_no = $queue_no;
        $this->assignment = $assignment;
        $this->group_name = $group_name;
        $this->section_id = $section_id;
        $this->enrollments = $enrollments;
        $this->pusher_channel = $pusher_channel;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Use the Assignment service to copy the file
        $assignment_file = AssignmentService::copyFile($this->enrollments, $this->assignment->id, $this->assignment->template_id, $this->assignment->folder_id, $this->assignment->name." - ".$this->group_name, $this->email, $this->config);
        // Trigger the event to update the progress bar
        event(new FileCopied($assignment_file, $this->section_id, $this->count, $this->pusher_channel, $this->queue_no));
    }
}
