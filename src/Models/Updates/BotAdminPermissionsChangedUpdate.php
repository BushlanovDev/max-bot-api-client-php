<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Models\Updates;

use BushlanovDev\MaxMessengerBot\Attributes\ArrayOf;
use BushlanovDev\MaxMessengerBot\Enums\ChatAdminPermission;
use BushlanovDev\MaxMessengerBot\Enums\UpdateType;

/**
 * The bot receives this update when its admin permissions change.
 */
final readonly class BotAdminPermissionsChangedUpdate extends AbstractUpdate
{
    /**
     * @param int $timestamp Unix-time when event has occurred.
     * @param int $chatId Chat identifier where event has occurred.
     * @param int $userId User or bot who changed the bot's admin permissions.
     * @param int $botId Bot whose admin permissions changed.
     * @param bool $isChannel Whether the permissions changed in a channel.
     * @param bool $isAdmin Whether the bot is an admin in the chat or channel.
     * @param ChatAdminPermission[]|null $permissions The bot's permissions after the change.
     */
    public function __construct(
        int $timestamp,
        public int $chatId,
        public int $userId,
        public int $botId,
        public bool $isChannel,
        public bool $isAdmin,
        #[ArrayOf(ChatAdminPermission::class)]
        public ?array $permissions = null,
    ) {
        parent::__construct(UpdateType::BotAdminPermissionChanged, $timestamp);
    }
}
