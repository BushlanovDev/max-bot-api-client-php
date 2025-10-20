<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot;

use BushlanovDev\MaxMessengerBot\Enums\AttachmentType;
use BushlanovDev\MaxMessengerBot\Enums\InlineButtonType;
use BushlanovDev\MaxMessengerBot\Enums\MarkupType;
use BushlanovDev\MaxMessengerBot\Enums\ReplyButtonType;
use BushlanovDev\MaxMessengerBot\Enums\UpdateType;
use BushlanovDev\MaxMessengerBot\Models\Attachments\AbstractAttachment;
use BushlanovDev\MaxMessengerBot\Models\Attachments\AudioAttachment;
use BushlanovDev\MaxMessengerBot\Models\Attachments\Buttons\Inline\AbstractInlineButton;
use BushlanovDev\MaxMessengerBot\Models\Attachments\Buttons\Inline\CallbackButton;
use BushlanovDev\MaxMessengerBot\Models\Attachments\Buttons\Inline\ChatButton;
use BushlanovDev\MaxMessengerBot\Models\Attachments\Buttons\Inline\LinkButton;
use BushlanovDev\MaxMessengerBot\Models\Attachments\Buttons\Inline\OpenAppButton;
use BushlanovDev\MaxMessengerBot\Models\Attachments\Buttons\Inline\RequestContactButton;
use BushlanovDev\MaxMessengerBot\Models\Attachments\Buttons\Inline\RequestGeoLocationButton;
use BushlanovDev\MaxMessengerBot\Models\Attachments\Buttons\Reply\AbstractReplyButton;
use BushlanovDev\MaxMessengerBot\Models\Attachments\Buttons\Reply\SendContactButton;
use BushlanovDev\MaxMessengerBot\Models\Attachments\Buttons\Reply\SendGeoLocationButton;
use BushlanovDev\MaxMessengerBot\Models\Attachments\Buttons\Reply\SendMessageButton;
use BushlanovDev\MaxMessengerBot\Models\Attachments\ContactAttachment;
use BushlanovDev\MaxMessengerBot\Models\Attachments\DataAttachment;
use BushlanovDev\MaxMessengerBot\Models\Attachments\FileAttachment;
use BushlanovDev\MaxMessengerBot\Models\Attachments\InlineKeyboardAttachment;
use BushlanovDev\MaxMessengerBot\Models\Attachments\LocationAttachment;
use BushlanovDev\MaxMessengerBot\Models\Attachments\PhotoAttachment;
use BushlanovDev\MaxMessengerBot\Models\Attachments\ReplyKeyboardAttachment;
use BushlanovDev\MaxMessengerBot\Models\Attachments\ShareAttachment;
use BushlanovDev\MaxMessengerBot\Models\Attachments\StickerAttachment;
use BushlanovDev\MaxMessengerBot\Models\Attachments\VideoAttachment;
use BushlanovDev\MaxMessengerBot\Models\BotInfo;
use BushlanovDev\MaxMessengerBot\Models\Chat;
use BushlanovDev\MaxMessengerBot\Models\ChatList;
use BushlanovDev\MaxMessengerBot\Models\ChatMember;
use BushlanovDev\MaxMessengerBot\Models\ChatMembersList;
use BushlanovDev\MaxMessengerBot\Models\Markup\AbstractMarkup;
use BushlanovDev\MaxMessengerBot\Models\Markup\EmphasizedMarkup;
use BushlanovDev\MaxMessengerBot\Models\Markup\HeadingMarkup;
use BushlanovDev\MaxMessengerBot\Models\Markup\HighlightedMarkup;
use BushlanovDev\MaxMessengerBot\Models\Markup\LinkMarkup;
use BushlanovDev\MaxMessengerBot\Models\Markup\MonospacedMarkup;
use BushlanovDev\MaxMessengerBot\Models\Markup\StrikethroughMarkup;
use BushlanovDev\MaxMessengerBot\Models\Markup\StrongMarkup;
use BushlanovDev\MaxMessengerBot\Models\Markup\UnderlineMarkup;
use BushlanovDev\MaxMessengerBot\Models\Markup\UserMentionMarkup;
use BushlanovDev\MaxMessengerBot\Models\Message;
use BushlanovDev\MaxMessengerBot\Models\MessageBody;
use BushlanovDev\MaxMessengerBot\Models\Result;
use BushlanovDev\MaxMessengerBot\Models\Subscription;
use BushlanovDev\MaxMessengerBot\Models\UpdateList;
use BushlanovDev\MaxMessengerBot\Models\Updates\AbstractUpdate;
use BushlanovDev\MaxMessengerBot\Models\Updates\BotAddedToChatUpdate;
use BushlanovDev\MaxMessengerBot\Models\Updates\BotRemovedFromChatUpdate;
use BushlanovDev\MaxMessengerBot\Models\Updates\BotStartedUpdate;
use BushlanovDev\MaxMessengerBot\Models\Updates\ChatTitleChangedUpdate;
use BushlanovDev\MaxMessengerBot\Models\Updates\MessageCallbackUpdate;
use BushlanovDev\MaxMessengerBot\Models\Updates\MessageChatCreatedUpdate;
use BushlanovDev\MaxMessengerBot\Models\Updates\MessageCreatedUpdate;
use BushlanovDev\MaxMessengerBot\Models\Updates\MessageEditedUpdate;
use BushlanovDev\MaxMessengerBot\Models\Updates\MessageRemovedUpdate;
use BushlanovDev\MaxMessengerBot\Models\Updates\UserAddedToChatUpdate;
use BushlanovDev\MaxMessengerBot\Models\Updates\UserRemovedFromChatUpdate;
use BushlanovDev\MaxMessengerBot\Models\UploadEndpoint;
use BushlanovDev\MaxMessengerBot\Models\VideoAttachmentDetails;
use LogicException;
use ReflectionException;

