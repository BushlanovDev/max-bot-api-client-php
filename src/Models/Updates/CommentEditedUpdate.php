<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Models\Updates;

use BushlanovDev\MaxMessengerBot\Enums\UpdateType;
use BushlanovDev\MaxMessengerBot\Models\Message;

/**
 * You will get this `update` as soon as a comment is edited.
 * The bot receives it only if it is an administrator of the channel with the `read_all_messages` permission.
 */
final readonly class CommentEditedUpdate extends AbstractUpdate
{
    /**
     * @param int $timestamp Unix-time when event has occurred.
     * @param Message $message Edited comment.
     */
    public function __construct(
        int $timestamp,
        public Message $message,
    ) {
        parent::__construct(UpdateType::CommentEdited, $timestamp);
    }
}
