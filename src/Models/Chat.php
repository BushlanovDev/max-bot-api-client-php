<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Models;

use BushlanovDev\MaxMessengerBot\Enums\ChatStatus;
use BushlanovDev\MaxMessengerBot\Enums\ChatType;

/**
 * Information about the chat.
 */
final readonly class Chat extends AbstractModel
{
    /**
     * @param int $chatId Chats identifier.
     * @param ChatType $type Type of chat. One of: `dialog`, `chat`, `channel`.
     * @param ChatStatus $status Chat status.
     * @param int $lastEventTime Time of last event occurred in chat.
     * @param int $participantsCount Number of people in chat. Always 2 for `dialog` chat type.
     * @param bool $isPublic Is current chat publicly available. Always false for dialogs.
     * @param string|null $title Visible title of chat. Can be null for dialogs.
     * @param Image|null $icon Icon of chat.
     * @param int|null $ownerId Identifier of chat owner. Visible only for chat admins
     * @param string|null $link Link on chat.
     * @param string|null $description Chat description.
     * @param UserWithPhoto|null $dialogWithUser Another user in conversation. For `dialog` type chats only.
     * @param int|null $messagesCount Messages count in chat. Only for group chats and channels. Not available for dialogs.
     * @param string|null $chatMessageId Identifier of message that contains `chat` button initialized chat. Deprecated: not in the schema since 0.0.33.
     * @param Message|null $pinnedMessage Pinned message in chat or channel. Returned only when single chat is requested.
     * @param array<string, int>|null $participants List of participants in chat. Returned only when single chat is requested.
     */
    public function __construct(
        public int $chatId,
        public ChatType $type,
        public ChatStatus $status,
        public int $lastEventTime,
        public int $participantsCount,
        public bool $isPublic,
        public ?string $title,
        public ?Image $icon,
        public ?int $ownerId,
        public ?string $link,
        public ?string $description,
        public ?UserWithPhoto $dialogWithUser,
        public ?int $messagesCount,
        public ?string $chatMessageId,
        public ?Message $pinnedMessage,
        public ?array $participants,
    ) {
    }
}
