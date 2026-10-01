<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Models;

use BushlanovDev\MaxMessengerBot\Enums\MessageLinkType;

/**
 * A forwarded or replied comment linked to a comment.
 */
final readonly class CommentLinkedMessage extends AbstractModel
{
    /**
     * @param MessageLinkType $type Type of linked message (forward or reply).
     * @param CommentMessageBody $message The body of the original comment.
     * @param UserWithPhoto|null $sender The sender of the original comment. Can be null if posted on behalf of a channel.
     * @param int|null $chatId The chat where the comment was originally posted.
     */
    public function __construct(
        public MessageLinkType $type,
        public CommentMessageBody $message,
        public ?UserWithPhoto $sender,
        public ?int $chatId,
    ) {
    }
}
