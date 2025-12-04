<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Tests;

use BushlanovDev\MaxMessengerBot\Api;
use BushlanovDev\MaxMessengerBot\Enums\ChatType;
use BushlanovDev\MaxMessengerBot\Enums\UpdateType;
use BushlanovDev\MaxMessengerBot\Exceptions\SecurityException;
use BushlanovDev\MaxMessengerBot\Exceptions\SerializationException;
use BushlanovDev\MaxMessengerBot\ModelFactory;
use BushlanovDev\MaxMessengerBot\Models\Message;
use BushlanovDev\MaxMessengerBot\Models\MessageBody;
use BushlanovDev\MaxMessengerBot\Models\Recipient;
use BushlanovDev\MaxMessengerBot\Models\Updates\AbstractUpdate;
use BushlanovDev\MaxMessengerBot\Models\Updates\MessageCreatedUpdate;
use BushlanovDev\MaxMessengerBot\UpdateDispatcher;
use BushlanovDev\MaxMessengerBot\WebhookHandler;
use LogicException;
use phpmock\phpunit\PHPMock;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;

#[CoversClass(WebhookHandler::class)]
#[UsesClass(UpdateDispatcher::class)]
#[UsesClass(Message::class)]
#[UsesClass(MessageBody::class)]
#[UsesClass(Recipient::class)]
#[UsesClass(AbstractUpdate::class)]
#[UsesClass(MessageCreatedUpdate::class)]
final class WebhookHandlerTest extends TestCase
{
    use PHPMock;

    private const string SECRET = 'my-secret-key';

    private MockObject&Api $apiMock;
    private MockObject&ModelFactory $modelFactoryMock;
    private UpdateDispatcher $dispatcher;
    private MockObject&LoggerInterface $loggerMock;

