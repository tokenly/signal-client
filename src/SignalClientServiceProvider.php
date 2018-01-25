<?php

namespace Tokenly\SignalClient;

use Illuminate\Queue\QueueManager;
use Illuminate\Support\ServiceProvider;
use Tokenly\SignalClient\SignalClient;

class SignalClientServiceProvider extends ServiceProvider
{

    public function register()
    {
        /**
         * for package configure
         */
        $configPath = __DIR__ . '/config/signal-client.php';
        $this->mergeConfigFrom($configPath, 'signal-client');
        $this->publishes([$configPath => config_path('signal-client.php')], 'signal-client');

        // bind classes
        $this->app->singleton(SignalClient::class, function ($app) {
            $config = $app['config']->get('signal-client');
            return new SignalClient($config['queue_connection'], $config['queue_name'], $config['reply_queue_name'], app(QueueManager::class));
        });
    }

}