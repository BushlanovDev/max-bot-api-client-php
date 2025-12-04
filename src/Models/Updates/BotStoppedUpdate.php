<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Models\Updates;

use BushlanovDev\MaxMessengerBot\Enums\UpdateType;
use BushlanovDev\MaxMessengerBot\Models\User;

/**
 * The bot receives this type of update as soon as the user stops the bot.
 */
final readonly class BotStoppedUpdate extends AbstractUpdate
{
    /**
     * @param int $timestamp Unix-time when event has occurred.
     * @param int $chatId Dialog identifier where event has occurred.
     * @param User $user User pressed the 'Start' button.
     * @param string|null $userLocale Current user locale in IETF BCP 47 format.
     */
    public function __construct(
        int $timestamp,
        public int $chatId,
        public User $user,
        public ?string $userLocale,
    ) {
        parent::__construct(UpdateType::BotStopped, $timestamp);
    }
}
