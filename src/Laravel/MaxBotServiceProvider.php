<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Laravel;

use BushlanovDev\MaxMessengerBot\Api;
use BushlanovDev\MaxMessengerBot\Client;
use BushlanovDev\MaxMessengerBot\ClientApiInterface;
use BushlanovDev\MaxMessengerBot\Laravel\Commands\PollingStartCommand;
use BushlanovDev\MaxMessengerBot\ModelFactory;
use BushlanovDev\MaxMessengerBot\UpdateDispatcher;
use BushlanovDev\MaxMessengerBot\WebhookHandler;
use BushlanovDev\MaxMessengerBot\LongPollingHandler;
use BushlanovDev\MaxMessengerBot\Laravel\Commands\WebhookSubscribeCommand;
use BushlanovDev\MaxMessengerBot\Laravel\Commands\WebhookUnsubscribeCommand;
use BushlanovDev\MaxMessengerBot\Laravel\Commands\WebhookListCommand;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Config\Repository as Config;
use Psr\Log\LoggerInterface;
use InvalidArgumentException;
use Psr\Log\NullLogger;

/**
 * Laravel Service Provider for Max Bot API Client.
 * Registers all necessary services in the Laravel container.
 */
class MaxBotServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/config/maxbot.php',
            'maxbot',
        );

        $this->app->singleton(ClientApiInterface::class, function (Application $app) {
            /** @var Config $config */
            $config = $app->make(Config::class);
            $accessToken = $config->get('maxbot.access_token');

            if (empty($accessToken)) {
                throw new InvalidArgumentException(
                    'Max Bot access token is not configured. Please set MAXBOT_ACCESS_TOKEN in your .env file.'
                );
            }

            if (!class_exists(\GuzzleHttp\Client::class) || !class_exists(\GuzzleHttp\Psr7\HttpFactory::class)) {
                throw new \LogicException(
                    'Guzzle HTTP client is required. Please run "composer require guzzlehttp/guzzle".'
                );
            }

            $logger = $config->get('maxbot.logging.enabled', false)
                ? $app->make(LoggerInterface::class)
                : new NullLogger();

            $guzzle = new \GuzzleHttp\Client([
                'timeout' => (int)$config->get('maxbot.timeout', 10),
                'connect_timeout' => (int)$config->get('maxbot.connect_timeout', 5),
                'read_timeout' => (int)$config->get('maxbot.read_timeout', 10),
                'headers' => [
                    'User-Agent' => 'max-bot-api-client-php/' . Api::LIBRARY_VERSION
                        . ' Laravel/' . $app->version() . ' PHP/' . PHP_VERSION
                ],
            ]);

            $httpFactory = new \GuzzleHttp\Psr7\HttpFactory();

            return new Client(
                $accessToken,
                $guzzle,
                $httpFactory,
                $httpFactory,
                $config->get('maxbot.base_url', 'https://platform-api.max.ru'),
                $config->get('maxbot.api_version'),
                $logger,
            );
        });

        $this->app->singleton(ModelFactory::class, function (Application $app) {
            /** @var Config $config */
            $config = $app->make(Config::class);
            $logger = $config->get('maxbot.logging.enabled', false)
                ? $app->make(LoggerInterface::class)
                : new NullLogger();

            return new ModelFactory($logger);
        });

        $this->app->singleton(Api::class, function (Application $app) {
            /** @var Config $config */
            $config = $app->make(Config::class);
            $accessToken = $config->get('maxbot.access_token');

            if (empty($accessToken)) {
                throw new InvalidArgumentException(
                    'Max Bot access token is not configured. Please set MAXBOT_ACCESS_TOKEN in your .env file.'
                );
            }

            $logger = $config->get('maxbot.logging.enabled', false)
                ? $app->make(LoggerInterface::class)
                : new NullLogger();

            return new Api(
                $accessToken,
                $app->make(ClientApiInterface::class),
                $app->make(ModelFactory::class),
                $logger,
            );
        });

        $this->app->singleton(UpdateDispatcher::class, function (Application $app) {
            return $app->make(Api::class)->getUpdateDispatcher();
        });

        $this->app->bind(WebhookHandler::class, function (Application $app) {
            /** @var Config $config */
            $config = $app->make(Config::class);
            $secret = $config->get('maxbot.webhook_secret');

            $logger = $config->get('maxbot.logging.enabled', false)
                ? $app->make(LoggerInterface::class)
                : new NullLogger();

            return new WebhookHandler(
                $app->make(UpdateDispatcher::class),
                $app->make(ModelFactory::class),
                $logger,
                $secret,
            );
        });

        $this->app->bind(LongPollingHandler::class, function (Application $app) {
            /** @var Config $config */
            $config = $app->make(Config::class);

            $logger = $config->get('maxbot.logging.enabled', false)
                ? $app->make(LoggerInterface::class)
                : new NullLogger();

            return new LongPollingHandler(
                $app->make(Api::class),
                $app->make(UpdateDispatcher::class),
                $logger,
            );
        });

        $this->app->singleton(MaxBotManager::class, function (Application $app) {
            return new MaxBotManager(
                $app,
                $app->make(Api::class),
                $app->make(UpdateDispatcher::class),
            );
        });

        $this->app->alias(Api::class, 'maxbot');
        $this->app->alias(Api::class, 'maxbot.api');
        $this->app->alias(ClientApiInterface::class, 'maxbot.client');
        $this->app->alias(UpdateDispatcher::class, 'maxbot.dispatcher');
        $this->app->alias(WebhookHandler::class, 'maxbot.webhook');
        $this->app->alias(LongPollingHandler::class, 'maxbot.polling');
        $this->app->alias(MaxBotManager::class, 'maxbot.manager');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/config/maxbot.php' => $this->app->configPath('maxbot.php'),
        ], 'maxbot-config');

        if ($this->app->runningInConsole()) {
            $this->commands([
                WebhookSubscribeCommand::class,
                WebhookUnsubscribeCommand::class,
                WebhookListCommand::class,
                PollingStartCommand::class,
            ]);
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array<int, string>
     */
    public function provides(): array
    {
        return [
            Api::class,
            ClientApiInterface::class,
            ModelFactory::class,
            UpdateDispatcher::class,
            WebhookHandler::class,
            LongPollingHandler::class,
            MaxBotManager::class,
            'maxbot',
            'maxbot.api',
            'maxbot.client',
            'maxbot.dispatcher',
            'maxbot.webhook',
            'maxbot.polling',
            'maxbot.manager',
        ];
    }
}
