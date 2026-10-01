<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Tests\Models\Updates;

use BushlanovDev\MaxMessengerBot\Attributes\ArrayOf;
use BushlanovDev\MaxMessengerBot\Enums\ChatAdminPermission;
use BushlanovDev\MaxMessengerBot\Enums\UpdateType;
use BushlanovDev\MaxMessengerBot\Models\Updates\BotAdminPermissionsChangedUpdate;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(BotAdminPermissionsChangedUpdate::class)]
#[UsesClass(ArrayOf::class)]
final class BotAdminPermissionsChangedUpdateTest extends TestCase
{
    #[Test]
    public function canBeCreatedFromArray(): void
    {
        $update = BotAdminPermissionsChangedUpdate::fromArray([
            'update_type' => UpdateType::BotAdminPermissionChanged->value,
            'timestamp' => 1678886400000,
            'chat_id' => -100,
            'user_id' => 42,
            'bot_id' => 7,
            'is_channel' => true,
            'is_admin' => true,
            'permissions' => ['read_all_messages', 'pin_message'],
        ]);

        $this->assertSame(UpdateType::BotAdminPermissionChanged, $update->updateType);
        $this->assertSame(-100, $update->chatId);
        $this->assertSame(42, $update->userId);
        $this->assertSame(7, $update->botId);
        $this->assertTrue($update->isChannel);
        $this->assertTrue($update->isAdmin);
        $this->assertSame([ChatAdminPermission::ReadAllMessages, ChatAdminPermission::PinMessage], $update->permissions);
    }

    #[Test]
    public function permissionsAreNullWhenTheBotIsNoLongerAdmin(): void
    {
        $update = BotAdminPermissionsChangedUpdate::fromArray([
            'update_type' => UpdateType::BotAdminPermissionChanged->value,
            'timestamp' => 1678886400000,
            'chat_id' => -100,
            'user_id' => 42,
            'bot_id' => 7,
            'is_channel' => false,
            'is_admin' => false,
        ]);

        $this->assertFalse($update->isAdmin);
        $this->assertNull($update->permissions);
    }
}
