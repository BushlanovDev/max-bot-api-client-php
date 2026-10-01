<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Models\Updates;

use BushlanovDev\MaxMessengerBot\Enums\UpdateType;

/**
 * You will get this `update` as soon as a comment is removed.
 * The bot receives it only if it is an administrator of the channel with the `read_all_messages` permission.
 */
final readonly class CommentRemovedUpdate extends AbstractUpdate
{
    /**
     * @param int $timestamp Unix-time when the event has occurred.
     * @param string $messageId Identifier of the removed comment.
     * @param int $chatId Chat identifier where the comment has been deleted.
     * @param int $userId User who deleted this comment.
     * @param string|null $postId Identifier of the commented post.
     */
    public function __construct(
        int $timestamp,
        public string $messageId,
        public int $chatId,
        public int $userId,
        public ?string $postId,
    ) {
        parent::__construct(UpdateType::CommentRemoved, $timestamp);
    }
}
