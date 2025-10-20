<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Tests;

use BushlanovDev\MaxMessengerBot\Attributes\ArrayOf;
use BushlanovDev\MaxMessengerBot\Enums\ChatAdminPermission;
use BushlanovDev\MaxMessengerBot\Enums\UpdateType;
use BushlanovDev\MaxMessengerBot\ModelFactory;
use BushlanovDev\MaxMessengerBot\Models\Attachments\AbstractAttachment;
use BushlanovDev\MaxMessengerBot\Models\Attachments\Buttons\Inline\AbstractInlineButton;
use BushlanovDev\MaxMessengerBot\Models\Attachments\Buttons\Inline\CallbackButton;
use BushlanovDev\MaxMessengerBot\Models\Attachments\Buttons\Inline\ChatButton;
use BushlanovDev\MaxMessengerBot\Models\Attachments\Buttons\Inline\LinkButton;
use BushlanovDev\MaxMessengerBot\Models\Attachments\Buttons\Inline\RequestContactButton;
use BushlanovDev\MaxMessengerBot\Models\Attachments\Buttons\Inline\RequestGeoLocationButton;
use BushlanovDev\MaxMessengerBot\Models\Attachments\Buttons\Reply\AbstractReplyButton;
use BushlanovDev\MaxMessengerBot\Models\Attachments\Buttons\Reply\SendContactButton;
use BushlanovDev\MaxMessengerBot\Models\Attachments\Buttons\Reply\SendMessageButton;
use BushlanovDev\MaxMessengerBot\Models\Attachments\DataAttachment;
use BushlanovDev\MaxMessengerBot\Models\Attachments\InlineKeyboardAttachment;
use BushlanovDev\MaxMessengerBot\Models\Attachments\LocationAttachment;
use BushlanovDev\MaxMessengerBot\Models\Attachments\Payloads\KeyboardPayload;
use BushlanovDev\MaxMessengerBot\Models\Attachments\Payloads\PhotoAttachmentPayload;
use BushlanovDev\MaxMessengerBot\Models\Attachments\Payloads\PhotoAttachmentRequestPayload;
use BushlanovDev\MaxMessengerBot\Models\Attachments\Payloads\ShareAttachmentRequestPayload;
use BushlanovDev\MaxMessengerBot\Models\Attachments\PhotoAttachment;
use BushlanovDev\MaxMessengerBot\Models\Attachments\ReplyKeyboardAttachment;
use BushlanovDev\MaxMessengerBot\Models\Attachments\ShareAttachment;
use BushlanovDev\MaxMessengerBot\Models\BotCommand;
use BushlanovDev\MaxMessengerBot\Models\BotInfo;
use BushlanovDev\MaxMessengerBot\Models\Chat;
use BushlanovDev\MaxMessengerBot\Models\ChatList;
use BushlanovDev\MaxMessengerBot\Models\ChatMember;
use BushlanovDev\MaxMessengerBot\Models\ChatMembersList;
use BushlanovDev\MaxMessengerBot\Models\Image;
use BushlanovDev\MaxMessengerBot\Models\Markup\AbstractMarkup;
use BushlanovDev\MaxMessengerBot\Models\Markup\LinkMarkup;
use BushlanovDev\MaxMessengerBot\Models\Markup\StrongMarkup;
use BushlanovDev\MaxMessengerBot\Models\Message;
use BushlanovDev\MaxMessengerBot\Models\MessageBody;
use BushlanovDev\MaxMessengerBot\Models\Recipient;
use BushlanovDev\MaxMessengerBot\Models\Result;
use BushlanovDev\MaxMessengerBot\Models\User;
use BushlanovDev\MaxMessengerBot\Models\Subscription;
use BushlanovDev\MaxMessengerBot\Models\UpdateList;
use BushlanovDev\MaxMessengerBot\Models\Updates\BotStartedUpdate;
use BushlanovDev\MaxMessengerBot\Models\Updates\ChatTitleChangedUpdate;
use BushlanovDev\MaxMessengerBot\Models\Updates\MessageChatCreatedUpdate;
use BushlanovDev\MaxMessengerBot\Models\Updates\MessageCreatedUpdate;
use BushlanovDev\MaxMessengerBot\Models\UploadEndpoint;
use BushlanovDev\MaxMessengerBot\Models\UserWithPhoto;
use BushlanovDev\MaxMessengerBot\Models\VideoAttachmentDetails;
use BushlanovDev\MaxMessengerBot\Models\VideoUrls;
use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ModelFactory::class)]
#[UsesClass(BotInfo::class)]
#[UsesClass(BotCommand::class)]
#[UsesClass(Result::class)]
#[UsesClass(Subscription::class)]
#[UsesClass(ArrayOf::class)]
#[UsesClass(Message::class)]
#[UsesClass(MessageBody::class)]
#[UsesClass(Recipient::class)]
#[UsesClass(User::class)]
#[UsesClass(UpdateList::class)]
#[UsesClass(BotStartedUpdate::class)]
#[UsesClass(MessageCreatedUpdate::class)]
#[UsesClass(UserWithPhoto::class)]
#[UsesClass(UploadEndpoint::class)]
#[UsesClass(Chat::class)]
#[UsesClass(Image::class)]
#[UsesClass(ChatTitleChangedUpdate::class)]
#[UsesClass(MessageChatCreatedUpdate::class)]
#[UsesClass(ChatList::class)]
#[UsesClass(ChatMember::class)]
#[UsesClass(ChatMembersList::class)]
#[UsesClass(VideoAttachmentDetails::class)]
#[UsesClass(PhotoAttachmentRequestPayload::class)]
#[UsesClass(VideoUrls::class)]
#[UsesClass(AbstractAttachment::class)]
#[UsesClass(DataAttachment::class)]
#[UsesClass(ShareAttachment::class)]
#[UsesClass(ShareAttachmentRequestPayload::class)]
#[UsesClass(LinkMarkup::class)]
#[UsesClass(AbstractMarkup::class)]
#[UsesClass(StrongMarkup::class)]
#[UsesClass(PhotoAttachmentPayload::class)]
#[UsesClass(PhotoAttachment::class)]
#[UsesClass(AbstractReplyButton::class)]
#[UsesClass(SendContactButton::class)]
#[UsesClass(SendMessageButton::class)]
#[UsesClass(ReplyKeyboardAttachment::class)]
#[UsesClass(AbstractInlineButton::class)]
#[UsesClass(ChatButton::class)]
#[UsesClass(RequestContactButton::class)]
#[UsesClass(CallbackButton::class)]
#[UsesClass(RequestGeoLocationButton::class)]
#[UsesClass(LinkButton::class)]
#[UsesClass(LocationAttachment::class)]
#[UsesClass(InlineKeyboardAttachment::class)]
#[UsesClass(KeyboardPayload::class)]
final class ModelFactoryTest extends TestCase
{
    private ModelFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->factory = new ModelFactory();
    }

    #[Test]
    public function createResultSuccessfully(): void
    {
        $rawData = ['success' => true];

        $result = $this->factory->createResult($rawData);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertTrue($result->success);
        $this->assertNull($result->message);
    }

    #[Test]
    public function createResultNotSuccessfully()
    {
        $rawData = [
            'success' => false,
            'message' => 'error message',
        ];

        $result = $this->factory->createResult($rawData);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertFalse($result->success);
        $this->assertSame('error message', $result->message);
    }

    #[Test]
    public function createBotInfoCorrectlyHydratesCommands(): void
    {
        $rawData = [
            'user_id' => 12345,
            'first_name' => 'Test',
            'last_name' => 'Bot',
            'username' => 'test_bot',
            'is_bot' => true,
            'last_activity_time' => 1678886400000,
            'description' => 'A test bot.',
            'avatar_url' => 'http://example.com/avatar.jpg',
            'full_avatar_url' => 'http://example.com/full_avatar.jpg',
            'commands' => [
                ['name' => 'start', 'description' => 'Start the bot'],
                ['name' => 'help', 'description' => 'Show help'],
            ],
        ];

        $botInfo = $this->factory->createBotInfo($rawData);

        $this->assertInstanceOf(BotInfo::class, $botInfo);
        $this->assertSame(12345, $botInfo->userId);

        $this->assertIsArray($botInfo->commands);
        $this->assertCount(2, $botInfo->commands);
        $this->assertInstanceOf(BotCommand::class, $botInfo->commands[0]);
        $this->assertSame('start', $botInfo->commands[0]->name);
        $this->assertInstanceOf(BotCommand::class, $botInfo->commands[1]);
        $this->assertSame('help', $botInfo->commands[1]->name);
    }

    #[Test]
    public function createBotInfoHandlesNullCommands(): void
    {
        $rawData = [
            'user_id' => 12345,
            'first_name' => 'Test',
            'last_name' => null,
            'username' => 'test_bot',
            'is_bot' => true,
            'last_activity_time' => 1678886400000,
            'description' => null,
            'avatar_url' => null,
            'full_avatar_url' => null,
            'commands' => null,
        ];

        $botInfo = $this->factory->createBotInfo($rawData);

        $this->assertInstanceOf(BotInfo::class, $botInfo);
        $this->assertNull($botInfo->commands);
    }

    #[Test]
    public function createSubscriptions(): void
    {
        $rawData = [
            'subscriptions' => [
                [
                    'url' => 'https://example.com/webhook',
                    'time' => 1678886400000,
                    'update_types' => ['message_created'],
                    'version' => '0.0.1',
                ],
            ],
        ];

        $subscriptions = $this->factory->createSubscriptions($rawData);

        $this->assertIsArray($subscriptions);
        $this->assertCount(1, $subscriptions);
        $this->assertInstanceOf(Subscription::class, $subscriptions[0]);
        $this->assertSame(UpdateType::MessageCreated, $subscriptions[0]->updateTypes[0]);
    }

    #[Test]
    public function createMessageFromSendResponseCorrectlyTransformsData(): void
    {
        $apiResponse = [
            'message' => [
                'recipient' => ['chat_id' => 20414985, 'chat_type' => 'dialog', 'user_id' => 4328369],
                'timestamp' => 1760089962345,
                'sender' => [
                    'user_id' => 24480184,
                    'first_name' => 'Autobot',
                    'is_bot' => true,
                    'last_activity_time' => 1760089962354,
                ],
                'message' => ['mid' => 'mid.xyz', 'seq' => 1153, 'text' => '123123'],
            ],
            'chat_id' => 20414985,
            'recipient_id' => 4328369,
            'message_id' => 'mid.xyz',
        ];

        $message = $this->factory->createMessageFromSendResponse($apiResponse);

        $this->assertInstanceOf(Message::class, $message);
        $this->assertInstanceOf(MessageBody::class, $message->body);
        $this->assertSame('mid.xyz', $message->body->mid);
        $this->assertSame(20414985, $message->chatId);
        $this->assertSame(4328369, $message->recipientId);
        $this->assertSame('mid.xyz', $message->messageId);
    }

    #[Test]
    public function createMessage(): void
    {
        $rawData = [
            'timestamp' => time(),
            'body' => [
                'mid' => 'mid.456.xyz',
                'seq' => 101,
                'text' => 'Hello, **world**!',
            ],
            'recipient' => [
                'chat_type' => 'dialog',
                'user_id' => 123,
                'chat_id' => null,
            ],
            'sender' => [
                'user_id' => 123,
                'first_name' => 'John',
                'last_name' => 'Doe',
                'username' => 'johndoe',
                'is_bot' => false,
                'last_activity_time' => 1678886400000,
            ],
            'url' => 'https://max.ru/message/123',
        ];

        $message = $this->factory->createMessage($rawData);

        $this->assertInstanceOf(Message::class, $message);
        $this->assertInstanceOf(MessageBody::class, $message->body);
        $this->assertInstanceOf(Recipient::class, $message->recipient);
        $this->assertInstanceOf(User::class, $message->sender);
    }

    #[Test]
    public function createUploadEndpoint(): void
    {
        $rawData = [
            'url' => 'https://example.com/upload',
        ];

        $uploadEndpoint = $this->factory->createUploadEndpoint($rawData);

        $this->assertInstanceOf(UploadEndpoint::class, $uploadEndpoint);
        $this->assertSame('https://example.com/upload', $uploadEndpoint->url);
        $this->assertNull($uploadEndpoint->token);
    }

    #[Test]
    public function createChat(): void
    {
        $rawData = [
            'chat_id' => 123,
            'type' => 'chat',
            'status' => 'active',
            'last_event_time' => 1678886400000,
            'participants_count' => 50,
            'is_public' => false,
            'title' => 'Test Chat',
            'icon' => [
                'url' => 'https://example.com/icon.jpg',
            ],
            'owner_id' => 123,
            'link' => 'https://max.ru/chat/123',
            'description' => 'This is a test chat',
            'dialog_with_user' => [
                'user_id' => 456,
                'first_name' => 'John',
                'last_name' => 'Doe',
                'username' => 'johndoe',
                'is_bot' => false,
                'last_activity_time' => 1678886400000,
            ],
            'messages_count' => 100,
            'chat_message_id' => 'mid.123',
        ];

        $chat = $this->factory->createChat($rawData);

        $this->assertInstanceOf(Chat::class, $chat);
        $this->assertInstanceOf(Image::class, $chat->icon);
        $this->assertInstanceOf(UserWithPhoto::class, $chat->dialogWithUser);
    }

    #[Test]
    public function createUpdateListHandlesDifferentUpdateTypes(): void
    {
        $rawData = [
            'updates' => [
                [
                    'update_type' => 'message_created',
                    'timestamp' => 1,
                    'message' => [
                        'timestamp' => 1,
                        'body' => ['mid' => 'mid.1', 'seq' => 1],
                        'recipient' => ['chat_type' => 'dialog'],
                    ],
                ],
                [
                    'update_type' => 'bot_started',
                    'timestamp' => 2,
                    'chat_id' => 123,
                    'user' => [
                        'user_id' => 123,
                        'first_name' => 'John',
                        'is_bot' => false,
                        'last_activity_time' => 2,
                    ],
                    'payload' => 'start_payload',
                    'user_locale' => 'ru-RU',
                ],
                [
                    'update_type' => 'chat_title_changed',
                    'timestamp' => 1680000000,
                    'chat_id' => 12345,
                    'user' => [
                        'user_id' => 54321,
                        'first_name' => 'John',
                        'is_bot' => false,
                        'last_activity_time' => 1679999999,
                    ],
                    'title' => 'New Awesome Chat Title',
                ],
                [
                    'update_type' => 'message_chat_created',
                    'timestamp' => 1683000001,
                    'chat' => [
                        'chat_id' => 54321,
                        'type' => 'chat',
                        'status' => 'active',
                        'last_event_time' => 1683000001,
                        'participants_count' => 1,
                        'is_public' => false,
                        'title' => 'Another Discussion',
                    ],
                    'message_id' => 'mid.another.message',
                    'start_payload' => null,
                ],
            ],
            'marker' => 12345,
        ];

        $updateList = $this->factory->createUpdateList($rawData);

        $this->assertInstanceOf(UpdateList::class, $updateList);
        $this->assertSame(12345, $updateList->marker);
        $this->assertCount(4, $updateList->updates);
        $this->assertInstanceOf(MessageCreatedUpdate::class, $updateList->updates[0]);
        $this->assertInstanceOf(BotStartedUpdate::class, $updateList->updates[1]);
        $this->assertSame('start_payload', $updateList->updates[1]->payload);
        $this->assertInstanceOf(ChatTitleChangedUpdate::class, $updateList->updates[2]);
        $this->assertInstanceOf(MessageChatCreatedUpdate::class, $updateList->updates[3]);
    }

    #[Test]
    public function createChatListDelegatesCreationToChatListModel(): void
    {
        $rawData = [
            'chats' => [
                [
                    'chat_id' => 101,
                    'type' => 'chat',
                    'status' => 'active',
                    'last_event_time' => 1,
                    'participants_count' => 5,
                    'is_public' => false,
                ],
                [
                    'chat_id' => 102,
                    'type' => 'dialog',
                    'status' => 'suspended',
                    'last_event_time' => 2,
                    'participants_count' => 2,
                    'is_public' => false,
                ],
            ],
            'marker' => 98765,
        ];

        $chatList = $this->factory->createChatList($rawData);

        $this->assertInstanceOf(ChatList::class, $chatList);

        $this->assertSame(98765, $chatList->marker);
        $this->assertCount(2, $chatList->chats);
        $this->assertInstanceOf(Chat::class, $chatList->chats[0]);
        $this->assertSame(101, $chatList->chats[0]->chatId);
    }

    #[Test]
    public function createChatMember()
    {
        $rawData = [
            'user_id' => 101,
            'first_name' => 'AdminBot',
            'last_name' => null,
            'username' => 'admin_bot',
            'is_bot' => true,
            'last_activity_time' => 1678886400,
            'description' => 'I am a bot.',
            'avatar_url' => null,
            'full_avatar_url' => null,
            'last_access_time' => 1679000000,
            'is_owner' => false,
            'is_admin' => true,
            'join_time' => 1678000000,
            'permissions' => ['pin_message', 'write'],
        ];

        $chatMember = $this->factory->createChatMember($rawData);

        $this->assertInstanceOf(ChatMember::class, $chatMember);
        $this->assertTrue($chatMember->isAdmin);
        $this->assertFalse($chatMember->isOwner);
        $this->assertIsArray($chatMember->permissions);
        $this->assertCount(2, $chatMember->permissions);
        $this->assertSame(ChatAdminPermission::PinMessage, $chatMember->permissions[0]);
        $this->assertSame(ChatAdminPermission::Write, $chatMember->permissions[1]);
        $this->assertEquals($rawData, $chatMember->toArray());
    }

    #[Test]
    public function createMessagesReturnsArrayOfMessageObjects(): void
    {
        $data = [
            'messages' => [
                [
                    'timestamp' => 1,
                    'body' => ['mid' => 'mid.1', 'seq' => 1],
                    'recipient' => ['chat_type' => 'chat', 'chat_id' => 123],
                ],
                [
                    'timestamp' => 2,
                    'body' => ['mid' => 'mid.2', 'seq' => 2],
                    'recipient' => ['chat_type' => 'chat', 'chat_id' => 123],
                ],
            ],
        ];

        $messages = $this->factory->createMessages($data);

        $this->assertIsArray($messages);
        $this->assertCount(2, $messages);
        $this->assertInstanceOf(Message::class, $messages[0]);
        $this->assertSame('mid.1', $messages[0]->body->mid);
    }

    #[Test]
    public function createMessagesHandlesEmptyOrMissingKey(): void
    {
        $this->assertEmpty($this->factory->createMessages(['messages' => []]));
        $this->assertEmpty($this->factory->createMessages([]));
    }

    #[Test]
    public function createChatMembersListSuccessfully(): void
    {
        $rawData = [
            'members' => [
                [
                    'user_id' => 101,
                    'first_name' => 'Admin1',
                    'is_bot' => false,
                    'last_activity_time' => 1,
                    'last_access_time' => 2,
                    'is_owner' => true,
                    'is_admin' => true,
                    'join_time' => 0,
                ],
                [
                    'user_id' => 102,
                    'first_name' => 'Admin2',
                    'is_bot' => true,
                    'last_activity_time' => 3,
                    'last_access_time' => 4,
                    'is_owner' => false,
                    'is_admin' => true,
                    'join_time' => 5,
                ],
            ],
            'marker' => 98765,
        ];

        $list = $this->factory->createChatMembersList($rawData);

        $this->assertInstanceOf(ChatMembersList::class, $list);
        $this->assertCount(2, $list->members);
        $this->assertSame(98765, $list->marker);

        $this->assertInstanceOf(ChatMember::class, $list->members[0]);
        $this->assertSame(101, $list->members[0]->userId);
        $this->assertTrue($list->members[0]->isOwner);

        $this->assertInstanceOf(ChatMember::class, $list->members[1]);
        $this->assertSame(102, $list->members[1]->userId);
        $this->assertTrue($list->members[1]->isBot);
    }

    #[Test]
    public function createChatMembersListHandlesEmptyResponse(): void
    {
        $rawData = [
            'members' => [],
            'marker' => null,
        ];

        $list = $this->factory->createChatMembersList($rawData);

        $this->assertInstanceOf(ChatMembersList::class, $list);
        $this->assertEmpty($list->members);
        $this->assertNull($list->marker);
    }

    #[Test]
    public function createVideoAttachmentDetailsSuccessfully(): void
    {
        $rawData = [
            'token' => 'vid_token',
            'width' => 1280,
            'height' => 720,
            'duration' => 60,
            'urls' => ['mp4_720' => 'http://a.com/720.mp4'],
            'thumbnail' => ['token' => 'thumb_token'],
        ];

        $details = $this->factory->createVideoAttachmentDetails($rawData);

        $this->assertInstanceOf(VideoAttachmentDetails::class, $details);
        $this->assertSame('vid_token', $details->token);
        $this->assertInstanceOf(VideoUrls::class, $details->urls);
        $this->assertInstanceOf(PhotoAttachmentRequestPayload::class, $details->thumbnail);
    }

    #[Test]
    public function createMessageCorrectlyHydratesPolymorphicAttachments(): void
    {
        $rawData = [
            'timestamp' => time(),
            'body' => [
                'mid' => 'mid.789.def',
                'seq' => 102,
                'text' => 'Message with data attachment',
                'attachments' => [
                    ['type' => 'data', 'data' => 'payload_from_reply_button'],
                    [
                        'type' => 'share',
                        'payload' => ['url' => 'http://a.com'],
                        'title' => 'Test Share',
                        'description' => null,
                        'image_url' => null,
                    ],
                    ['type' => 'image', 'payload' => ['photo_id' => 1, 'token' => 't', 'url' => 'u']]
                ],
                'markup' => null,
            ],
            'recipient' => ['chat_type' => 'dialog', 'user_id' => 123],

        ];

        $message = $this->factory->createMessage($rawData);
        $attachments = $message->body->attachments;

        $this->assertInstanceOf(Message::class, $message);
        $this->assertInstanceOf(MessageBody::class, $message->body);
        $this->assertIsArray($attachments);
        $this->assertCount(3, $attachments);

        $this->assertInstanceOf(DataAttachment::class, $attachments[0]);
        $this->assertSame('payload_from_reply_button', $attachments[0]->data);

        $this->assertInstanceOf(ShareAttachment::class, $message->body->attachments[1]);
        $this->assertSame('Test Share', $attachments[1]->title);

        $this->assertInstanceOf(PhotoAttachment::class, $attachments[2]);
        $this->assertSame(1, $attachments[2]->payload->photoId);
    }

    #[Test]
    public function createMessageCorrectlyHydratesMarkup(): void
    {
        // ... (данные теста)
        $rawData = [
            'timestamp' => time(),
            'body' => [
                'mid' => 'mid.markup.test',
                'seq' => 200,
                'text' => 'Hello world! Visit our site.',
                'attachments' => null,
                'markup' => [
                    ['type' => 'strong', 'from' => 6, 'length' => 5],
                    ['type' => 'link', 'from' => 18, 'length' => 4, 'url' => 'https://dev.max.ru']
                ]
            ],
            'recipient' => ['chat_type' => 'dialog', 'user_id' => 123],
        ];

        $message = $this->factory->createMessage($rawData);
        $markup = $message->body->markup;

        $this->assertInstanceOf(StrongMarkup::class, $markup[0]);
        $this->assertSame(6, $markup[0]->from);

        $this->assertInstanceOf(LinkMarkup::class, $markup[1]);
        $this->assertSame('https://dev.max.ru', $markup[1]->url);
    }

    #[Test]
    public function createMarkupElementThrowsExceptionForUnknownType(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Unknown or unsupported markup type: brand_new_unsupported_type');

        $this->factory->createMarkupElement(['type' => 'brand_new_unsupported_type']);
    }

    #[Test]
    public function createAttachmentCorrectlyHydratesReplyKeyboard(): void
    {
        $data = [
            'type' => 'reply_keyboard',
            'buttons' => [
                [['type' => 'message', 'text' => 'Hello']],
                [['type' => 'user_contact', 'text' => 'My Contact']]
            ]
        ];

        $attachment = $this->factory->createAttachment($data);

        $this->assertInstanceOf(ReplyKeyboardAttachment::class, $attachment);
        $this->assertCount(2, $attachment->buttons);
        $this->assertInstanceOf(SendMessageButton::class, $attachment->buttons[0][0]);
        $this->assertInstanceOf(SendContactButton::class, $attachment->buttons[1][0]);
        $this->assertSame('My Contact', $attachment->buttons[1][0]->text);
    }

    #[Test]
    public function createReplyButtonThrowsExceptionForUnknownType(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Unknown or unsupported reply button type: new_fancy_button');

        $this->factory->createReplyButton(['type' => 'new_fancy_button']);
    }

    /**
     * Data provider for successful inline button creation.
     * @return array<string, array{0: array<string, mixed>, 1: class-string}>
     */
    public static function inlineButtonProvider(): array
    {
        return [
            'CallbackButton' => [
                ['type' => 'callback', 'text' => 'Press', 'payload' => 'p'],
                CallbackButton::class,
            ],
            'LinkButton' => [
                ['type' => 'link', 'text' => 'Visit', 'url' => 'https://a.com'],
                LinkButton::class,
            ],
            'RequestContactButton' => [
                ['type' => 'request_contact', 'text' => 'Share Contact'],
                RequestContactButton::class,
            ],
            'RequestGeoLocationButton' => [
                ['type' => 'request_geo_location', 'text' => 'Share Location', 'quick' => false],
                RequestGeoLocationButton::class,
            ],
            'ChatButton' => [
                ['type' => 'chat', 'text' => 'Join Chat', 'chat_title' => 'My Chat'],
                ChatButton::class,
            ],
        ];
    }

    #[Test]
    #[DataProvider('inlineButtonProvider')]
    public function createInlineButtonSuccessfully(array $data, string $expectedClass): void
    {
        $button = $this->factory->createInlineButton($data);

        $this->assertInstanceOf($expectedClass, $button);
        $this->assertSame($data['text'], $button->text);
    }

    /**
     * Data provider for invalid inline button data.
     * @return array<string, array{0: array<string, mixed>, 1: string}>
     */
    public static function invalidInlineButtonProvider(): array
    {
        return [
            'unknown type' => [
                ['type' => 'unknown_button_type', 'text' => 'Unknown'],
                'Unknown or unsupported inline button type: unknown_button_type',
            ],
            'missing type' => [
                ['text' => 'No type here'],
                'Unknown or unsupported inline button type: none',
            ],
        ];
    }

    #[Test]
    #[DataProvider('invalidInlineButtonProvider')]
    public function createInlineButtonThrowsExceptionForInvalidType(array $invalidData, string $expectedMessage): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage($expectedMessage);

        $this->factory->createInlineButton($invalidData);
    }

    /**
     * Data provider for testing various attachment types in createAttachment.
     * @return array<string, array{0: array<string, mixed>, 1: class-string, 2: callable}>
     */
    public static function attachmentTypeProvider(): array
    {
        return [
            'Data Attachment' => [
                ['type' => 'data', 'data' => 'test_payload'],
                DataAttachment::class,
                function (TestCase $test, DataAttachment $attachment) {
                    $test->assertSame('test_payload', $attachment->data);
                }
            ],

            'Location Attachment' => [
                ['type' => 'location', 'latitude' => 55.751244, 'longitude' => 37.618423],
                LocationAttachment::class,
                function (TestCase $test, LocationAttachment $attachment) {
                    $test->assertSame(55.751244, $attachment->latitude);
                }
            ],

            'Inline Keyboard Attachment' => [
                [
                    'type' => 'inline_keyboard',
                    'payload' => [
                        'buttons' => [
                            [['type' => 'callback', 'text' => 'Test', 'payload' => 'p']]
                        ]
                    ]
                ],
                InlineKeyboardAttachment::class,
                function (TestCase $test, InlineKeyboardAttachment $attachment) {
                    $test->assertInstanceOf(KeyboardPayload::class, $attachment->payload);
                    $test->assertIsArray($attachment->payload->buttons);
                    $test->assertInstanceOf(CallbackButton::class, $attachment->payload->buttons[0][0]);
                    $test->assertSame('p', $attachment->payload->buttons[0][0]->payload);
                }
            ],
        ];
    }

    #[Test]
    #[DataProvider('attachmentTypeProvider')]
    public function createAttachmentSuccessfullyCreatesVariousTypes(
        array $data,
        string $expectedClass,
        callable $assertionCallback,
    ): void {
        $attachment = $this->factory->createAttachment($data);

        $this->assertInstanceOf($expectedClass, $attachment);

        $assertionCallback($this, $attachment);
    }
}
