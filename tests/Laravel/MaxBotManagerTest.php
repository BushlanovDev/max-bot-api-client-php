<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Tests\Laravel;

use BushlanovDev\MaxMessengerBot\Api;
use BushlanovDev\MaxMessengerBot\Enums\ChatType;
use BushlanovDev\MaxMessengerBot\Enums\UpdateType;
use BushlanovDev\MaxMessengerBot\Laravel\MaxBotManager;
use BushlanovDev\MaxMessengerBot\ModelFactory;
use BushlanovDev\MaxMessengerBot\Models\Message;
use BushlanovDev\MaxMessengerBot\Models\MessageBody;
use BushlanovDev\MaxMessengerBot\Models\Recipient;
use BushlanovDev\MaxMessengerBot\Models\Updates\AbstractUpdate;
use BushlanovDev\MaxMessengerBot\Models\Updates\MessageCreatedUpdate;
use BushlanovDev\MaxMessengerBot\UpdateDispatcher;
use BushlanovDev\MaxMessengerBot\WebhookHandler;
use Illuminate\Container\Container;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Facade;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

#[CoversClass(MaxBotManager::class)]
#[AllowMockObjectsWithoutExpectations]
#[UsesClass(Message::class)]
#[UsesClass(MessageBody::class)]
#[UsesClass(Recipient::class)]
#[UsesClass(AbstractUpdate::class)]
#[UsesClass(MessageCreatedUpdate::class)]
#[UsesClass(UpdateDispatcher::class)]
#[UsesClass(WebhookHandler::class)]
final class MaxBotManagerTest extends TestCase
{
    private Container $container;
    private MockObject&Api $apiMock;
    private MockObject&ModelFactory $modelFactoryMock;
    private UpdateDispatcher $updateDispatcher;
    private MaxBotManager $manager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->container = new Container();
        Container::setInstance($this->container);
        Facade::setFacadeApplication($this->container);

        $this->container->singleton('config', function ($app) {
            return $app->make(Repository::class);
        });

        $loggerMock = $this->createMock(LoggerInterface::class);

        $this->container->instance(LoggerInterface::class, $loggerMock);
        $this->container->instance('log', $loggerMock);

        $this->apiMock = $this->createMock(Api::class);
        $this->modelFactoryMock = $this->createMock(ModelFactory::class);

        $this->updateDispatcher = new UpdateDispatcher($this->apiMock);

        $this->container->instance(Api::class, $this->apiMock);
        $this->container->instance('maxbot', $this->apiMock);
        $this->container->instance(ModelFactory::class, $this->modelFactoryMock);
        $this->container->instance(UpdateDispatcher::class, $this->updateDispatcher);

        $this->container->singleton(MaxBotManager::class, function ($app) {
            return new MaxBotManager(
                $app,
                $app->make(Api::class),
                $app->make(UpdateDispatcher::class),
            );
        });
        $this->manager = $this->container->make(MaxBotManager::class);

