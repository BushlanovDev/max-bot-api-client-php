<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Tests\Models\Updates;

use BushlanovDev\MaxMessengerBot\Enums\UpdateType;
use BushlanovDev\MaxMessengerBot\Models\Updates\CommentRemovedUpdate;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(CommentRemovedUpdate::class)]
final class CommentRemovedUpdateTest extends TestCase
{
    #[Test]
    public function canBeCreatedFromArray(): void
    {
        $update = CommentRemovedUpdate::fromArray([
            'update_type' => UpdateType::CommentRemoved->value,
            'timestamp' => 1678886400000,
            'message_id' => 'mid.comment',
            'chat_id' => -100,
            'user_id' => 42,
            'post_id' => 'mid.post',
        ]);

        $this->assertSame(UpdateType::CommentRemoved, $update->updateType);
        $this->assertSame('mid.comment', $update->messageId);
        $this->assertSame(-100, $update->chatId);
        $this->assertSame(42, $update->userId);
        $this->assertSame('mid.post', $update->postId);
    }
}
