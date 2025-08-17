<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Laravel;

use BushlanovDev\MaxMessengerBot\Api;
use BushlanovDev\MaxMessengerBot\Enums\MessageFormat;
use BushlanovDev\MaxMessengerBot\Enums\SenderAction;
use BushlanovDev\MaxMessengerBot\Enums\UpdateType;
use BushlanovDev\MaxMessengerBot\Enums\UploadType;
use BushlanovDev\MaxMessengerBot\Models\Attachments\Requests\AbstractAttachmentRequest;
use BushlanovDev\MaxMessengerBot\Models\BotInfo;
use BushlanovDev\MaxMessengerBot\Models\BotPatch;
use BushlanovDev\MaxMessengerBot\Models\Chat;
use BushlanovDev\MaxMessengerBot\Models\ChatAdmin;
use BushlanovDev\MaxMessengerBot\Models\ChatList;
use BushlanovDev\MaxMessengerBot\Models\ChatMember;
use BushlanovDev\MaxMessengerBot\Models\ChatMembersList;
use BushlanovDev\MaxMessengerBot\Models\ChatPatch;
use BushlanovDev\MaxMessengerBot\Models\Message;
use BushlanovDev\MaxMessengerBot\Models\MessageLink;
use BushlanovDev\MaxMessengerBot\Models\Result;
use BushlanovDev\MaxMessengerBot\Models\Subscription;
use BushlanovDev\MaxMessengerBot\Models\UpdateList;
use BushlanovDev\MaxMessengerBot\Models\UploadEndpoint;
use BushlanovDev\MaxMessengerBot\Models\VideoAttachmentDetails;
use BushlanovDev\MaxMessengerBot\UpdateDispatcher;
use BushlanovDev\MaxMessengerBot\WebhookHandler;
use BushlanovDev\MaxMessengerBot\LongPollingHandler;
use Illuminate\Support\Facades\Facade;

/**
 * Laravel Facade for Max Bot API Client.
 *
 * Provides static access to the Max Bot API methods through Laravel's facade system.
 *
 * @method static array<string, mixed> request(string $method, string $uri, array<string, mixed> $queryParams = [], array<string, mixed> $body = [])
 * @method static UpdateDispatcher getUpdateDispatcher()
 * @method static WebhookHandler createWebhookHandler(?string $secret = null)
 * @method static LongPollingHandler createLongPollingHandler()
 * @method static UpdateList getUpdates(?int $limit = null, ?int $timeout = null, ?int $marker = null, ?array<UpdateType> $types = null)
 * @method static BotInfo getBotInfo()
 * @method static Subscription[] getSubscriptions()
 * @method static Result subscribe(string $url, ?string $secret = null, ?array<UpdateType> $updateTypes = null)
 * @method static Result unsubscribe(string $url)
 * @method static Message sendMessage(?int $userId = null, ?int $chatId = null, ?string $text = null, ?array<AbstractAttachmentRequest> $attachments = null, ?MessageFormat $format = null, ?MessageLink $link = null, bool $notify = true, bool $disableLinkPreview = false)
 * @method static Message sendUserMessage(?int $userId = null, ?string $text = null, ?array<AbstractAttachmentRequest> $attachments = null, ?MessageFormat $format = null, ?MessageLink $link = null, bool $notify = true, bool $disableLinkPreview = false)
 * @method static Message sendChatMessage(?int $chatId = null, ?string $text = null, ?array<AbstractAttachmentRequest> $attachments = null, ?MessageFormat $format = null, ?MessageLink $link = null, bool $notify = true, bool $disableLinkPreview = false)
 * @method static UploadEndpoint getUploadUrl(UploadType $type)
 * @method static AbstractAttachmentRequest uploadAttachment(UploadType $type, string $filePath)
 * @method static Chat getChat(int $chatId)
 * @method static Chat getChatByLink(string $chatLink)
 * @method static ChatList getChats(?int $count = null, ?int $marker = null)
 * @method static Result deleteChat(int $chatId)
 * @method static Result sendAction(int $chatId, SenderAction $action)
 * @method static Message|null getPinnedMessage(int $chatId)
 * @method static Result unpinMessage(int $chatId)
 * @method static ChatMember getMembership(int $chatId)
 * @method static Result leaveChat(int $chatId)
 * @method static Message[] getMessages(int $chatId, ?array<string> $messageIds = null, ?int $from = null, ?int $to = null, ?int $count = null)
 * @method static Result deleteMessage(string $messageId)
 * @method static Message getMessageById(string $messageId)
 * @method static Result pinMessage(int $chatId, string $messageId, bool $notify = true)
 * @method static ChatMembersList getAdmins(int $chatId)
 * @method static ChatMembersList getMembers(int $chatId, ?array<int> $userIds = null, ?int $marker = null, ?int $count = null)
 * @method static Result deleteAdmin(int $chatId, int $userId)
 * @method static Result deleteMember(int $chatId, int $userId, bool $block = false)
 * @method static Result addAdmins(int $chatId, array<ChatAdmin> $admins)
 * @method static Result addMembers(int $chatId, array<int> $userIds)
 * @method static Result answerOnCallback(string $callbackId, ?string $notification = null, ?string $text = null, ?array<AbstractAttachmentRequest> $attachments = null, ?MessageLink $link = null, ?MessageFormat $format = null, bool $notify = true)
 * @method static Result editMessage(string $messageId, ?string $text = null, ?array<AbstractAttachmentRequest> $attachments = null, ?MessageFormat $format = null, ?MessageLink $link = null, bool $notify = true)
 * @method static BotInfo editBotInfo(BotPatch $botPatch)
 * @method static Chat editChat(int $chatId, ChatPatch $chatPatch)
 * @method static VideoAttachmentDetails getVideoAttachmentDetails(string $videoToken)
 *
 * @see Api
 * @codeCoverageIgnore
 */
class MaxBotFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'maxbot';
    }
}
