<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Tests;

use BushlanovDev\MaxMessengerBot\Api;
use BushlanovDev\MaxMessengerBot\Enums\ChatType;
use BushlanovDev\MaxMessengerBot\Enums\UpdateType;
use BushlanovDev\MaxMessengerBot\Models\Message;
use BushlanovDev\MaxMessengerBot\Models\MessageBody;
use BushlanovDev\MaxMessengerBot\Models\Recipient;
use BushlanovDev\MaxMessengerBot\Models\Updates\BotStartedUpdate;
use BushlanovDev\MaxMessengerBot\Models\Updates\MessageCreatedUpdate;
use BushlanovDev\MaxMessengerBot\Models\UserWithPhoto;
use BushlanovDev\MaxMessengerBot\UpdateDispatcher;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(UpdateDispatcher::class)]
#[AllowMockObjectsWithoutExpectations]
#[UsesClass(UserWithPhoto::class)]
#[UsesClass(BotStartedUpdate::class)]
#[UsesClass(Message::class)]
#[UsesClass(MessageBody::class)]
#[UsesClass(Recipient::class)]
#[UsesClass(MessageCreatedUpdate::class)]
final class UpdateDispatcherTest extends TestCase
{
    private Api $apiMock;
    private UpdateDispatcher $dispatcher;

    protected function setUp(): void
    {
        $this->apiMock = $this->createMock(Api::class);
        $this->dispatcher = new UpdateDispatcher($this->apiMock);
    }

    #[Test]
    public function addHandlerAndDispatch(): void
    {
        $wasCalled = false;

        $user = new UserWithPhoto(100, 'Test', 'User', 'testuser', false, time(), null, null, null);
        $update = new BotStartedUpdate(time(), 12345, $user, null, 'ru-RU');

        $this->dispatcher->addHandler(
            UpdateType::BotStarted,
            function ($receivedUpdate, $receivedApi) use (&$wasCalled, $update) {
                $this->assertSame($update, $receivedUpdate);
                $this->assertSame($this->apiMock, $receivedApi);
                $wasCalled = true;
            }
        );

        $this->dispatcher->dispatch($update);

        $this->assertTrue($wasCalled, 'Handler for BotStarted update was not called.');
    }

    #[Test]
    public function onCommandDispatch(): void
    {
        $commandCalled = false;
        $messageHandlerCalled = false;

        $messageBody = new MessageBody('mid1', 1, '/start with args', null, null);
        $sender = new UserWithPhoto(101, 'Cmd', 'Sender', 'cmdsender', false, time(), null, null, null);
        $recipient = new Recipient(ChatType::Dialog, 101, null);
        $message = new Message(time(), $recipient, $messageBody, $sender, null, null, null);
        $update = new MessageCreatedUpdate(time(), $message, 'ru-RU');

        $this->dispatcher->onCommand('/start', function ($receivedUpdate) use (&$commandCalled, $update) {
            $this->assertSame($update, $receivedUpdate);
            $commandCalled = true;
        });

        $this->dispatcher->onMessageCreated(function () use (&$messageHandlerCalled) {
            $messageHandlerCalled = true;
        });

        $this->dispatcher->dispatch($update);

        $this->assertTrue($commandCalled, 'onCommand handler was not called.');
        $this->assertFalse(
            $messageHandlerCalled,
            'onMessageCreated handler should not be called when a command matches.',
        );
    }

    #[Test]
    public function messageWithoutCommandTriggersGenericHandler(): void
    {
        $commandCalled = false;
        $messageHandlerCalled = false;

        $messageBody = new MessageBody('mid2', 2, 'Hello world', null, null);
        $sender = new UserWithPhoto(102, 'Msg', 'Sender', 'msgsender', false, time(), null, null, null);
        $recipient = new Recipient(ChatType::Dialog, 102, null);
        $message = new Message(time(), $recipient, $messageBody, $sender, null, null, null);
        $update = new MessageCreatedUpdate(time(), $message, 'en-US');

        $this->dispatcher->onCommand('/start', function () use (&$commandCalled) {
            $commandCalled = true;
        });

        $this->dispatcher->onMessageCreated(function ($receivedUpdate) use (&$messageHandlerCalled, $update) {
            $this->assertSame($update, $receivedUpdate);
            $messageHandlerCalled = true;
        });

        $this->dispatcher->dispatch($update);

        $this->assertFalse($commandCalled, 'onCommand handler should not be called for a regular message.');
        $this->assertTrue($messageHandlerCalled, 'onMessageCreated handler was not called.');
    }
}
