<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Tests\Models;

use BushlanovDev\MaxMessengerBot\Models\LinkedMessage;
use BushlanovDev\MaxMessengerBot\Models\Message;
use BushlanovDev\MaxMessengerBot\Models\MessageBody;
use BushlanovDev\MaxMessengerBot\Models\MessageStat;
use BushlanovDev\MaxMessengerBot\Models\Recipient;
use BushlanovDev\MaxMessengerBot\Models\User;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Message::class)]
#[UsesClass(MessageBody::class)]
#[UsesClass(Recipient::class)]
#[UsesClass(User::class)]
#[UsesClass(LinkedMessage::class)]
#[UsesClass(MessageStat::class)]
final class MessageTest extends TestCase
{
    #[Test]
    public function canBeCreatedFromArrayWithAllData(): void
    {
        $data = [
            'timestamp' => time(),
            'recipient' => [
                'chat_type' => 'dialog',
                'user_id' => 123,
                'chat_id' => null,
            ],
            'body' => [
                'mid' => 'mid.456.xyz',
                'seq' => 101,
                'text' => 'Hello, **world**!',
                'attachments' => null,
                'markup' => null,
            ],
            'sender' =>[
                'user_id' => 123,
                'first_name' => 'John',
                'last_name' => 'Doe',
                'username' => 'johndoe',
                'is_bot' => false,
                'last_activity_time' => 1678886400000,
            ],
            'url' => 'https://max.ru/message/123',
            'link' => [
                'type' => 'reply',
                'message' => [
                    'mid' => 'mid.original.123',
                    'seq' => 10,
                    'text' => 'Original message',
                    'attachments' => null,
                    'markup' => null,
                ],
                'sender' => null,
                'chat_id' => null,
            ],
            'stat' => [
                'views' => 500,
            ],
            'chat_id' => null,
            'recipient_id' => null,
            'message_id' => null,
        ];

        $message = Message::fromArray($data);

        $this->assertInstanceOf(Message::class, $message);
        $this->assertSame($data['timestamp'], $message->timestamp);
        $this->assertInstanceOf(MessageBody::class, $message->body);
        $this->assertInstanceOf(Recipient::class, $message->recipient);
        $this->assertInstanceOf(User::class, $message->sender);
        $this->assertSame($data['url'], $message->url);
        $this->assertInstanceOf(MessageStat::class, $message->stat);
        $this->assertSame(500, $message->stat->views);

        $array = $message->toArray();

        $this->assertIsArray($array);
        $this->assertSame($data, $array);

        $this->assertInstanceOf(LinkedMessage::class, $message->link);
        $this->assertSame('mid.original.123', $message->link->message->mid);
    }
}