/**
 * Creates DTOs from raw associative arrays returned by the API client.
 */
class ModelFactory
{
    /**
     * Simple response to request.
     *
     * @param array<string, mixed> $data
     *
     * @return Result
     * @throws ReflectionException
     */
    public function createResult(array $data): Result
    {
        return Result::fromArray($data);
    }

    /**
     * Information about the current bot.
     *
     * @param array<string, mixed> $data
     *
     * @return BotInfo
     * @throws ReflectionException
     */
    public function createBotInfo(array $data): BotInfo
    {
        return BotInfo::fromArray($data);
    }

    /**
     * Information about webhook subscription.
     *
     * @param array<string, mixed> $data
     *
     * @return Subscription
     * @throws ReflectionException
     */
    public function createSubscription(array $data): Subscription
    {
        return Subscription::fromArray($data);
    }

    /**
     * List of all active webhook subscriptions.
     *
     * @param array<string, mixed> $data
     *
     * @return Subscription[]
     * @throws ReflectionException
     */
    public function createSubscriptions(array $data): array
    {
        return isset($data['subscriptions']) && is_array($data['subscriptions'])
            ? array_map([$this, 'createSubscription'], $data['subscriptions'])
            : [];
    }

    /**
     * Creates a Message from the specific response structure of the sendMessage endpoint.
     *
     * @param array<string, mixed> $data The raw response from the client.
     *
     * @return Message
     * @throws ReflectionException
     */
    public function createMessageFromSendResponse(array $data): Message
    {
        $messageData = $data['message'];

        $topLevelData = [
            'chat_id' => $data['chat_id'] ?? null,
            'recipient_id' => $data['recipient_id'] ?? null,
            'message_id' => $data['message_id'] ?? null,
        ];
        $messageData = array_merge($messageData, array_filter($topLevelData, fn($value) => $value !== null));

        if (isset($messageData['message']) && is_array($messageData['message'])) {
            $messageData['body'] = $messageData['message'];
            unset($messageData['message']);
        }

        return $this->createMessage($messageData);
    }

    /**
     * Message.
     *
     * @param array<string, mixed> $data
     *
     * @return Message
     * @throws ReflectionException
     */
    public function createMessage(array $data): Message
    {
        if (isset($data['body']) && is_array($data['body'])) {
            $data['body'] = $this->createMessageBody($data['body']);
        }

        return Message::fromArray($data);
    }

    /**
     * List of messages.
     *
     * @param array<string, mixed> $data
     *
     * @return Message[]
     */
    public function createMessages(array $data): array
    {
        return isset($data['messages']) && is_array($data['messages'])
            ? array_map([$this, 'createMessage'], $data['messages'])
            : [];
    }

    /**
     * Creates a MessageBody object from raw API data, handling polymorphic attachments and markup.
     *
     * @param array<string, mixed> $data
     *
     * @return MessageBody
     * @throws ReflectionException
     */
    private function createMessageBody(array $data): MessageBody
    {
        if (isset($data['attachments']) && is_array($data['attachments'])) {
            $data['attachments'] = array_map(
                [$this, 'createAttachment'],
                $data['attachments'],
            );
        }

        if (isset($data['markup']) && is_array($data['markup'])) {
            $data['markup'] = array_map(
                [$this, 'createMarkupElement'],
                $data['markup'],
            );
        }

        return MessageBody::fromArray($data);
    }

