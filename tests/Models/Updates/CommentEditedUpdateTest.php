<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Tests\Models\Updates;

use BushlanovDev\MaxMessengerBot\Enums\UpdateType;
use BushlanovDev\MaxMessengerBot\Models\Message;
use BushlanovDev\MaxMessengerBot\Models\MessageBody;
use BushlanovDev\MaxMessengerBot\Models\Recipient;
use BushlanovDev\MaxMessengerBot\Models\Updates\CommentEditedUpdate;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CommentEditedUpdate::class)]
#[UsesClass(Message::class)]
#[UsesClass(MessageBody::class)]
#[UsesClass(Recipient::class)]
final class CommentEditedUpdateTest extends TestCase
{
    #[Test]
    public function canBeCreatedFromArray(): void
    {
        $update = CommentEditedUpdate::fromArray([
            'update_type' => UpdateType::CommentEdited->value,
            'timestamp' => 1678886400000,
            'message' => [
                'timestamp' => 1678886400000,
                'body' => ['mid' => 'mid.comment', 'seq' => 5, 'text' => 'Nice post'],
                'recipient' => ['chat_type' => 'channel', 'chat_id' => -100],
            ],
        ]);

        $this->assertSame(UpdateType::CommentEdited, $update->updateType);
        $this->assertSame(1678886400000, $update->timestamp);
        $this->assertSame('mid.comment', $update->message->body?->mid);
        $this->assertSame('Nice post', $update->message->body?->text);
    }
}
