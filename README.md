# Signal Client

Uses Tokenly Signal to send message queue, websocket and webhook notifications.  Also provides a helper class to receive message queue notifications.

# Installation

### Add the package via composer

```
composer require tokenly/signal-client
```

## Usage with Laravel

The service provider will automatically be registered in a Laravel 5.5+ application.


### Sending Notifications

```php
$signal_client = app(\Tokenly\SignalClient\SignalClient::class);
$signal_client->sendWebsocketNotification('mychannel', 'myevent', ['hello' => 'world']);

```

### Consuming Internal Notifications

Create a class `App\Jobs\SignalNotificationJob` that extends `Tokenly\SignalClient\Jobs\SignalConsumerJob`.

The SignalNotificationJob must implement the following method:

```php
public function handle($data) {
    // $data['uuid']
    // $data['eventType']
    // $data['payload']
}
```

You should also define:

```php
    protected $max_attempts = null;
```

in your subclass.