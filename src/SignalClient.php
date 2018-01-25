<?php

namespace Tokenly\SignalClient;

use Illuminate\Queue\QueueManager;
use Ramsey\Uuid\Uuid;

/**
 * Class SignalClient
 */
class SignalClient
{

    /**
     * SignalClient.
     *
     */
    /**
     * @param string       $queue_connection The name of the signal message queue connection (rabbitmq)
     * @param string       $queue_name       The name of the signal message queue (notification)
     * @param string       $reply_queue_name The name of the signal reply queue (signal_reply)
     * @param QueueManager $queue_manager    The QueueManager instance
     */
    public function __construct($queue_connection, $queue_name, $reply_queue_name, QueueManager $queue_manager)
    {
        $this->queue_connection = $queue_connection;
        $this->queue_name = $queue_name;
        $this->reply_queue_name = $reply_queue_name;
        $this->queue_manager = $queue_manager;
    }

    public function sendWebhookNotification(string $event_type, array $payload, string $webhook_url, array $security_parameters = null)
    {
        // prepare payload
        $notification = [
            'notificationType' => 'webhook',
            'endpoint' => $webhook_url,
            'eventType' => $event_type,
            'payload' => $payload,
        ];

        if ($security_parameters !== null) {
            $notification['security'] = $security_parameters;
        }

        // send to signal
        return $this->send($notification);
    }

    public function sendWebsocketNotification(string $endpoint, string $event_type, array $payload)
    {
        // prepare payload
        $notification = [
            'notificationType' => 'websocket',
            'endpoint' => $endpoint,
            'eventType' => $event_type,
            'payload' => $payload,
        ];

        // send to signal
        return $this->send($notification);
    }

    public function sendMessageQueueNotification(string $endpoint, string $event_type, array $payload)
    {
        // prepare payload
        $notification = [
            'notificationType' => 'messageQueue',
            'endpoint' => $endpoint,
            'eventType' => $event_type,
            'payload' => $payload,
        ];

        // send to signal
        return $this->send($notification);
    }

    public function send($notification)
    {
        if (!isset($notification['uuid'])) {
            $notification['uuid'] = Uuid::uuid4()->toString();
        }

        $queue_data = [
            'job' => 'App\Jobs\IncomingNotificationJob',
            'data' => $notification,
        ];

        $this->queue_manager
            ->connection($this->queue_connection)
            ->pushRaw(json_encode($queue_data), $this->queue_name);

        return $notification['uuid'];
    }

    public function sendReply($uuid, $response = null)
    {

        $reply_data = [
            'uuid' => $uuid,
            'response' => $response,
        ];

        $queue_data = [
            'job' => 'App\Jobs\NotificationReplyJob',
            'data' => $reply_data,
        ];

        $this->queue_manager
            ->connection($this->queue_connection)
            ->pushRaw(json_encode($queue_data), $this->reply_queue_name);

        return $uuid;
    }

}