    /**
     * Creates a specific Attachment model based on the 'type' field.
     *
     * @param array<string, mixed> $data
     *
     * @return AbstractAttachment
     * @throws ReflectionException
     */
    public function createAttachment(array $data): AbstractAttachment
    {
        $attachmentType = AttachmentType::tryFrom($data['type'] ?? '');
        if ($attachmentType === AttachmentType::ReplyKeyboard
            && isset($data['buttons']) && is_array($data['buttons'])) {
            $data['buttons'] = array_map(
                fn($rowOfButtons) => array_map([$this, 'createReplyButton'], $rowOfButtons),
                $data['buttons'],
            );
        }

        if ($attachmentType === AttachmentType::InlineKeyboard
            && isset($data['payload']['buttons']) && is_array($data['payload']['buttons'])) {
            $data['payload']['buttons'] = array_map(
                fn($rowOfButtons) => array_map([$this, 'createInlineButton'], $rowOfButtons),
                $data['payload']['buttons']
            );
        }

        return match ($attachmentType) {
            AttachmentType::Data => DataAttachment::fromArray($data),
            AttachmentType::Share => ShareAttachment::fromArray($data),
            AttachmentType::Image => PhotoAttachment::fromArray($data),
            AttachmentType::Video => VideoAttachment::fromArray($data),
            AttachmentType::Audio => AudioAttachment::fromArray($data),
            AttachmentType::File => FileAttachment::fromArray($data),
            AttachmentType::Sticker => StickerAttachment::fromArray($data),
            AttachmentType::Contact => ContactAttachment::fromArray($data),
            AttachmentType::InlineKeyboard => InlineKeyboardAttachment::fromArray($data),
            AttachmentType::ReplyKeyboard => ReplyKeyboardAttachment::fromArray($data),
            AttachmentType::Location => LocationAttachment::fromArray($data),
            default => throw new LogicException('Unknown or unsupported attachment type: ' . ($data['type'] ?? 'none')),
        };
    }

    /**
     * Creates a specific ReplyButton model based on the 'type' field.
     *
     * @param array<string, mixed> $data
     *
     * @return AbstractReplyButton
     * @throws ReflectionException
     * @throws LogicException
     */
    public function createReplyButton(array $data): AbstractReplyButton
    {
        return match (ReplyButtonType::tryFrom($data['type'] ?? '')) {
            ReplyButtonType::Message => SendMessageButton::fromArray($data),
            ReplyButtonType::UserContact => SendContactButton::fromArray($data),
            ReplyButtonType::UserGeoLocation => SendGeoLocationButton::fromArray($data),
            default => throw new LogicException(
                'Unknown or unsupported reply button type: ' . ($data['type'] ?? 'none')
            ),
        };
    }

    /**
     * Creates a specific InlineButton model based on the 'type' field.
     *
     * @param array<string, mixed> $data
     * @return AbstractInlineButton
     * @throws ReflectionException
     * @throws LogicException
     */
    public function createInlineButton(array $data): AbstractInlineButton
    {
        return match (InlineButtonType::tryFrom($data['type'] ?? '')) {
            InlineButtonType::Callback => CallbackButton::fromArray($data),
            InlineButtonType::Link => LinkButton::fromArray($data),
            InlineButtonType::RequestContact => RequestContactButton::fromArray($data),
            InlineButtonType::RequestGeoLocation => RequestGeoLocationButton::fromArray($data),
            InlineButtonType::Chat => ChatButton::fromArray($data),
            InlineButtonType::OpenApp => OpenAppButton::fromArray($data),
            default => throw new LogicException(
                'Unknown or unsupported inline button type: ' . ($data['type'] ?? 'none')
            ),
        };
    }

    /**
     * Endpoint you should upload to your binaries.
     *
     * @param array<string, mixed> $data
     *
     * @return UploadEndpoint
     * @throws ReflectionException
     */
    public function createUploadEndpoint(array $data): UploadEndpoint
    {
        return UploadEndpoint::fromArray($data);
    }

    /**
     * Chat information.
     *
     * @param array<string, mixed> $data
     *
     * @return Chat
     * @throws ReflectionException
     */
    public function createChat(array $data): Chat
    {
        return Chat::fromArray($data);
    }

