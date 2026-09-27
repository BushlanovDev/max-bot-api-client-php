<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Tests\Models;

use BushlanovDev\MaxMessengerBot\Models\UserWithPhoto;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(UserWithPhoto::class)]
final class UserWithPhotoTest extends TestCase
{
    #[Test]
    public function optionalFieldsMayBeAbsent(): void
    {
        // Since schema 0.0.33 only user_id, first_name and is_bot are required
        $user = UserWithPhoto::fromArray([
            'user_id' => 123,
            'first_name' => 'John',
            'is_bot' => false,
        ]);

        $this->assertSame(123, $user->userId);
        $this->assertNull($user->lastName);
        $this->assertNull($user->username);
        $this->assertNull($user->lastActivityTime);
        $this->assertNull($user->avatarUrl);
    }

    #[Test]
    public function canBeCreatedFromArray(): void
    {
        $data = [
            'description' => 'Description',
            'avatar_url' => 'https://example.com/avatar.jpg',
            'full_avatar_url' => 'https://example.com/full_avatar.jpg',
            'user_id' => 123,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'username' => 'johndoe',
            'is_bot' => false,
            'last_activity_time' => 1678886400000,
        ];

        $user = UserWithPhoto::fromArray($data);

        $this->assertInstanceOf(UserWithPhoto::class, $user);
        $this->assertSame($data['user_id'], $user->userId);
        $this->assertSame($data['first_name'], $user->firstName);
        $this->assertSame($data['last_name'], $user->lastName);
        $this->assertSame($data['username'], $user->username);
        $this->assertSame($data['is_bot'], $user->isBot);
        $this->assertSame($data['last_activity_time'], $user->lastActivityTime);
        $this->assertSame($data['description'], $user->description);
        $this->assertSame($data['avatar_url'], $user->avatarUrl);
        $this->assertSame($data['full_avatar_url'], $user->fullAvatarUrl);

        $array = $user->toArray();

        $this->assertIsArray($array);
        $this->assertSame($data, $array);
    }

    #[Test]
    public function canBeCreatedFromArrayWithOptionalDataNull(): void
    {
        $data = [
            'user_id' => 123,
            'first_name' => 'John',
            'is_bot' => false,
            'last_activity_time' => 1678886400000,
        ];

        $user = UserWithPhoto::fromArray($data);

        $this->assertInstanceOf(UserWithPhoto::class, $user);
        $this->assertSame($data['user_id'], $user->userId);
        $this->assertSame($data['first_name'], $user->firstName);
        $this->assertNull($user->lastName);
        $this->assertNull($user->username);
        $this->assertSame($data['is_bot'], $user->isBot);
        $this->assertSame($data['last_activity_time'], $user->lastActivityTime);
        $this->assertNull($user->description);
        $this->assertNull($user->avatarUrl);
        $this->assertNull($user->fullAvatarUrl);

        $array = $user->toArray();

        $this->assertIsArray($array);
        $array = array_filter($array, fn($item) => null !== $item);
        $this->assertSame($data, $array);
    }
}
