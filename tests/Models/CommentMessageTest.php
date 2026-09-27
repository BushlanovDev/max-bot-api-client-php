<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Tests\Models;

use BushlanovDev\MaxMessengerBot\Enums\MessageLinkType;
use BushlanovDev\MaxMessengerBot\Models\CommentLinkedMessage;
use BushlanovDev\MaxMessengerBot\Models\CommentMessage;
use BushlanovDev\MaxMessengerBot\Models\CommentMessageBody;
use BushlanovDev\MaxMessengerBot\Models\MessageStat;
use BushlanovDev\MaxMessengerBot\Models\Recipient;
use BushlanovDev\MaxMessengerBot\Models\UserWithPhoto;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CommentMessage::class)]
#[CoversClass(CommentMessageBody::class)]
#[CoversClass(CommentLinkedMessage::class)]
#[UsesClass(Recipient::class)]
#[UsesClass(UserWithPhoto::class)]
#[UsesClass(MessageStat::class)]
final class CommentMessageTest extends TestCase
{
    #[Test]
    public function canBeCreatedFromArrayWithAllData(): void
    {
        $comment = CommentMessage::fromArray([
            'timestamp' => 1678886400000,
            'recipient' => ['chat_type' => 'channel', 'chat_id' => -100, 'post_id' => 'mid.post'],
            'body' => ['mid' => 'mid.comment', 'seq' => 7, 'text' => 'Agree', 'markup' => null],
            'sender' => ['user_id' => 42, 'first_name' => 'Anna', 'is_bot' => false],
            'link' => [
                'type' => 'forward',
                'message' => ['mid' => 'mid.original', 'seq' => 1, 'text' => 'Original', 'markup' => null],
                'sender' => null,
                'chat_id' => -200,
            ],
            'stat' => ['views' => 10],
        ]);

        $this->assertSame(1678886400000, $comment->timestamp);
        $this->assertSame('mid.post', $comment->recipient->postId);
        $this->assertSame('mid.comment', $comment->body->mid);
        $this->assertSame(7, $comment->body->seq);
        $this->assertSame('Agree', $comment->body->text);
        $this->assertSame('Anna', $comment->sender?->firstName);
        $this->assertSame(MessageLinkType::Forward, $comment->link?->type);
        $this->assertSame('Original', $comment->link?->message->text);
        $this->assertSame(-200, $comment->link?->chatId);
        $this->assertSame(10, $comment->stat?->views);
    }

    #[Test]
    public function commentOnBehalfOfChannelHasNoSender(): void
    {
        $comment = CommentMessage::fromArray([
            'timestamp' => 1,
            'recipient' => ['chat_type' => 'channel', 'chat_id' => -100],
            'body' => ['mid' => 'mid.comment', 'seq' => 1],
        ]);

        $this->assertNull($comment->sender);
        $this->assertNull($comment->link);
        $this->assertNull($comment->body->text);
        $this->assertNull($comment->body->markup);
    }
}
