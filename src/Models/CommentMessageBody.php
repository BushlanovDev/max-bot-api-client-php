<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Models;

use BushlanovDev\MaxMessengerBot\Models\Markup\AbstractMarkup;

/**
 * Body of a comment. Unlike MessageBody, comments have no attachments.
 */
final readonly class CommentMessageBody extends AbstractModel
{
    /**
     * @param string $mid Unique identifier of the comment.
     * @param int $seq Sequence identifier of the comment in the chat.
     * @param string|null $text Comment text.
     * @param AbstractMarkup[]|null $markup Comment text markup.
     */
    public function __construct(
        public string $mid,
        public int $seq,
        public ?string $text,
        public ?array $markup,
    ) {
    }
}
