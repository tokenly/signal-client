<?php

namespace Tokenly\SignalClient\Jobs;

use Exception;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;
use Tokenly\LaravelEventLog\Facade\EventLog;
use Tokenly\SignalClient\SignalClient;

abstract class SignalConsumerJob
{
    use InteractsWithQueue;

    /**
     * The chain name.  Must be defined in concrete classes.
     * @var string
     */
    protected $chain_name = '';

    /**
     * Execute the job.
     *
     * @return void
     */
    public function fire(Job $job, $data)
    {
        $this->setJob($job);

        // get the uuid
        $uuid = $data['uuid'] ?? null;
        if (!$uuid) {
            throw new Exception("Invalid signal notification.  Missing uuid.", 1);
        }

        // check attempts
        $attempts = $this->attempts();
        if ($attempts > 1) {
            EventLog::debug("signalConsumerJob.multipleAttempts", ['attempts' => $attempts, 'job' => get_class($this)]);
        }

        // handle the job
        try {
            $response = DB::transaction(function () use ($data) {
                return $this->handle($data);
            });
        } catch (Exception $e) {
            EventLog::logError("signalConsumerJob.error", $e, ['attempts' => $attempts, 'job' => get_class($this)]);

            // send an explicit NACK and do not requeue
            $job->reject();

            throw $e;
        }

        // handling was successful with no exception thrown

        // send a reply
        app(SignalClient::class)->sendReply($uuid, $response);

        // delete the job
        $this->delete();
    }

    // handle the job
    abstract public function handle($data);
}