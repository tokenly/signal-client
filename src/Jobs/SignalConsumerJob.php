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
     * The maximum number of attempts to process this job.  Leaving this blank processes jobs indefinitely.
     * @var int
     */
    protected $max_attempts = null;

    protected $use_backoff = false;
    protected $backoff_delay = 5;
    protected $backoff_power = 4.1;

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
            if ($this->max_attempts !== null and $attempts >= $this->max_attempts) {
                EventLog::logError("signalConsumerJob.error.permanent", $e, ['attempts' => $attempts, 'job' => get_class($this)]);

                // handle a permanent failure
                $this->handlePermanentFailure($job, $data, $e);

                // send an error response
                app(SignalClient::class)->sendReply($uuid, ['error' => $e->getMessage(), 'errorCode' => $e->getCode()]);

                // delete the job
                $this->delete();

                return;
            }

            // log the temporary error
            EventLog::logError("signalConsumerJob.error", $e, ['attempts' => $attempts, 'job' => get_class($this)]);

            if ($this->use_backoff == false or $this->max_attempts === null or $this->backoff_power === null or $this->backoff_delay === null) {
                // delay without a backoff strategy
                throw $e;
            }

            // delay the job with a backoff strategy
            $delay_seconds = $this->calculateBackoffDelaySeconds($attempts, $this->max_attempts, $this->backoff_delay, $this->backoff_power);
            $job->release($delay_seconds);
            return;
        }

        // handling was successful with no exception thrown
        // send a reply
        app(SignalClient::class)->sendReply($uuid, $response);

        // delete the job
        $this->delete();
    }

    // handle the job
    abstract public function handle($data);

    // handle a permanent failure
    protected function handlePermanentFailure(Job $job, $data, Exception $e)
    {
        // optional 
        //   override this method in subclasses to handle permanent failures
        //   after $this->max_attempts have been exhausted
    }

    // ------------------------------------------------------------------------

    protected function calculateBackoffDelaySeconds($attempts, $max_attempts, $backoff_delay, $backoff_power)
    {
        if ($backoff_power === 1) {
            $delay = $backoff_delay;
        } else {
            $pct = ($attempts - 1) / ($max_attempts - 1);
            $pow = 1 + (($backoff_power - 1) * $pct);
            $delay = pow($backoff_delay, $pow);
            $delay = floor($delay / $backoff_delay) * $backoff_delay;
        }
        return $delay;
    }

}
