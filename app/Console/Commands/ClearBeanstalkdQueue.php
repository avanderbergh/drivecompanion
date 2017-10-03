<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Queue;

class ClearBeanstalkdQueue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:beanstalkd:clear {queue}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear Beanstalkd Queues';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $queue = ($this->argument('queue')) ? $this->argument('queue') : Config::get('queue.connections.beanstalkd.queue');
	$this->info(sprintf('Clearing queue: %s', $queue));
	$pheanstalk = Queue::getPheanstalk();
	$pheanstalk->useTube($queue);
	$pheanstalk->watch($queue);
	while ($job = $pheanstalk->reserve(0)) {			
	    $pheanstalk->delete($job);
	}
	$this->info('...cleared.');
    }
}
