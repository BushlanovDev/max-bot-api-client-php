<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Models\Updates;

use BushlanovDev\MaxMessengerBot\Enums\UpdateType;
use BushlanovDev\MaxMessengerBot\Models\Chat;

/**
 * Bot will get this update when a chat has been created as soon as
 * the first user clicked a `chat` button.
 *
 * @deprecated Not part of the Bot API schema since 0.0.33.
 */
final readonly class MessageChatCreatedUpdate extends AbstractUpdate
{
    /**
     * @param int $timestamp Unix-time when the event has occurred.
     * @param Chat $chat The created chat.
     * @param string $messageId Message identifier where the button has been clicked.
     * @param string|null $startPayload Payload from the chat button.
     */
    public function __construct(
        int $timestamp,
        public Chat $chat,
        public string $messageId,
        public ?string $startPayload,
    ) {
        parent::__construct(UpdateType::MessageChatCreated, $timestamp);
    }
}