        Handlers::reset();
    }

    protected function tearDown(): void
    {
        Container::setInstance(null);
        Facade::clearResolvedInstances();
        parent::tearDown();
    }

    #[Test]
    public function handleWebhookReturns200OnSuccess(): void
    {
        $webhookHandler = new WebhookHandler(
            $this->updateDispatcher,
            $this->modelFactoryMock,
            $this->container->make(\Psr\Log\LoggerInterface::class),
            null,
        );
        $this->container->instance(WebhookHandler::class, $webhookHandler);

        $request = Request::create('/webhook', 'POST', content: '{"update_type":"message_created"}');

        $realUpdate = new MessageCreatedUpdate(
            time(),
            $this->createMinimalMessage(),
            'ru-RU',
        );

        $this->modelFactoryMock->method('createUpdate')->willReturn($realUpdate);

        $wasDispatched = false;
        $this->updateDispatcher->addHandler(UpdateType::MessageCreated, function () use (&$wasDispatched) {
            $wasDispatched = true;
        });

        $response = $this->manager->handleWebhook($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertTrue($wasDispatched, 'The update was not dispatched correctly.');
    }

    #[Test]
    public function handleWebhookReturns403OnSecurityException(): void
    {
        $webhookHandler = new WebhookHandler(
            $this->updateDispatcher,
            $this->modelFactoryMock,
            $this->container->make(\Psr\Log\LoggerInterface::class),
            'real-secret',
        );
        $this->container->instance(WebhookHandler::class, $webhookHandler);

        $request = Request::create('/webhook', 'POST', content: '{}');
        $request->headers->set('X-Max-Bot-Api-Secret', 'wrong-secret');

        $response = $this->manager->handleWebhook($request);

        $this->assertSame(403, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString('{"status":"error","message":"Forbidden"}', $response->getContent());
    }

    #[Test]
    public function handleWebhookReturns400OnSerializationException(): void
    {
        $webhookHandler = new WebhookHandler(
            $this->updateDispatcher,
            $this->modelFactoryMock,
            $this->container->make(\Psr\Log\LoggerInterface::class),
            null
        );
        $this->container->instance(WebhookHandler::class, $webhookHandler);

        $request = Request::create('/webhook', 'POST', content: '{invalid-json');

        $response = $this->manager->handleWebhook($request);

        $this->assertSame(400, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString('{"status":"error","message":"Bad Request"}', $response->getContent());
    }

    #[Test]
    public function handleWebhookReturns500OnGenericException(): void
    {
        $webhookHandler = new WebhookHandler(
            $this->updateDispatcher,
            $this->modelFactoryMock,
            $this->container->make(\Psr\Log\LoggerInterface::class),
            null
        );
        $this->container->instance(WebhookHandler::class, $webhookHandler);

        $this->modelFactoryMock->method('createUpdate')->willThrowException(new \Exception('DB error'));

        $request = Request::create('/webhook', 'POST', content: '{"update_type":"message_created"}');

        $response = $this->manager->handleWebhook($request);

        $this->assertSame(500, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            '{"status":"error","message":"Internal Server Error"}',
            $response->getContent(),
        );
    }

    #[Test]
    public function resolveHandlerCanResolveCallable(): void
    {
        $wasCalled = false;
        $callable = function () use (&$wasCalled) {
            $wasCalled = true;
        };

        $this->manager->onCommand('test', $callable);
        $dispatcher = $this->manager->getDispatcher();

        $messageBody = new MessageBody('mid.cmd', 1, 'test', [], []);
        $message = new Message(time(), new Recipient(ChatType::Dialog, 1, null), $messageBody, null, null, null, null);
        $update = new MessageCreatedUpdate(time(), $message, null);

        $dispatcher->dispatch($update);

        $this->assertTrue($wasCalled, 'The resolved callable handler was not called.');
    }

    #[Test]
    public function resolveHandlerResolvesClassWithHandleMethod(): void
    {
        $this->manager->onCommand('test', TestHandlerWithHandleMethod::class);

        $this->dispatchCommand('test');

        $this->assertTrue(Handlers::$wasCalled, 'Handler with handle() method was not resolved and called.');
    }

    #[Test]
    public function resolveHandlerResolvesInvokableClassFromContainer(): void
    {
        $this->container->bind(TestHandlerWithInvokeMethod::class);

        $this->manager->onCommand('test', TestHandlerWithInvokeMethod::class);

        $this->dispatchCommand('test');

        $this->assertTrue(Handlers::$wasCalled, 'Invokable handler was not resolved and called.');
    }

    #[Test]
    public function resolveHandlerResolvesClassAtMethodString(): void
    {
        $this->manager->onCommand('test', TestHandlerWithCustomMethod::class . '@custom');

        $this->dispatchCommand('test');

        $this->assertTrue(Handlers::$wasCalled, 'Handler with "Class@method" string was not resolved and called.');
    }

    #[Test]
    public function resolveHandlerResolvesBoundClassWithHandleMethod(): void
    {
        // Шаг 1: Явно регистрируем класс обработчика в контейнере.
        // Это гарантирует, что будет выбрана ветка `if ($this->container->bound($handler))`.
        $this->container->bind(TestHandlerWithHandleMethod::class);

        // Шаг 2: Регистрируем обработчик, используя его имя класса (строку).
        $this->manager->onCommand('test', TestHandlerWithHandleMethod::class);

        // Шаг 3: Диспетчеризуем команду, которая вызовет обработчик.
        $this->dispatchCommand('test');

        // Шаг 4: Убеждаемся, что метод `handle` был вызван.
        $this->assertTrue(
            Handlers::$wasCalled,
            'Bound handler with handle() method was not resolved and called via the bound path.'
        );
    }

    #[Test]
    public function resolveHandlerThrowsExceptionForUnresolvableString(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to resolve handler: NonExistentClass');

        $this->manager->onCommand('test', 'NonExistentClass');
    }

    #[Test]
    public function resolveHandlerThrowsExceptionForClassWithoutHandleOrInvoke(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            "Handler class '" . Handlers::class . "' is not callable and doesn't have a handle method."
        );

        $this->container->bind(Handlers::class);

        $this->manager->onCommand('test', Handlers::class);
    }

    /**
     * Helper to dispatch a command to the real UpdateDispatcher.
     */
    private function dispatchCommand(string $commandText): void
    {
        $dispatcher = $this->manager->getDispatcher();
        $messageBody = new MessageBody('mid.cmd', 1, $commandText, [], []);
        $message = new Message(time(), new Recipient(ChatType::Dialog, 1, null), $messageBody, null, null, null, null);
        $update = new MessageCreatedUpdate(time(), $message, null);

        $dispatcher->dispatch($update);
    }

    private function createMinimalMessage(): Message
    {
        return new Message(
            time(),
            new Recipient(ChatType::Dialog, 1, null),
            new MessageBody('mid.1', 1, 'test', [], []),
            null,
            null,
            null,
            null,
        );
    }
}

class Handlers
{
    public static bool $wasCalled = false;

    public static function reset(): void
    {
        self::$wasCalled = false;
    }
}

class TestHandlerWithHandleMethod
{
    public function handle(MessageCreatedUpdate $update, Api $api): void
    {
        Handlers::$wasCalled = true;
    }
}

class TestHandlerWithInvokeMethod
{
    public function __invoke(MessageCreatedUpdate $update, Api $api): void
    {
        Handlers::$wasCalled = true;
    }
}

class TestHandlerWithCustomMethod
{
    public function custom(MessageCreatedUpdate $update, Api $api): void
    {
        Handlers::$wasCalled = true;
    }
}
