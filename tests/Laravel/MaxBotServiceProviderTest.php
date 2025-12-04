<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Tests\Laravel;

use BushlanovDev\MaxMessengerBot\Api;
use BushlanovDev\MaxMessengerBot\Client;
use BushlanovDev\MaxMessengerBot\ClientApiInterface;
use BushlanovDev\MaxMessengerBot\Laravel\Commands\PollingStartCommand;
use BushlanovDev\MaxMessengerBot\Laravel\Commands\WebhookListCommand;
use BushlanovDev\MaxMessengerBot\Laravel\Commands\WebhookSubscribeCommand;
use BushlanovDev\MaxMessengerBot\Laravel\Commands\WebhookUnsubscribeCommand;
use BushlanovDev\MaxMessengerBot\Laravel\MaxBotManager;
use BushlanovDev\MaxMessengerBot\Laravel\MaxBotServiceProvider;
use BushlanovDev\MaxMessengerBot\LongPollingHandler;
use BushlanovDev\MaxMessengerBot\ModelFactory;
use BushlanovDev\MaxMessengerBot\UpdateDispatcher;
use BushlanovDev\MaxMessengerBot\WebhookHandler;
use Illuminate\Support\Facades\Artisan;
use InvalidArgumentException;
use LogicException;
use Orchestra\Testbench\TestCase;
use phpmock\phpunit\PHPMock;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use ReflectionClass;

