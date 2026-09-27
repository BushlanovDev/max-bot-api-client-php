<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Models;

use BushlanovDev\MaxMessengerBot\Enums\ChatType;

/**
 * Message recipient. Could be user or chat.
 */
final readonly class Recipient extends AbstractModel
{
    /**
     * @param ChatType $chatType Chat type (dialog, chat or channel).
     * @param int|null $userId User identifier, if message was sent to user.
     * @param int|null $chatId Chat identifier.
     * @param string|null $postId Identifier of the commented post, for comments.
     */
    public function __construct(
        public ChatType $chatType,
        public ?int $userId,
        public ?int $chatId,
        public ?string $postId = null,
    ) {
    }
}