    /**
     * Creates a list of updates from a raw API response.
     *
     * @param array<string, mixed> $data Raw response data.
     *
     * @return UpdateList
     * @throws ReflectionException
     * @throws LogicException
     */
    public function createUpdateList(array $data): UpdateList
    {
        $updateObjects = [];
        if (isset($data['updates']) && is_array($data['updates'])) {
            foreach ($data['updates'] as $updateData) {
                // Here we delegate the creation of a specific update to another factory method
                $updateObjects[] = $this->createUpdate($updateData);
            }
        }

        return new UpdateList(
            $updateObjects,
            $data['marker'] ? (int)$data['marker'] : null,
        );
    }

    /**
     * Creates a specific Update model based on the 'update_type' field.
     *
     * @param array<string, mixed> $data Raw data for a single update.
     *
     * @return AbstractUpdate
     * @throws ReflectionException
     * @throws LogicException
     */
    public function createUpdate(array $data): AbstractUpdate
    {
        return match (UpdateType::tryFrom($data['update_type'] ?? '')) {
            UpdateType::MessageCreated => MessageCreatedUpdate::fromArray($data),
            UpdateType::MessageCallback => MessageCallbackUpdate::fromArray($data),
            UpdateType::MessageEdited => MessageEditedUpdate::fromArray($data),
            UpdateType::MessageRemoved => MessageRemovedUpdate::fromArray($data),
            UpdateType::BotAdded => BotAddedToChatUpdate::fromArray($data),
            UpdateType::BotRemoved => BotRemovedFromChatUpdate::fromArray($data),
            UpdateType::UserAdded => UserAddedToChatUpdate::fromArray($data),
            UpdateType::UserRemoved => UserRemovedFromChatUpdate::fromArray($data),
            UpdateType::BotStarted => BotStartedUpdate::fromArray($data),
            UpdateType::ChatTitleChanged => ChatTitleChangedUpdate::fromArray($data),
            UpdateType::MessageChatCreated => MessageChatCreatedUpdate::fromArray($data),
            default => throw new LogicException(
                'Unknown or unsupported update type received: ' . ($data['update_type'] ?? 'none')
            ),
        };
    }

    /**
     * Information about chat list.
     *
     * @param array<string, mixed> $data
     *
     * @return ChatList
     * @throws ReflectionException
     */
    public function createChatList(array $data): ChatList
    {
        return ChatList::fromArray($data);
    }

    /**
     * Creates a ChatMember object from raw API data.
     *
     * @param array<string, mixed> $data
     *
     * @return ChatMember
     * @throws ReflectionException
     */
    public function createChatMember(array $data): ChatMember
    {
        return ChatMember::fromArray($data);
    }

    /**
     * Creates a ChatMembersList object from raw API data.
     *
     * @param array<string, mixed> $data
     *
     * @return ChatMembersList
     * @throws ReflectionException
     */
    public function createChatMembersList(array $data): ChatMembersList
    {
        return ChatMembersList::fromArray($data);
    }

    /**
     * Creates a VideoAttachmentDetails object from raw API data.
     *
     * @param array<string, mixed> $data
     *
     * @return VideoAttachmentDetails
     * @throws ReflectionException
     */
    public function createVideoAttachmentDetails(array $data): VideoAttachmentDetails
    {
        return VideoAttachmentDetails::fromArray($data);
    }

    /**
     * Creates a specific Markup model based on the 'type' field.
     *
     * @param array<string, mixed> $data
     *
     * @return AbstractMarkup
     * @throws ReflectionException
     */
    public function createMarkupElement(array $data): AbstractMarkup
    {
        return match (MarkupType::tryFrom($data['type'] ?? '')) {
            MarkupType::Strong => StrongMarkup::fromArray($data),
            MarkupType::Emphasized => EmphasizedMarkup::fromArray($data),
            MarkupType::Monospaced => MonospacedMarkup::fromArray($data),
            MarkupType::Strikethrough => StrikethroughMarkup::fromArray($data),
            MarkupType::Underline => UnderlineMarkup::fromArray($data),
            MarkupType::Heading => HeadingMarkup::fromArray($data),
            MarkupType::Highlighted => HighlightedMarkup::fromArray($data),
            MarkupType::Link => LinkMarkup::fromArray($data),
            MarkupType::UserMention => UserMentionMarkup::fromArray($data),
            default => throw new LogicException(
                'Unknown or unsupported markup type: ' . ($data['type'] ?? 'none')
            ),
        };
    }
}
