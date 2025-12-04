<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Models\Updates;

use BushlanovDev\MaxMessengerBot\Enums\UpdateType;
use BushlanovDev\MaxMessengerBot\Models\User;

/**
 * Event when a user mutes a conversation with a bot.
 */
final readonly class DialogMutedUpdate extends AbstractUpdate
{
    /**
     * @param int $timestamp Unix-time when event has occurred.
     * @param int $chatId Dialog identifier where event has occurred.
     * @param User $user User pressed the 'Start' button.
     * @param int|null $mutedUntil The time in Unix format before which the dialog was disabled.
     * @param string|null $userLocale Current user locale in IETF BCP 47 format.
     */
    public function __construct(
        int $timestamp,
        public int $chatId,
        public User $user,
        public ?int $mutedUntil,
        public ?string $userLocale,
    ) {
        parent::__construct(UpdateType::DialogMuted, $timestamp);
    }
}
