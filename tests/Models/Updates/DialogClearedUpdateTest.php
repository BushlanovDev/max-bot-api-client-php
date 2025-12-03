<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Tests\Models\Updates;

use BushlanovDev\MaxMessengerBot\Enums\UpdateType;
use BushlanovDev\MaxMessengerBot\Models\Updates\DialogClearedUpdate;
use BushlanovDev\MaxMessengerBot\Models\User;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DialogClearedUpdate::class)]
#[UsesClass(User::class)]
final class DialogClearedUpdateTest extends TestCase
{
    #[Test]
    public function canBeCreatedFromArray(): void
    {
        $data = [
            'update_type' => UpdateType::DialogCleared->value,
            'timestamp' => 1678886400000,
            'chat_id' => 123,
            'user' => [
                'user_id' => 123,
                'first_name' => 'John',
                'last_name' => 'Doe',
                'is_bot' => false,
                'last_activity_time' => 1678886400000,
            ],
            'user_locale' => 'ru-ru',
        ];

        $update = DialogClearedUpdate::fromArray($data);

        $this->assertInstanceOf(DialogClearedUpdate::class, $update);
        $this->assertSame(UpdateType::DialogCleared, $update->updateType);
        $this->assertSame(123, $update->user->userId);
        $this->assertSame('John', $update->user->firstName);
        $this->assertSame('Doe', $update->user->lastName);
        $this->assertSame('ru-ru', $update->userLocale);
    }
}
