<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Models;

use BushlanovDev\MaxMessengerBot\Attributes\ArrayOf;

/**
 * Bot commands information.
 */
final readonly class BotCommandsInfo extends AbstractModel
{
    /**
     * @param BotCommand[]|null $commands Commands supported by the bot (up to 32 elements).
     */
    public function __construct(
        #[ArrayOf(BotCommand::class)]
        public ?array $commands,
    ) {
    }
}
