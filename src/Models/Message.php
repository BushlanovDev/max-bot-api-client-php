<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Models;

/**
 * Message.
 */
final readonly class Message extends AbstractModel
{
    /**
     * @param int $timestamp Unix-time when message was created.
     * @param Recipient $recipient Message recipient. Could be user or chat.
     * @param MessageBody|null $body Body of created message. Text + attachments.
     * @param User|null $sender User who sent this message. Can be null if message has been posted on behalf of a channel.
     * @param string|null $url Message public URL. Can be null for dialogs or non-public chats/channels.
     * @param LinkedMessage|null $link Forwarded or replied message.
     * @param MessageStat|null $stat Message statistics. Available only for channels.
     * @param int|null $chatId
     * @param int|null $recipientId
     * @param string|null $messageId
     */
    public function __construct(
        public int $timestamp,
        public Recipient $recipient,
        public ?MessageBody $body,
        public ?User $sender,
        public ?string $url,
        public ?LinkedMessage $link,
        public ?MessageStat $stat,
        public ?int $chatId,
        public ?int $recipientId,
        public ?string $messageId,
    ) {
    }
}
