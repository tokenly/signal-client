<?php

namespace Tokenly\SignalClient\Mock;

use Illuminate\Queue\QueueManager;
use Illuminate\Support\Facades\Config;
use Tokenly\SignalClient\Mock\SignalClientMock;
use Tokenly\SignalClient\SignalClient;

/**
 * Class SignalClientMock
 */
class SignalClientMock extends SignalClient
{

    public $_sent_payloads = [];
    public $_sent_replies = [];

    public static function mockSignalClient()
    {
        $config = Config::get('signal-client');
        $mock_signal_client = new SignalClientMock($config['queue_connection'], $config['queue_name'], $config['reply_queue_name'], app(QueueManager::class));
        app()->instance(SignalClient::class, $mock_signal_client);
        return $mock_signal_client;
    }

    public function getSentPayloads()
    {
        return $this->_sent_payloads;
    }
    public function clearSentPayloads()
    {
        $this->_sent_payloads = [];
    }

    public function getSentReplies()
    {
        return $this->_sent_replies;
    }
    public function clearSentReplies()
    {
        $this->_sent_replies = [];
    }

    public function send($payload)
    {
        // save the sent payload
        $this->_sent_payloads[] = $payload;

        return [];
    }

    public function sendReply($uuid, $response = null)
    {
        // save the sent reply
        $this->_sent_replies[] = [
            'uuid' => $uuid,
            'response' => $response,
        ];

        return $uuid;
    }

}
