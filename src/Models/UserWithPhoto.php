<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Models;

final readonly class UserWithPhoto extends AbstractUser
{
    /**
     * @param int $userId Users identifier.
     * @param string $firstName Users first name.
     * @param string|null $lastName Users last name.
     * @param string|null $username Unique public user name. Can be `null` if user is not accessible or it is not set.
     * @param bool $isBot `true` if user is bot.
     * @param int|null $lastActivityTime Time of last user activity in Max (Unix timestamp in milliseconds).
     * @param string|null $description UserWithPhoto description. Can be `null` if user did not fill it out.
     * @param string|null $avatarUrl URL of avatar.
     * @param string|null $fullAvatarUrl URL of avatar of a bigger size.
     */
    public function __construct(
        int $userId,
        string $firstName,
        ?string $lastName,
        ?string $username,
        bool $isBot,
        ?int $lastActivityTime,
        public ?string $description,
        public ?string $avatarUrl,
        public ?string $fullAvatarUrl,
    ) {
        parent::__construct($userId, $firstName, $lastName, $username, $isBot, $lastActivityTime);
    }
}
