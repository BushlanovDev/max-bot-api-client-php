<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Tests;

use BushlanovDev\MaxMessengerBot\Api;
use BushlanovDev\MaxMessengerBot\ClientApiInterface;
use BushlanovDev\MaxMessengerBot\LongPollingHandler;
use BushlanovDev\MaxMessengerBot\ModelFactory;
use BushlanovDev\MaxMessengerBot\UpdateDispatcher;
use BushlanovDev\MaxMessengerBot\WebhookHandler;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use ReflectionClass;

#[CoversClass(Api::class)]
#[AllowMockObjectsWithoutExpectations]
#[UsesClass(UpdateDispatcher::class)]
#[UsesClass(WebhookHandler::class)]
#[UsesClass(LongPollingHandler::class)]
final class ApiFactoryMethodsTest extends TestCase
{
    private MockObject&ClientApiInterface $clientMock;
    private MockObject&ModelFactory $modelFactoryMock;
    private MockObject&LoggerInterface $loggerMock;
    private Api $api;

    protected function setUp(): void
    {
        $this->clientMock = $this->createMock(ClientApiInterface::class);
        $this->modelFactoryMock = $this->createMock(ModelFactory::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);

        $this->api = new Api(
            'fake-token',
            $this->clientMock,
            $this->modelFactoryMock,
            $this->loggerMock,
        );
    }

    #[Test]
    public function createWebhookHandlerReturnsCorrectlyConfiguredInstance(): void
    {
        $secret = 'my-test-secret';

        $webhookHandler = $this->api->createWebhookHandler($secret);

        $this->assertInstanceOf(WebhookHandler::class, $webhookHandler);

        $this->assertSame(
            $this->getPrivateProperty($this->api, 'updateDispatcher'),
            $this->getPrivateProperty($webhookHandler, 'dispatcher'),
        );
        $this->assertSame(
            $this->getPrivateProperty($this->api, 'modelFactory'),
            $this->getPrivateProperty($webhookHandler, 'modelFactory'),
        );
        $this->assertSame(
            $this->getPrivateProperty($this->api, 'logger'),
            $this->getPrivateProperty($webhookHandler, 'logger'),
        );
        $this->assertSame(
            $secret,
            $this->getPrivateProperty($webhookHandler, 'secret'),
        );
    }

    #[Test]
    public function createLongPollingHandlerReturnsCorrectlyConfiguredInstance(): void
    {
        $longPollingHandler = $this->api->createLongPollingHandler();

        $this->assertInstanceOf(LongPollingHandler::class, $longPollingHandler);

        $this->assertSame(
            $this->api,
            $this->getPrivateProperty($longPollingHandler, 'api')
        );
        $this->assertSame(
            $this->getPrivateProperty($this->api, 'updateDispatcher'),
            $this->getPrivateProperty($longPollingHandler, 'dispatcher')
        );
        $this->assertSame(
            $this->getPrivateProperty($this->api, 'logger'),
            $this->getPrivateProperty($longPollingHandler, 'logger')
        );
    }

    private function getPrivateProperty(object $object, string $propertyName): mixed
    {
        $reflection = new ReflectionClass($object);
        $property = $reflection->getProperty($propertyName);

        return $property->getValue($object);
    }
}
