<?php

return [
    'queue_connection' => env('SIGNAL_QUEUE_CONNECTION', 'rabbitmq'),
    'queue_name' => env('SIGNAL_QUEUE_NAME', 'notification'),

    'consumer_queue_name' => env('SIGNAL_CONSUMER_QUEUE_NAME'),

    'reply_queue_name' => env('SIGNAL_REPLY_QUEUE_NAME', 'signal_reply'),
];
