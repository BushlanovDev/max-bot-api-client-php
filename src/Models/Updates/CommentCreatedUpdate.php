<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Models\Updates;

use BushlanovDev\MaxMessengerBot\Enums\UpdateType;
use BushlanovDev\MaxMessengerBot\Models\Message;

/**
 * You will get this `update` as soon as a comment is created.
 * The bot receives it only if it is an administrator of the channel with the `read_all_messages` permission.
 */
final readonly class CommentCreatedUpdate extends AbstractUpdate
{
    /**
     * @param int $timestamp Unix-time when event has occurred.
     * @param Message $message Newly created comment.
     */
    public function __construct(
        int $timestamp,
        public Message $message,
    ) {
        parent::__construct(UpdateType::CommentCreated, $timestamp);
    }
}
