<?php

/**
 * This is an example of queue connection configuration.
 * It will be merged into config/queue.php.
 * You need to set proper values in `.env`
 */
return [

  'driver' => 'rabbitmq-signal',
  'connection' => PhpAmqpLib\Connection\AMQPLazyConnection::class,
  'dsn' => env('RABBITMQ_DSN', null),
  'hosts' => [
    [
      'host' => env('RABBITMQ_HOST', '127.0.0.1'),
      'port' => env('RABBITMQ_PORT', 5672),
      'vhost' => env('RABBITMQ_VHOST', '/'),
      'user' => env('RABBITMQ_LOGIN', 'guest'),
      'password' => env('RABBITMQ_PASSWORD', 'guest'),
    ],
  ],
  'options' => [
    'exchange' => [

        'name' => env('RABBITMQ_SIGNAL_EXCHANGE_NAME', env('RABBITMQ_EXCHANGE_NAME')),

        /*
        * Determine if exchange should be created if it does not exist.
        */
        'declare' => env('RABBITMQ_EXCHANGE_DECLARE', false),

        /*
        * Read more about possible values at https://www.rabbitmq.com/tutorials/amqp-concepts.html
        */
        'type' => env('RABBITMQ_EXCHANGE_TYPE', 'direct'),
        'passive' => env('RABBITMQ_EXCHANGE_PASSIVE', false),
        'durable' => env('RABBITMQ_EXCHANGE_DURABLE', true),
        'auto_delete' => env('RABBITMQ_EXCHANGE_AUTODELETE', false),
        'arguments' => env('RABBITMQ_EXCHANGE_ARGUMENTS'),
    ],

    'queue' => [

        /*
        * The name of default queue.
        */
        'name' => env('RABBITMQ_SIGNAL_QUEUE', env('RABBITMQ_QUEUE', 'default')),

        /*
        * Determine if queue should be created if it does not exist.
        */
        'declare' => env('RABBITMQ_QUEUE_DECLARE', false),

        /*
        * Determine if queue should be binded to the exchange created.
        */
        'bind' => env('RABBITMQ_QUEUE_DECLARE_BIND', false),

        /*
        * Read more about possible values at https://www.rabbitmq.com/tutorials/amqp-concepts.html
        */
        'passive' => env('RABBITMQ_QUEUE_PASSIVE', false),
        'durable' => env('RABBITMQ_QUEUE_DURABLE', true),
        'exclusive' => env('RABBITMQ_QUEUE_EXCLUSIVE', false),
        'auto_delete' => env('RABBITMQ_QUEUE_AUTODELETE', false),
        'arguments' => env('RABBITMQ_QUEUE_ARGUMENTS'),
    ],
    'ssl_options' => [
      'ssl_on' => env('RABBITMQ_SSL', false),
      'cafile' => env('RABBITMQ_SSL_CAFILE', null),
      'local_cert' => env('RABBITMQ_SSL_LOCALCERT', null),
      'local_key' => env('RABBITMQ_SSL_LOCALKEY', null),
      'verify_peer' => env('RABBITMQ_SSL_VERIFY_PEER', false),
      'passphrase' => env('RABBITMQ_SSL_PASSPHRASE', null),
      ],
    ],

  /*
  * Set to "horizon" if you wish to use Laravel Horizon.
  */
  'worker' => env('RABBITMQ_WORKER', 'default'),

  /*
   * Determine the number of seconds to sleep if there's an error communicating with rabbitmq
   * If set to false, it'll throw an exception rather than doing the sleep for X seconds.
   */
  'sleep_on_error' => false,


  'receive' => [
      /**
       * Use basic_consume by default which will block and not poll
       */
      'method' => env('RABBITMQ_RECEIVE_METHOD', 'basic_consume'),

      /**
       * The timeout (in milliseconds) to block and wait for a message
       */
      'timeout' => env('RABBITMQ_RECEIVE_TIMEOUT', 5000),
  ],

  /**
   * default to an 8 second heartbeat
   */
  'timeouts' => [
      'read' => 16,
      'write' => 16,
      'heartbeat' => 8,
  ]
];