#[CoversClass(MaxBotServiceProvider::class)]
#[UsesClass(Api::class)]
#[UsesClass(Client::class)]
#[UsesClass(UpdateDispatcher::class)]
#[UsesClass(MaxBotManager::class)]
#[UsesClass(WebhookHandler::class)]
#[UsesClass(ModelFactory::class)]
final class MaxBotServiceProviderTest extends TestCase
{
    use PHPMock;

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('maxbot.access_token', 'test-token');
        $app['config']->set('maxbot.webhook_secret', 'test-secret');
        $app['config']->set('maxbot.base_url', 'https://test.max.ru');
        $app['config']->set('maxbot.api_version', 'test-version');
    }

    protected function getPackageProviders($app): array
    {
        return [MaxBotServiceProvider::class];
    }

    #[Test]
    public function serviceProviderIsLoaded(): void
    {
        $this->assertInstanceOf(MaxBotServiceProvider::class, $this->app->getProvider(MaxBotServiceProvider::class));
    }

    #[Test]
    public function itThrowsExceptionWhenAccessTokenIsMissingForClient(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Max Bot access token is not configured. Please set MAXBOT_ACCESS_TOKEN in your .env file.'
        );

        $this->app['config']->set('maxbot.access_token', null);

        $this->app->make(ClientApiInterface::class);
    }

    #[Test]
    public function itThrowsExceptionWhenAccessTokenIsMissingForApi(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Max Bot access token is not configured. Please set MAXBOT_ACCESS_TOKEN in your .env file.'
        );

        $this->app['config']->set('maxbot.access_token', null);

        $this->app->make(Api::class);
    }

    #[Test]
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function itThrowsExceptionWhenGuzzleIsMissing(): void
    {
        error_reporting(E_ALL & ~E_DEPRECATED);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(
            'Guzzle HTTP client is required. Please run "composer require guzzlehttp/guzzle".'
        );

        $classExistsMock = $this->getFunctionMock('BushlanovDev\\MaxMessengerBot\\Laravel', 'class_exists');
        $classExistsMock->expects($this->once())->with(\GuzzleHttp\Client::class)->willReturn(false);

        (new MaxBotServiceProvider($this->app))->register();

        $this->app->make(ClientApiInterface::class);
    }

    /**
     * @return array<string, array{0: string, 1: class-string}>
     */
    public static function servicesProvider(): array
    {
        return [
            'Api::class' => [Api::class, Api::class],
            'maxbot alias' => ['maxbot', Api::class],
            'maxbot.api alias' => ['maxbot.api', Api::class],
            'ClientApiInterface::class' => [ClientApiInterface::class, Client::class],
            'maxbot.client alias' => ['maxbot.client', Client::class],
            'ModelFactory::class' => [ModelFactory::class, ModelFactory::class],
            'UpdateDispatcher::class' => [UpdateDispatcher::class, UpdateDispatcher::class],
            'maxbot.dispatcher alias' => ['maxbot.dispatcher', UpdateDispatcher::class],
            'WebhookHandler::class' => [WebhookHandler::class, WebhookHandler::class],
            'maxbot.webhook alias' => ['maxbot.webhook', WebhookHandler::class],
            'LongPollingHandler::class' => [LongPollingHandler::class, LongPollingHandler::class],
            'maxbot.polling alias' => ['maxbot.polling', LongPollingHandler::class],
            'MaxBotManager::class' => [MaxBotManager::class, MaxBotManager::class],
            'maxbot.manager alias' => ['maxbot.manager', MaxBotManager::class],
        ];
    }

    #[Test]
    #[DataProvider('servicesProvider')]
    public function allServicesAreRegisteredCorrectly(string $service, string $expectedClass): void
    {
        $this->assertInstanceOf($expectedClass, $this->app->make($service));
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function singletonsProvider(): array
    {
        return [
            'Api' => [Api::class],
            'ClientApiInterface' => [ClientApiInterface::class],
            'ModelFactory' => [ModelFactory::class],
            'UpdateDispatcher' => [UpdateDispatcher::class],
            'MaxBotManager' => [MaxBotManager::class],
        ];
    }

    #[Test]
    #[DataProvider('singletonsProvider')]
    public function servicesAreRegisteredAsSingletons(string $service): void
    {
        $instance1 = $this->app->make($service);
        $instance2 = $this->app->make($service);

        $this->assertSame($instance1, $instance2);
    }

    #[Test]
    public function clientIsConfiguredCorrectlyFromConfig(): void
    {
        /** @var Client $client */
        $client = $this->app->make(ClientApiInterface::class);
        $this->assertInstanceOf(Client::class, $client);

        $reflection = new ReflectionClass($client);

        $accessTokenProp = $reflection->getProperty('accessToken');
        $baseUrlProp = $reflection->getProperty('baseUrl');
        $apiVersionProp = $reflection->getProperty('apiVersion');

        $this->assertSame('test-token', $accessTokenProp->getValue($client));
        $this->assertSame('https://test.max.ru', $baseUrlProp->getValue($client));
        $this->assertSame('test-version', $apiVersionProp->getValue($client));
    }

    #[Test]
    public function clientIsConfiguredWithApplicationLoggerWhenLoggingIsEnabled(): void
    {
        $this->app['config']->set('maxbot.logging.enabled', true);

        $mockLogger = $this->createMock(LoggerInterface::class);
        $this->app->instance(LoggerInterface::class, $mockLogger);

        /** @var Client $client */
        $client = $this->app->make(ClientApiInterface::class);

        $reflection = new ReflectionClass($client);
        $loggerProp = $reflection->getProperty('logger');
        $actualLogger = $loggerProp->getValue($client);

        $this->assertSame($mockLogger, $actualLogger);
    }

    #[Test]
    public function clientIsConfiguredWithNullLoggerWhenLoggingIsDisabled(): void
    {
        $this->app['config']->set('maxbot.logging.enabled', false);

        /** @var Client $client */
        $client = $this->app->make(ClientApiInterface::class);

        $reflection = new ReflectionClass($client);
        $loggerProp = $reflection->getProperty('logger');
        $actualLogger = $loggerProp->getValue($client);

        $this->assertInstanceOf(NullLogger::class, $actualLogger);
    }

    #[Test]
    public function modelFactoryIsConfiguredWithApplicationLoggerWhenLoggingIsEnabled(): void
    {
        $this->app['config']->set('maxbot.logging.enabled', true);

        $mockLogger = $this->createMock(LoggerInterface::class);
        $this->app->instance(LoggerInterface::class, $mockLogger);

        /** @var ModelFactory $factory */
        $factory = $this->app->make(ModelFactory::class);

        $reflection = new ReflectionClass($factory);
        $loggerProp = $reflection->getProperty('logger');
        $actualLogger = $loggerProp->getValue($factory);

        $this->assertSame($mockLogger, $actualLogger);
    }

    #[Test]
    public function modelFactoryIsConfiguredWithNullLoggerWhenLoggingIsDisabled(): void
    {
        $this->app['config']->set('maxbot.logging.enabled', false);

        /** @var ModelFactory $factory */
        $factory = $this->app->make(ModelFactory::class);

        $reflection = new ReflectionClass($factory);
        $loggerProp = $reflection->getProperty('logger');
        $actualLogger = $loggerProp->getValue($factory);

        $this->assertInstanceOf(NullLogger::class, $actualLogger);
    }

    #[Test]
    public function webhookHandlerIsConfiguredWithApplicationLoggerWhenLoggingIsEnabled(): void
    {
        $this->app['config']->set('maxbot.logging.enabled', true);

        $mockLogger = $this->createMock(LoggerInterface::class);
        $this->app->instance(LoggerInterface::class, $mockLogger);

        /** @var WebhookHandler $handler */
        $handler = $this->app->make(WebhookHandler::class);

        $reflection = new ReflectionClass($handler);
        $loggerProp = $reflection->getProperty('logger');
        $actualLogger = $loggerProp->getValue($handler);

        $this->assertSame($mockLogger, $actualLogger);
    }

    #[Test]
    public function webhookHandlerIsConfiguredWithNullLoggerWhenLoggingIsDisabled(): void
    {
        $this->app['config']->set('maxbot.logging.enabled', false);

        /** @var WebhookHandler $handler */
        $handler = $this->app->make(WebhookHandler::class);

        $reflection = new ReflectionClass($handler);
        $loggerProp = $reflection->getProperty('logger');
        $actualLogger = $loggerProp->getValue($handler);

        $this->assertInstanceOf(NullLogger::class, $actualLogger);
    }

    #[Test]
    public function longPollingHandlerIsConfiguredWithApplicationLoggerWhenLoggingIsEnabled(): void
    {
        $this->app['config']->set('maxbot.logging.enabled', true);

        $mockLogger = $this->createMock(LoggerInterface::class);
        $this->app->instance(LoggerInterface::class, $mockLogger);

        /** @var LongPollingHandler $handler */
        $handler = $this->app->make(LongPollingHandler::class);

        $reflection = new ReflectionClass($handler);
        $loggerProp = $reflection->getProperty('logger');
        $actualLogger = $loggerProp->getValue($handler);

        $this->assertSame($mockLogger, $actualLogger);
    }

    #[Test]
    public function longPollingHandlerIsConfiguredWithNullLoggerWhenLoggingIsDisabled(): void
    {
        $this->app['config']->set('maxbot.logging.enabled', false);

        /** @var LongPollingHandler $handler */
        $handler = $this->app->make(LongPollingHandler::class);

        $reflection = new ReflectionClass($handler);
        $loggerProp = $reflection->getProperty('logger');
        $actualLogger = $loggerProp->getValue($handler);

        $this->assertInstanceOf(NullLogger::class, $actualLogger);
    }

    #[Test]
    public function webhookHandlerIsConfiguredWithSecretFromConfig(): void
    {
        /** @var WebhookHandler $handler */
        $handler = $this->app->make(WebhookHandler::class);
        $this->assertInstanceOf(WebhookHandler::class, $handler);

        $reflection = new ReflectionClass($handler);
        $secretProp = $reflection->getProperty('secret');

        $this->assertSame('test-secret', $secretProp->getValue($handler));
    }

    #[Test]
    public function apiIsCreatedWithAllDependenciesFromContainer(): void
    {
        /** @var Api $api */
        $api = $this->app->make(Api::class);

        $reflection = new ReflectionClass($api);

        $clientProp = $reflection->getProperty('client');
        $factoryProp = $reflection->getProperty('modelFactory');
        $loggerProp = $reflection->getProperty('logger');
        $dispatcherProp = $reflection->getProperty('updateDispatcher');

        $this->assertSame($this->app->make(ClientApiInterface::class), $clientProp->getValue($api));
        $this->assertSame($this->app->make(ModelFactory::class), $factoryProp->getValue($api));
        $this->assertInstanceOf(NullLogger::class, $loggerProp->getValue($api));
        $this->assertSame($this->app->make(UpdateDispatcher::class), $dispatcherProp->getValue($api));
    }

    #[Test]
    public function apiIsConfiguredWithApplicationLoggerWhenLoggingIsEnabled(): void
    {
        $this->app['config']->set('maxbot.logging.enabled', true);

        $mockLogger = $this->createMock(LoggerInterface::class);
        $this->app->instance(LoggerInterface::class, $mockLogger);

        /** @var Api $api */
        $api = $this->app->make(Api::class);

        $reflection = new ReflectionClass($api);
        $loggerProp = $reflection->getProperty('logger');
        $actualLogger = $loggerProp->getValue($api);

        $this->assertSame($mockLogger, $actualLogger);
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function commandsProvider(): array
    {
        return [
            'WebhookSubscribeCommand' => [WebhookSubscribeCommand::class, 'maxbot:webhook:subscribe'],
            'WebhookUnsubscribeCommand' => [WebhookUnsubscribeCommand::class, 'maxbot:webhook:unsubscribe'],
            'WebhookListCommand' => [WebhookListCommand::class, 'maxbot:webhook:list'],
            'PollingStartCommand' => [PollingStartCommand::class, 'maxbot:polling:start'],
        ];
    }

    #[Test]
    #[DataProvider('commandsProvider')]
    public function bootMethodRegistersCommandsInConsole(string $class, string $signature): void
    {
        $commands = Artisan::all();
        $this->assertArrayHasKey($signature, $commands);
        $this->assertInstanceOf($class, $commands[$signature]);
    }

    #[Test]
    public function providesMethod(): void
    {
        $provides = [
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

        $this->assertSame($this->app->getProvider(MaxBotServiceProvider::class)->provides(), $provides);
    }
}
