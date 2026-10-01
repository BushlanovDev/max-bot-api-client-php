<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Models;

/**
 * A comment to a post in a channel. Unlike Message, it has no public URL and no attachments.
 */
final readonly class CommentMessage extends AbstractModel
{
    /**
     * @param int $timestamp Unix-time when the comment was created.
     * @param Recipient $recipient Comment recipient, always a channel; carries the commented post in $postId.
     * @param CommentMessageBody $body Body of the comment.
     * @param UserWithPhoto|null $sender User who sent the comment. Can be null if posted on behalf of a channel.
     * @param CommentLinkedMessage|null $link Forwarded or replied comment.
     * @param MessageStat|null $stat Comment statistics.
     */
    public function __construct(
        public int $timestamp,
        public Recipient $recipient,
        public CommentMessageBody $body,
        public ?UserWithPhoto $sender,
        public ?CommentLinkedMessage $link,
        public ?MessageStat $stat,
    ) {
    }
}