    protected function setUp(): void
    {
        $this->apiMock = $this->createMock(Api::class);
        $this->modelFactoryMock = $this->createMock(ModelFactory::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->dispatcher = new UpdateDispatcher($this->apiMock);
    }

    private function createValidUpdate(): MessageCreatedUpdate
    {
        $messageBody = new MessageBody('m.1', 1, 'Hi', null, null);
        $recipient = new Recipient(ChatType::Dialog, 1, null);
        $message = new Message(time(), $recipient, $messageBody, null, null, null, null);

        return new MessageCreatedUpdate(time(), $message, 'ru-RU');
    }

    private function createMockRequest(string $body, string $signature): ServerRequestInterface
    {
        $streamMock = $this->createMock(StreamInterface::class);
        $streamMock->method('__toString')->willReturn($body);

        $requestMock = $this->createMock(ServerRequestInterface::class);
        $requestMock->method('getBody')->willReturn($streamMock);
        $requestMock->method('getHeaderLine')->with('X-Max-Bot-Api-Secret')->willReturn($signature);

        return $requestMock;
    }

    #[Test]
    #[DataProvider('successfulRequestProvider')]
    public function handleSuccessfulRequest(?string $secret, string $signatureHeader): void
    {
        $payload = '{"update_type":"message_created","timestamp":123}';
        $updateData = json_decode($payload, true);
        $expectedUpdate = $this->createValidUpdate();

        $request = $this->createMockRequest($payload, $signatureHeader);

        $this->modelFactoryMock->expects($this->once())
            ->method('createUpdate')
            ->with($updateData)
            ->willReturn($expectedUpdate);

        $handlerWasCalled = false;
        $this->dispatcher->addHandler(UpdateType::MessageCreated, function () use (&$handlerWasCalled) {
            $handlerWasCalled = true;
        });

        $handler = new WebhookHandler($this->dispatcher, $this->modelFactoryMock, $this->loggerMock, $secret);

        $handler->handle($request);

        $this->assertTrue($handlerWasCalled, 'Dispatcher was not called on successful request.');
    }

    public static function successfulRequestProvider(): array
    {
        return [
            'with correct secret' => [self::SECRET, self::SECRET],
            'with no secret configured' => [null, 'any-signature'],
        ];
    }

    #[Test]
    public function handleThrowsSecurityExceptionOnInvalidSignature(): void
    {
        $this->expectException(SecurityException::class);
        $request = $this->createMockRequest('{}', 'wrong-signature');
        $handler = new WebhookHandler($this->dispatcher, $this->modelFactoryMock, $this->loggerMock, self::SECRET);
        $handler->handle($request);
    }

    #[Test]
    public function handleLogsWarningOnSignatureFailure(): void
    {
        $this->loggerMock->expects($this->once())
            ->method('warning')
            ->with('Webhook signature verification failed', ['received_signature' => 'wrong-signature']);

        $request = $this->createMockRequest('{}', 'wrong-signature');
        $handler = new WebhookHandler($this->dispatcher, $this->modelFactoryMock, $this->loggerMock, self::SECRET);

        try {
            $handler->handle($request);
        } catch (SecurityException) {
            // Expected
        }
    }

    #[Test]
    public function handleThrowsSerializationExceptionOnEmptyBody(): void
    {
        $this->expectException(SerializationException::class);
        $this->expectExceptionMessage('Webhook body is empty.');
        $request = $this->createMockRequest('', self::SECRET);
        $handler = new WebhookHandler($this->dispatcher, $this->modelFactoryMock, $this->loggerMock, self::SECRET);
        $handler->handle($request);
    }

    #[Test]
    public function handleThrowsSerializationExceptionOnInvalidJson(): void
    {
        $this->expectException(SerializationException::class);
        $this->expectExceptionMessage('Failed to decode webhook body as JSON.');
        $request = $this->createMockRequest('{invalid-json', self::SECRET);
        $handler = new WebhookHandler($this->dispatcher, $this->modelFactoryMock, $this->loggerMock, self::SECRET);
        $handler->handle($request);
    }

    #[Test]
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function handleWithoutRequestThrowsLogicExceptionWhenGuzzleIsMissing(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessageMatches('/No ServerRequest was provided and "guzzlehttp\/psr7" is not found/');
        $classExistsMock = $this->getFunctionMock('BushlanovDev\MaxMessengerBot', 'class_exists');
        $classExistsMock->expects($this->once())
            ->with(\GuzzleHttp\Psr7\ServerRequest::class)
            ->willReturn(false);
        $handler = new WebhookHandler($this->dispatcher, $this->modelFactoryMock, $this->loggerMock, null);
        $handler->handle(null);
    }

    #[Test]
    public function handleWithoutRequestWhenGuzzleIsPresent(): void
    {
        if (!class_exists(\GuzzleHttp\Psr7\ServerRequest::class)) {
            $this->markTestSkipped('guzzlehttp/psr7 is not installed, cannot run this test.');
        }
        $this->expectException(SerializationException::class);
        $this->expectExceptionMessage('Webhook body is empty.');
        $handler = new WebhookHandler($this->dispatcher, $this->modelFactoryMock, $this->loggerMock, null);
        $handler->handle(null);
    }

    #[Test]
    public function handleCatchesAndLogsLogicExceptionFromModelFactory(): void
    {
        $payload = '{"update_type":"unknown_type","timestamp":123}';
        $updateData = json_decode($payload, true);
        $exception = new LogicException('Unknown or unsupported update type received: unknown_type');

        $request = $this->createMockRequest($payload, self::SECRET);

        $this->modelFactoryMock->expects($this->once())
            ->method('createUpdate')
            ->with($updateData)
            ->willThrowException($exception);

        $callIndex = 0;
        $this->loggerMock->expects($this->exactly(2))
            ->method('debug')
            ->willReturnCallback(
                function (string $message, array $context = []) use (&$callIndex, $payload, $exception) {
                    if ($callIndex === 0) {
                        $this->assertSame('Received webhook payload', $message);
                        $this->assertArrayHasKey('body', $context);
                        $this->assertSame($payload, $context['body']);
                    } elseif ($callIndex === 1) {
                        $this->assertSame('Unknown or unsupported update type received: unknown_type', $message);
                        $this->assertArrayHasKey('payload', $context);
                        $this->assertArrayHasKey('exception', $context);
                        $this->assertSame($payload, $context['payload']);
                        $this->assertSame($exception, $context['exception']);
                    }
                    $callIndex++;
                }
            );

        $handler = new WebhookHandler($this->dispatcher, $this->modelFactoryMock, $this->loggerMock, self::SECRET);

        $handler->handle($request);
    }
}
