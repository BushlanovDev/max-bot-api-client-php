<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Models;

use BushlanovDev\MaxMessengerBot\Attributes\ArrayOf;

/**
 * Information about the current bot.
 */
final readonly class BotInfo extends AbstractModel
{
    /**
     * @param int $userId ID user.
     * @param string $firstName User display name.
     * @param string|null $lastName User's display last name.
     * @param string|null $username Unique public name of the user, may be null if the user is not available or no name is set.
     * @param bool $isBot Is the user a bot.
     * @param int $lastActivityTime User last activity time in MAX (Unix time in milliseconds). May be irrelevant if the user has disabled the "online" status in the settings.
     * @param string|null $description User description, may be null if the user has not filled it in (up to 16000 characters).
     * @param string|null $avatarUrl Avatar URL.
     * @param string|null $fullAvatarUrl Larger Avatar URL.
     * @param BotCommand[]|null $commands Commands supported by the bot (up to 32 elements).
     */
    public function __construct(
        public int $userId,
        public string $firstName,
        public ?string $lastName,
        public ?string $username,
        public bool $isBot,
        public int $lastActivityTime,
        public ?string $description,
        public ?string $avatarUrl,
        public ?string $fullAvatarUrl,
        #[ArrayOf(BotCommand::class)]
        public ?array $commands,
    ) {
    }
}
