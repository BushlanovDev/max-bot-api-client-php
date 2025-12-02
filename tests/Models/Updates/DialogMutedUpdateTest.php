<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Tests\Models\Updates;

use BushlanovDev\MaxMessengerBot\Enums\UpdateType;
use BushlanovDev\MaxMessengerBot\Models\Updates\DialogMutedUpdate;
use BushlanovDev\MaxMessengerBot\Models\User;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DialogMutedUpdate::class)]
#[UsesClass(User::class)]
class DialogMutedUpdateTest extends TestCase
{
    #[Test]
    public function canBeCreatedFromArray(): void
    {
        $data = [
            'update_type' => UpdateType::DialogMuted->value,
            'timestamp' => 1678886400000,
            'chat_id' => 123,
            'user' => [
                'user_id' => 123,
                'first_name' => 'John',
                'last_name' => 'Doe',
                'is_bot' => false,
                'last_activity_time' => 1678886400000,
            ],
            'mutedUntil' => 1678886400000,
            'user_locale' => 'ru-ru',
        ];

        $update = DialogMutedUpdate::fromArray($data);

        $this->assertInstanceOf(DialogMutedUpdate::class, $update);
        $this->assertSame(UpdateType::DialogMuted, $update->updateType);
        $this->assertSame(123, $update->user->userId);
        $this->assertSame('John', $update->user->firstName);
        $this->assertSame('Doe', $update->user->lastName);
        $this->assertSame('ru-ru', $update->userLocale);
    }
}
