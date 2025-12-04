<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Tests;

use BushlanovDev\MaxMessengerBot\Api;
use BushlanovDev\MaxMessengerBot\Attributes\ArrayOf;
use BushlanovDev\MaxMessengerBot\Client;
use BushlanovDev\MaxMessengerBot\ClientApiInterface;
use BushlanovDev\MaxMessengerBot\Enums\AttachmentType;
use BushlanovDev\MaxMessengerBot\Enums\ChatAdminPermission;
use BushlanovDev\MaxMessengerBot\Enums\InlineButtonType;
use BushlanovDev\MaxMessengerBot\Enums\MessageFormat;
use BushlanovDev\MaxMessengerBot\Enums\SenderAction;
use BushlanovDev\MaxMessengerBot\Enums\UpdateType;
use BushlanovDev\MaxMessengerBot\Enums\UploadType;
use BushlanovDev\MaxMessengerBot\Exceptions\SerializationException;
use BushlanovDev\MaxMessengerBot\ModelFactory;
use BushlanovDev\MaxMessengerBot\Models\Attachments\Buttons\Inline\CallbackButton;
use BushlanovDev\MaxMessengerBot\Models\Attachments\Payloads\ContactAttachmentRequestPayload;
use BushlanovDev\MaxMessengerBot\Models\Attachments\Payloads\InlineKeyboardAttachmentRequestPayload;
use BushlanovDev\MaxMessengerBot\Models\Attachments\Payloads\LocationAttachmentRequestPayload;
use BushlanovDev\MaxMessengerBot\Models\Attachments\Payloads\PhotoAttachmentRequestPayload;
use BushlanovDev\MaxMessengerBot\Models\Attachments\Payloads\PhotoToken;
use BushlanovDev\MaxMessengerBot\Models\Attachments\Payloads\ShareAttachmentRequestPayload;
use BushlanovDev\MaxMessengerBot\Models\Attachments\Payloads\StickerAttachmentRequestPayload;
use BushlanovDev\MaxMessengerBot\Models\Attachments\Payloads\UploadedInfoAttachmentRequestPayload;
use BushlanovDev\MaxMessengerBot\Models\Attachments\Requests\AudioAttachmentRequest;
use BushlanovDev\MaxMessengerBot\Models\Attachments\Requests\ContactAttachmentRequest;
use BushlanovDev\MaxMessengerBot\Models\Attachments\Requests\FileAttachmentRequest;
use BushlanovDev\MaxMessengerBot\Models\Attachments\Requests\InlineKeyboardAttachmentRequest;
use BushlanovDev\MaxMessengerBot\Models\Attachments\Requests\LocationAttachmentRequest;
use BushlanovDev\MaxMessengerBot\Models\Attachments\Requests\PhotoAttachmentRequest;
use BushlanovDev\MaxMessengerBot\Models\Attachments\Requests\ShareAttachmentRequest;
use BushlanovDev\MaxMessengerBot\Models\Attachments\Requests\StickerAttachmentRequest;
use BushlanovDev\MaxMessengerBot\Models\Attachments\Requests\VideoAttachmentRequest;
use BushlanovDev\MaxMessengerBot\Models\BotInfo;
use BushlanovDev\MaxMessengerBot\Models\BotPatch;
use BushlanovDev\MaxMessengerBot\Models\Chat;
use BushlanovDev\MaxMessengerBot\Models\ChatAdmin;
use BushlanovDev\MaxMessengerBot\Models\ChatList;
use BushlanovDev\MaxMessengerBot\Models\ChatMember;
use BushlanovDev\MaxMessengerBot\Models\ChatMembersList;
use BushlanovDev\MaxMessengerBot\Models\ChatPatch;
use BushlanovDev\MaxMessengerBot\Models\Message;
use BushlanovDev\MaxMessengerBot\Models\MessageBody;
use BushlanovDev\MaxMessengerBot\Models\Recipient;
use BushlanovDev\MaxMessengerBot\Models\Result;
use BushlanovDev\MaxMessengerBot\Models\User;
use BushlanovDev\MaxMessengerBot\Models\Subscription;
use BushlanovDev\MaxMessengerBot\Models\UpdateList;
use BushlanovDev\MaxMessengerBot\Models\Updates\AbstractUpdate;
use BushlanovDev\MaxMessengerBot\Models\Updates\BotStartedUpdate;
use BushlanovDev\MaxMessengerBot\Models\Updates\MessageCreatedUpdate;
use BushlanovDev\MaxMessengerBot\Models\UploadEndpoint;
use BushlanovDev\MaxMessengerBot\Models\VideoAttachmentDetails;
use BushlanovDev\MaxMessengerBot\Models\VideoUrls;
use BushlanovDev\MaxMessengerBot\UpdateDispatcher;
use BushlanovDev\MaxMessengerBot\WebhookHandler;
use InvalidArgumentException;
use LogicException;
use org\bovigo\vfs\vfsStream;
use phpmock\phpunit\PHPMock;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use RuntimeException;

#[CoversClass(Api::class)]
#[UsesClass(Result::class)]
#[UsesClass(Client::class)]
#[UsesClass(BotInfo::class)]
#[UsesClass(Subscription::class)]
#[UsesClass(Message::class)]
#[UsesClass(MessageBody::class)]
#[UsesClass(Recipient::class)]
#[UsesClass(User::class)]
#[UsesClass(CallbackButton::class)]
#[UsesClass(InlineKeyboardAttachmentRequestPayload::class)]
#[UsesClass(InlineKeyboardAttachmentRequest::class)]
#[UsesClass(PhotoToken::class)]
#[UsesClass(PhotoAttachmentRequest::class)]
#[UsesClass(PhotoAttachmentRequestPayload::class)]
#[UsesClass(UploadEndpoint::class)]
#[UsesClass(Chat::class)]
#[UsesClass(UpdateList::class)]
#[UsesClass(WebhookHandler::class)]
#[UsesClass(AbstractUpdate::class)]
#[UsesClass(BotStartedUpdate::class)]
#[UsesClass(MessageCreatedUpdate::class)]
#[UsesClass(UploadedInfoAttachmentRequestPayload::class)]
#[UsesClass(VideoAttachmentRequest::class)]
#[UsesClass(AudioAttachmentRequest::class)]
#[UsesClass(FileAttachmentRequest::class)]
#[UsesClass(StickerAttachmentRequest::class)]
#[UsesClass(StickerAttachmentRequestPayload::class)]
#[UsesClass(ContactAttachmentRequest::class)]
#[UsesClass(ContactAttachmentRequestPayload::class)]
#[UsesClass(LocationAttachmentRequest::class)]
#[UsesClass(LocationAttachmentRequestPayload::class)]
#[UsesClass(ShareAttachmentRequest::class)]
#[UsesClass(ShareAttachmentRequestPayload::class)]
#[UsesClass(ChatList::class)]
#[UsesClass(ChatMember::class)]
#[UsesClass(ArrayOf::class)]
#[UsesClass(ChatMembersList::class)]
#[UsesClass(ChatAdmin::class)]
#[UsesClass(BotPatch::class)]
#[UsesClass(ChatPatch::class)]
#[UsesClass(VideoAttachmentDetails::class)]
#[UsesClass(VideoUrls::class)]
#[UsesClass(UpdateDispatcher::class)]
#[UsesClass(ModelFactory::class)]
final class ApiTest extends TestCase
{
    use PHPMock;

    private MockObject&ClientApiInterface $clientMock;
    private MockObject&ModelFactory $modelFactoryMock;
    private MockObject&LoggerInterface $loggerMock;

    private Api $api;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->clientMock = $this->createMock(ClientApiInterface::class);
        $this->modelFactoryMock = $this->createMock(ModelFactory::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);

        $this->api = new Api('fake-token', $this->clientMock, $this->modelFactoryMock, $this->loggerMock);
    }

    #[Test]
    public function constructorCanCreateDefaultDependencies(): void
    {
        $api = new Api('some-token');

        $reflection = new ReflectionClass($api);

        $clientProp = $reflection->getProperty('client');
        $this->assertInstanceOf(ClientApiInterface::class, $clientProp->getValue($api));

        $factoryProp = $reflection->getProperty('modelFactory');
        $this->assertInstanceOf(ModelFactory::class, $factoryProp->getValue($api));
    }

    #[Test]
    public function getBotInfoCallsClientAndFactoryCorrectly(): void
    {
        $rawResponseData = ['user_id' => 123, 'first_name' => 'ApiTestBot'];

        $botInfo = new BotInfo(
            123,
            'ApiTestBot',
            null, null, true, 0, null, null, null, null,
        );

        $this->clientMock
            ->expects($this->once())
            ->method('request')
            ->with('GET', '/me')
            ->willReturn($rawResponseData);

        $this->modelFactoryMock
            ->expects($this->once())
            ->method('createBotInfo')
            ->with($rawResponseData)
            ->willReturn($botInfo);

        $result = $this->api->getBotInfo();

        $this->assertSame($botInfo, $result);
    }

    #[Test]
    public function testSubscribeCallsClientAndFactoryCorrectly(): void
    {
        $rawResponseData = [
            'subscriptions' => [
                [
                    'url' => 'https://example.com/webhook',
                    'time' => 1678886400000,
                    'update_types' => ['message_created'],
                    'version' => '0.0.1',
                ],
            ],
        ];

        $subscription = new Subscription(
            'https://example.com/webhook',
            1678886400000,
            [UpdateType::MessageCreated],
            '0.0.1',
        );

        $this->clientMock
            ->expects($this->once())
            ->method('request')
            ->with('GET', '/subscriptions')
            ->willReturn($rawResponseData);

        $this->modelFactoryMock
            ->expects($this->once())
            ->method('createSubscriptions')
            ->with($rawResponseData)
            ->willReturn([$subscription]);

        $result = $this->api->getSubscriptions();

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertInstanceOf(Subscription::class, $result[0]);
        $this->assertSame(UpdateType::MessageCreated, $result[0]->updateTypes[0]);
    }

    #[Test]
    public function subscribeCallsClientWithAllParameters(): void
    {
        $url = 'https://example.com/webhook';
        $secret = 'secure';
        $updateTypes = [UpdateType::MessageCreated, UpdateType::BotStarted];
        $updateTypesAsStrings = array_map(fn($type) => $type->value, $updateTypes);

        $expectedBody = [
            'url' => $url,
            'secret' => $secret,
            'update_types' => $updateTypesAsStrings,
        ];

        $rawClientResponse = ['success' => true];
        $expectedResultObject = new Result(true, null);

        $this->clientMock
            ->expects($this->once())
            ->method('request')
            ->with('POST', '/subscriptions', [], $expectedBody)
            ->willReturn($rawClientResponse);

        $this->modelFactoryMock
            ->expects($this->once())
            ->method('createResult')
            ->with($rawClientResponse)
            ->willReturn($expectedResultObject);

        $result = $this->api->subscribe($url, $secret, $updateTypes);
        $this->assertSame($expectedResultObject, $result);
    }

    #[Test]
    public function subscribeHandlesOptionalParametersAsNull(): void
    {
        $url = 'https://example.com/webhook';

        $expectedBody = [
            'url' => $url,
            'secret' => null,
            'update_types' => null,
        ];

        $rawClientResponse = ['success' => true];
        $expectedResultObject = new Result(true, null);

        $this->clientMock
            ->expects($this->once())
            ->method('request')
            ->with('POST', '/subscriptions', [], $expectedBody)
            ->willReturn($rawClientResponse);

        $this->modelFactoryMock
            ->expects($this->once())
            ->method('createResult')
            ->with($rawClientResponse)
            ->willReturn($expectedResultObject);

        $result = $this->api->subscribe($url);
        $this->assertSame($expectedResultObject, $result);
    }

    #[Test]
    public function unsubscribeCallsClientWithCorrectParameters(): void
    {
        $url = 'https://example.com/webhook';
        $expectedQueryParams = ['url' => $url];

        $rawClientResponse = ['success' => true];
        $expectedResultObject = new Result(true, null);

        $this->clientMock
            ->expects($this->once())
            ->method('request')
            ->with('DELETE', '/subscriptions', $expectedQueryParams, [])
            ->willReturn($rawClientResponse);

        $this->modelFactoryMock
            ->expects($this->once())
            ->method('createResult')
            ->with($rawClientResponse)
            ->willReturn($expectedResultObject);

        $result = $this->api->unsubscribe($url);
        $this->assertSame($expectedResultObject, $result);
    }

    #[Test]
    public function sendMessageBuildsCorrectRequestForAllParameters(): void
    {
        $chatId = 123456;
        $text = 'Hello, **world**!';
        $format = MessageFormat::Markdown;
        $notify = false;
        $disableLinkPreview = true;

        $expectedQuery = [
            'chat_id' => $chatId,
            'disable_link_preview' => $disableLinkPreview,
        ];

        $expectedBody = [
            'text' => $text,
            'format' => $format->value,
            'notify' => $notify,
        ];

        $apiResponse = [
            'message' => [
                'timestamp' => time(),
                'message' => ['mid' => 'mid.456.xyz', 'seq' => 101, 'text' => $text],
                'recipient' => ['chat_type' => 'dialog', 'user_id' => 123, 'chat_id' => null],
                'sender' => [
                    'user_id' => 123,
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                    'username' => 'johndoe',
                    'is_bot' => false,
                    'last_activity_time' => 1678886400000,
                ],
                'url' => 'https://max.ru/message/123',
            ],
            'chat_id' => 20414985,
            'recipient_id' => 4328369,
            'message_id' => 'mid.456.xyz',
        ];

        $finalMessageData = $apiResponse['message'];
        $finalMessageData['body'] = $finalMessageData['message'];
        unset($finalMessageData['message']);
        $finalMessageData['chat_id'] = $apiResponse['chat_id'];
        $finalMessageData['recipient_id'] = $apiResponse['recipient_id'];
        $finalMessageData['message_id'] = $apiResponse['message_id'];
        $expectedMessageObject = Message::fromArray($finalMessageData);

        $this->clientMock
            ->expects($this->once())
            ->method('request')
            ->with('POST', '/messages', $expectedQuery, $expectedBody)
            ->willReturn($apiResponse);

        $this->modelFactoryMock
            ->expects($this->once())
            ->method('createMessageFromSendResponse')
            ->with($apiResponse)
            ->willReturn($expectedMessageObject);

        $result = $this->api->sendMessage(
            null,
            $chatId,
            $text,
            null,
            $format,
            null,
            $notify,
            $disableLinkPreview,
        );

        $this->assertSame($expectedMessageObject, $result);
    }

    #[Test]
    public function sendMessageWithAttachmentsBuildsCorrectRequest(): void
    {
        $chatId = 123456;
        $text = 'Test message with a keyboard';
        $disableLinkPreview = true;
        $button = new CallbackButton('Press Me', 'payload_123');
        $keyboard = new InlineKeyboardAttachmentRequest([[$button]]);

        $expectedAttachmentsJson = [
            [
                'type' => AttachmentType::InlineKeyboard->value,
                'payload' => [
                    'buttons' => [
                        [
                            [
                                'type' => InlineButtonType::Callback->value,
                                'text' => 'Press Me',
                                'payload' => 'payload_123',
                                'intent' => null,
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $expectedBody = [
            'text' => $text,
            'attachments' => $expectedAttachmentsJson,
            'notify' => true,
        ];

        $expectedQuery = ['chat_id' => $chatId, 'disable_link_preview' => $disableLinkPreview];

        $apiResponse = [
            'message' => [
                'timestamp' => time(),
                'message' => ['mid' => 'mid.test.123', 'seq' => 1, 'text' => $text],
                'recipient' => ['chat_type' => 'dialog', 'user_id' => 123, 'chat_id' => null],
            ],
            'message_id' => 'mid.test.123',
        ];

        $finalMessageData = $apiResponse['message'];
        $finalMessageData['body'] = $finalMessageData['message'];
        unset($finalMessageData['message']);
        $finalMessageData['message_id'] = $apiResponse['message_id'];
        $expectedMessageObject = Message::fromArray($finalMessageData);

        $this->clientMock
            ->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                '/messages',
                $expectedQuery,
                $expectedBody,
            )
            ->willReturn($apiResponse);

        $this->modelFactoryMock
            ->expects($this->once())
            ->method('createMessageFromSendResponse')
            ->with($apiResponse)
            ->willReturn($expectedMessageObject);

        $result = $this->api->sendMessage(
            chatId: $chatId,
            text: $text,
            attachments: [$keyboard],
            disableLinkPreview: $disableLinkPreview,
        );

        $this->assertSame($expectedMessageObject, $result);
    }

    #[Test]
    public function uploadAttachmentForImage(): void
    {
        $filePath = $this->createTempFile('image-content');
        $uploadUrl = 'https://upload.server/image';
        $uploadResponseJson = '{"photos":{"random_key_123":{"token":"final_image_token"}}}';
        $expectedAttachment = PhotoAttachmentRequest::fromToken('final_image_token');

        $this->clientMock->method('request')->willReturn(['url' => $uploadUrl]);
        $this->modelFactoryMock->method('createUploadEndpoint')->willReturn(new UploadEndpoint($uploadUrl));

        $this->clientMock->method('multipartUpload')->willReturn($uploadResponseJson);

        $result = $this->api->uploadAttachment(UploadType::Image, $filePath);

        $this->assertEquals($expectedAttachment, $result);
        unlink($filePath);
    }

    #[Test]
    public function uploadAttachmentForFile(): void
    {
        $filePath = $this->createTempFile('file-content');
        $uploadUrl = 'https://upload.server/file';
        $uploadResponseJson = '{"token":"final_file_token"}';
        $expectedAttachment = new FileAttachmentRequest('final_file_token');

        $this->clientMock->method('request')->willReturn(['url' => $uploadUrl]);
        $this->modelFactoryMock->method('createUploadEndpoint')->willReturn(new UploadEndpoint($uploadUrl));

        $this->clientMock->method('multipartUpload')->willReturn($uploadResponseJson);

        $result = $this->api->uploadAttachment(UploadType::File, $filePath);

        $this->assertEquals($expectedAttachment, $result);
        unlink($filePath);
    }

    #[Test]
    public function uploadAttachmentForAudio(): void
    {
        $filePath = $this->createTempFile('audio-content');
        $uploadUrl = 'https://upload.server/audio';
        $preUploadToken = 'pre_upload_audio_token';
        $uploadResponse = '<retval>1</retval>';
        $expectedAttachment = new AudioAttachmentRequest($preUploadToken);

        $getUploadUrlResponse = ['url' => $uploadUrl, 'token' => $preUploadToken];
        $expectedEndpoint = new UploadEndpoint($uploadUrl, $preUploadToken);

        $this->clientMock
            ->expects($this->once())
            ->method('request')
            ->with('POST', '/uploads', ['type' => UploadType::Audio->value])
            ->willReturn($getUploadUrlResponse);

        $this->modelFactoryMock
            ->expects($this->once())
            ->method('createUploadEndpoint')
            ->with($getUploadUrlResponse)
            ->willReturn($expectedEndpoint);

        $this->clientMock
            ->expects($this->once())
            ->method('multipartUpload')
            ->with($uploadUrl, $this->isResource(), basename($filePath))
            ->willReturn($uploadResponse);

        $result = $this->api->uploadAttachment(UploadType::Audio, $filePath);

        $this->assertEquals($expectedAttachment, $result);

        unlink($filePath);
    }

    #[Test]
    public function uploadAttachmentForVideo(): void
    {
        $filePath = $this->createTempFile('video-content');
        $uploadUrl = 'https://upload.server/video';
        $preUploadToken = 'pre_upload_video_token';
        $uploadResponse = '<retval>1</retval>';
        $expectedAttachment = new VideoAttachmentRequest($preUploadToken);

        $getUploadUrlResponse = ['url' => $uploadUrl, 'token' => $preUploadToken];
        $expectedEndpoint = new UploadEndpoint($uploadUrl, $preUploadToken);

        $this->clientMock
            ->expects($this->once())
            ->method('request')
            ->with('POST', '/uploads', ['type' => UploadType::Video->value])
            ->willReturn($getUploadUrlResponse);

        $this->modelFactoryMock
            ->expects($this->once())
            ->method('createUploadEndpoint')
            ->with($getUploadUrlResponse)
            ->willReturn($expectedEndpoint);

        $this->clientMock
            ->expects($this->once())
            ->method('multipartUpload')
            ->with($uploadUrl, $this->isResource(), basename($filePath))
            ->willReturn($uploadResponse);

        $result = $this->api->uploadAttachment(UploadType::Video, $filePath);

        $this->assertEquals($expectedAttachment, $result);

        unlink($filePath);
    }

    private function createTempFile(string $content): string
    {
        $filePath = tempnam(sys_get_temp_dir(), 'test_upload_');
        file_put_contents($filePath, $content);
        return $filePath;
    }

    #[Test]
    public function uploadAttachmentThrowsExceptionForNonExistentFile(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/File not found or not readable/');
        $this->api->uploadAttachment(UploadType::Image, '/path/to/non/existent/file.jpg');
    }

    #[Test]
    public function getChatCallsClientAndFactoryCorrectly(): void
    {
        $chatId = 100123456789;
        $rawResponseData = [
            'chat_id' => $chatId,
            'type' => 'chat',
            'status' => 'active',
            'last_event_time' => 1678886400000,
            'participants_count' => 50,
            'is_public' => false,
            'title' => 'Test Chat Title',
            'icon' => null,
            'owner_id' => null,
            'link' => null,
            'description' => null,
            'dialog_with_user' => null,
            'messages_count' => null,
            'chat_message_id' => null,
            'pinned_message' => null,
        ];

        $expectedChatObject = Chat::fromArray($rawResponseData);

        $this->clientMock
            ->expects($this->once())
            ->method('request')
            ->with('GET', '/chats/' . $chatId)
            ->willReturn($rawResponseData);

        $this->modelFactoryMock
            ->expects($this->once())
            ->method('createChat')
            ->with($rawResponseData)
            ->willReturn($expectedChatObject);

        $result = $this->api->getChat($chatId);

        $this->assertSame($expectedChatObject, $result);
    }

    #[Test]
    public function getUpdatesCallsClientWithCorrectParameters(): void
    {
        $limit = 50;
        $timeout = 60;
        $marker = 12345;
        $types = [UpdateType::MessageCreated, UpdateType::BotStarted];

        $expectedQuery = [
            'limit' => $limit,
            'timeout' => $timeout,
            'marker' => $marker,
            'types' => 'message_created,bot_started',
        ];

        $rawResponse = ['updates' => [], 'marker' => $marker + 1];
        $expectedUpdateList = new UpdateList([], $marker + 1);

        $this->clientMock
            ->expects($this->once())
            ->method('request')
            ->with('GET', '/updates', $expectedQuery)
            ->willReturn($rawResponse);

        $this->modelFactoryMock
            ->expects($this->once())
            ->method('createUpdateList')
            ->with($rawResponse)
            ->willReturn($expectedUpdateList);

        $result = $this->api->getUpdates($limit, $timeout, $marker, $types);

        $this->assertSame($expectedUpdateList, $result);
    }

    #[Test]
    public function getUpdatesHandlesNullParameters(): void
    {
        $this->clientMock
            ->expects($this->once())
            ->method('request')
            ->with('GET', '/updates', [])
            ->willReturn(['updates' => [], 'marker' => null]);

        $this->modelFactoryMock
            ->method('createUpdateList')
            ->willReturn(new UpdateList([], null));

        $this->api->getUpdates();
    }

    #[Test]
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function constructorThrowsExceptionWhenGuzzleIsMissing(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessageMatches('/"guzzlehttp\/guzzle" is not found/');

        $classExistsMock = $this->getFunctionMock('BushlanovDev\MaxMessengerBot', 'class_exists');

        $classExistsMock->expects($this->once())
            ->with(\GuzzleHttp\Client::class)
            ->willReturn(false);

        new Api('some-token');
    }

    #[Test]
    public function uploadAttachmentThrowsRuntimeExceptionWhenPathIsADirectory(): void
    {
        $root = vfsStream::setup('root');
        $directory = vfsStream::newDirectory('my_dir')->at($root);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/Could not open file for reading/');

        $this->api->uploadAttachment(UploadType::File, $directory->url());
    }

    #[Test]
    public function uploadAttachmentThrowsSerializationExceptionForInvalidUploadResponse(): void
    {
        $filePath = tempnam(sys_get_temp_dir(), 'test_upload_');
        file_put_contents($filePath, 'content');

        $uploadType = UploadType::Image;
        $uploadUrl = 'https://upload.server/path';

        $getUploadUrlResponse = ['url' => $uploadUrl];
        $expectedEndpoint = new UploadEndpoint($uploadUrl);

        $invalidUploadResponse = ['status' => 'error', 'message' => 'Upload failed'];

        $this->clientMock
            ->expects($this->once())
            ->method('request')
            ->with('POST', '/uploads', ['type' => $uploadType->value])
            ->willReturn($getUploadUrlResponse);

        $this->modelFactoryMock
            ->expects($this->once())
            ->method('createUploadEndpoint')
            ->with($getUploadUrlResponse)
            ->willReturn($expectedEndpoint);

        $this->clientMock
            ->expects($this->once())
            ->method('multipartUpload')
            ->with($uploadUrl, $this->isResource(), basename($filePath))
            ->willReturn(json_encode($invalidUploadResponse));

        $this->expectException(SerializationException::class);
        $this->expectExceptionMessage('Could not find "token" in photo upload response.');

        try {
            $this->api->uploadAttachment($uploadType, $filePath);
        } finally {
            unlink($filePath);
        }
    }

    #[Test]
    public function uploadAttachmentSuccessfullyUploadsFileAndReturnsAttachment(): void
    {
        $filePath = tempnam(sys_get_temp_dir(), 'test_file_');
        file_put_contents($filePath, 'fake-file-content');

        $uploadType = UploadType::File;
        $uploadUrl = 'https://upload.server/file_path';
        $uploadToken = 'FILE_TOKEN_QWERTY';

        $getUploadUrlResponse = ['url' => $uploadUrl];
        $uploadResponse = ['token' => $uploadToken];
        $expectedEndpoint = new UploadEndpoint($uploadUrl);
        $expectedAttachment = new FileAttachmentRequest($uploadToken);

        $this->clientMock
            ->expects($this->once())
            ->method('request')
            ->with('POST', '/uploads', ['type' => $uploadType->value])
            ->willReturn($getUploadUrlResponse);

        $this->modelFactoryMock
            ->expects($this->once())
            ->method('createUploadEndpoint')
            ->with($getUploadUrlResponse)
            ->willReturn($expectedEndpoint);

        $this->clientMock
            ->expects($this->once())
            ->method('multipartUpload')
            ->with($uploadUrl, $this->isResource(), basename($filePath))
            ->willReturn(json_encode($uploadResponse));

        $result = $this->api->uploadAttachment($uploadType, $filePath);

        $this->assertEquals($expectedAttachment, $result);

        unlink($filePath);
    }

    #[Test]
    public function sendMessageWithStickerAttachmentBuildsCorrectRequest(): void
    {
        $chatId = 12345;
        $stickerCode = 'sticker_id_ok';
        $stickerRequest = new StickerAttachmentRequest($stickerCode);

        $expectedBody = [
            'attachments' => [
                [
                    'type' => 'sticker',
                    'payload' => [
                        'code' => $stickerCode,
                    ],
                ],
            ],
            'notify' => true,
        ];
        $expectedQuery = ['chat_id' => $chatId, 'disable_link_preview' => false];

        $apiResponse = [
            'message' => [
                'timestamp' => time(),
                'message' => ['mid' => 'mid.sticker.1', 'seq' => 10],
                'recipient' => ['chat_type' => 'dialog', 'user_id' => $chatId],
            ]
        ];

        $finalMessageData = $apiResponse['message'];
        $finalMessageData['body'] = $finalMessageData['message'];
        unset($finalMessageData['message']);
        $expectedMessageObject = Message::fromArray($finalMessageData);

        $this->clientMock->expects($this->once())
            ->method('request')
            ->with('POST', '/messages', $expectedQuery, $expectedBody)
            ->willReturn($apiResponse);

        $this->modelFactoryMock->expects($this->once())
            ->method('createMessageFromSendResponse')
            ->with($apiResponse)
            ->willReturn($expectedMessageObject);

        $result = $this->api->sendMessage(chatId: $chatId, attachments: [$stickerRequest]);

        $this->assertSame($expectedMessageObject, $result);
    }

    #[Test]
    public function sendMessageWithContactAttachmentBuildsCorrectRequest(): void
    {
        $chatId = 12345;
        $contactRequest = new ContactAttachmentRequest(name: 'Service Desk', vcfPhone: '555-1234');

        $expectedBody = [
            'attachments' => [
                [
                    'type' => 'contact',
                    'payload' => [
                        'name' => 'Service Desk',
                        'contact_id' => null,
                        'vcf_info' => null,
                        'vcf_phone' => '555-1234',
                    ],
                ],
            ],
            'notify' => true,
        ];
        $expectedQuery = ['chat_id' => $chatId, 'disable_link_preview' => false];

        $apiResponse = [
            'message' => [
                'timestamp' => time(),
                'message' => ['mid' => 'mid.contact.1', 'seq' => 11],
                'recipient' => ['chat_type' => 'dialog', 'user_id' => $chatId],
            ]
        ];
        $finalMessageData = $apiResponse['message'];
        $finalMessageData['body'] = $finalMessageData['message'];
        unset($finalMessageData['message']);
        $expectedMessageObject = Message::fromArray($finalMessageData);

        $this->clientMock->expects($this->once())
            ->method('request')
            ->with('POST', '/messages', $expectedQuery, $expectedBody)
            ->willReturn($apiResponse);

        $this->modelFactoryMock->expects($this->once())
            ->method('createMessageFromSendResponse')
            ->with($apiResponse)
            ->willReturn($expectedMessageObject);

        $result = $this->api->sendMessage(chatId: $chatId, attachments: [$contactRequest]);

        $this->assertSame($expectedMessageObject, $result);
    }

    #[Test]
    public function sendMessageWithLocationAttachmentBuildsCorrectRequest(): void
    {
        $chatId = 12345;
        $latitude = 59.9343;
        $longitude = 30.3351;
        $locationRequest = new LocationAttachmentRequest($latitude, $longitude);

        $expectedBody = [
            'attachments' => [
                [
                    'type' => 'location',
                    'payload' => [
                        'latitude' => $latitude,
                        'longitude' => $longitude,
                    ],
                ],
            ],
            'notify' => true,
        ];
        $expectedQuery = ['chat_id' => $chatId, 'disable_link_preview' => false];

        $apiResponse = [
            'message' => [
                'timestamp' => time(),
                'message' => ['mid' => 'mid.location.1', 'seq' => 12],
                'recipient' => ['chat_type' => 'dialog', 'user_id' => $chatId],
            ]
        ];
        $finalMessageData = $apiResponse['message'];
        $finalMessageData['body'] = $finalMessageData['message'];
        unset($finalMessageData['message']);
        $expectedMessageObject = Message::fromArray($finalMessageData);

        $this->clientMock->expects($this->once())
            ->method('request')
            ->with('POST', '/messages', $expectedQuery, $expectedBody)
            ->willReturn($apiResponse);

        $this->modelFactoryMock->expects($this->once())
            ->method('createMessageFromSendResponse')
            ->with($apiResponse)
            ->willReturn($expectedMessageObject);

        $result = $this->api->sendMessage(chatId: $chatId, attachments: [$locationRequest]);

        $this->assertSame($expectedMessageObject, $result);
    }

    #[Test]
    public function sendMessageWithShareAttachmentBuildsCorrectRequest(): void
    {
        $chatId = 12345;
        $url = 'https://dev.max.ru';
        $shareRequest = ShareAttachmentRequest::fromUrl($url);

        $expectedBody = [
            'attachments' => [
                [
                    'type' => 'share',
                    'payload' => [
                        'url' => $url,
                        'token' => null,
                    ],
                ],
            ],
            'notify' => true,
        ];
        $expectedQuery = ['chat_id' => $chatId, 'disable_link_preview' => false];

        $apiResponse = [
            'message' => [
                'timestamp' => time(),
                'message' => ['mid' => 'mid.share.1', 'seq' => 13],
                'recipient' => ['chat_type' => 'dialog', 'user_id' => $chatId],
            ]
        ];
        $finalMessageData = $apiResponse['message'];
        $finalMessageData['body'] = $finalMessageData['message'];
        unset($finalMessageData['message']);
        $expectedMessageObject = Message::fromArray($finalMessageData);

        $this->clientMock->expects($this->once())
            ->method('request')
            ->with('POST', '/messages', $expectedQuery, $expectedBody)
            ->willReturn($apiResponse);

        $this->modelFactoryMock->expects($this->once())
            ->method('createMessageFromSendResponse')
            ->with($apiResponse)
            ->willReturn($expectedMessageObject);

        $result = $this->api->sendMessage(chatId: $chatId, attachments: [$shareRequest]);

        $this->assertSame($expectedMessageObject, $result);
    }

    #[Test]
    public function getChatsPassesAllParametersToClient(): void
    {
        $count = 30;
        $marker = 12345;
        $expectedQuery = ['count' => $count, 'marker' => $marker];

        $rawResponse = ['chats' => [], 'marker' => 54321];
        $expectedChatList = new ChatList([], 54321);

        $this->clientMock->expects($this->once())
            ->method('request')
            ->with('GET', '/chats', $expectedQuery)
            ->willReturn($rawResponse);

        $this->modelFactoryMock->expects($this->once())
            ->method('createChatList')
            ->with($rawResponse)
            ->willReturn($expectedChatList);

        $result = $this->api->getChats($count, $marker);

        $this->assertSame($expectedChatList, $result);
    }

    #[Test]
    public function getChatsHandlesNullParametersCorrectly(): void
    {
        $expectedQuery = [];
        $rawResponse = ['chats' => [], 'marker' => null];
        $expectedChatList = new ChatList([], null);

        $this->clientMock->expects($this->once())
            ->method('request')
            ->with('GET', '/chats', $expectedQuery)
            ->willReturn($rawResponse);

        $this->modelFactoryMock->expects($this->once())
            ->method('createChatList')
            ->with($rawResponse)
            ->willReturn($expectedChatList);

        $result = $this->api->getChats(null, null);

        $this->assertSame($expectedChatList, $result);
    }

    #[Test]
    public function getChatByLinkCallsClientAndFactoryCorrectly(): void
    {
        $chatLink = '@test_channel';
        $rawResponseData = [
            'chat_id' => 987,
            'type' => 'channel',
            'status' => 'active',
            'last_event_time' => 1,
            'participants_count' => 100,
            'is_public' => true,
            'title' => 'Test Channel',
        ];

        $expectedChatObject = Chat::fromArray($rawResponseData);

        $this->clientMock
            ->expects($this->once())
            ->method('request')
            ->with('GET', '/chats/' . $chatLink)
            ->willReturn($rawResponseData);

        $this->modelFactoryMock
            ->expects($this->once())
            ->method('createChat')
            ->with($rawResponseData)
            ->willReturn($expectedChatObject);

        $result = $this->api->getChatByLink($chatLink);

        $this->assertSame($expectedChatObject, $result);
    }

    #[Test]
    public function getChatByLinkHandlesLinkWithoutAtSymbol(): void
    {
        $chatLink = 'test_channel_no_at';
        $rawResponseData = [
            'chat_id' => 987,
            'type' => 'channel',
            'status' => 'active',
            'last_event_time' => 1,
            'participants_count' => 100,
            'is_public' => true,
            'title' => 'Test Channel',
        ];
        $expectedChatObject = Chat::fromArray($rawResponseData);

        $this->clientMock->method('request')->willReturn($rawResponseData);
        $this->modelFactoryMock->method('createChat')->willReturn($expectedChatObject);

        $this->api->getChatByLink($chatLink);

        $this->expectNotToPerformAssertions();
    }

    #[Test]
    public function deleteChatCallsClientAndFactoryCorrectly(): void
    {
        $chatId = 123456789;
        $rawResponseData = ['success' => true, 'message' => null];
        $expectedResultObject = new Result(true, null);

        $this->clientMock
            ->expects($this->once())
            ->method('request')
            ->with(self::equalTo('DELETE'), self::equalTo('/chats/' . $chatId))
            ->willReturn($rawResponseData);

        $this->modelFactoryMock
            ->expects($this->once())
            ->method('createResult')
            ->with($rawResponseData)
            ->willReturn($expectedResultObject);

        $result = $this->api->deleteChat($chatId);

        $this->assertSame($expectedResultObject, $result);
        $this->assertTrue($result->success);
    }

    #[Test]
    public function sendActionCallsClientCorrectly(): void
    {
        $chatId = 12345;
        $action = SenderAction::TypingOn;
        $uri = '/chats/' . $chatId . '/actions';
        $expectedBody = ['action' => 'typing_on'];
        $rawResponse = ['success' => true];
        $expectedResult = new Result(true, null);

        $this->clientMock
            ->expects($this->once())
            ->method('request')
            ->with(self::equalTo('POST'), self::equalTo($uri), self::equalTo([]), self::equalTo($expectedBody))
            ->willReturn($rawResponse);

        $this->modelFactoryMock
            ->expects($this->once())
            ->method('createResult')
            ->with($rawResponse)
            ->willReturn($expectedResult);

        $result = $this->api->sendAction($chatId, $action);
        $this->assertSame($expectedResult, $result);
    }

    #[Test]
    public function getPinnedMessageReturnsMessageOnSuccess(): void
    {
        $chatId = 12345;
        $uri = '/chats/' . $chatId . '/pin';

        $messageData = [
            'timestamp' => 1,
            'body' => ['mid' => 'pinned.msg', 'seq' => 1],
            'recipient' => ['chat_type' => 'chat', 'chat_id' => $chatId],
        ];
        $rawResponse = ['message' => $messageData];

        $expectedMessage = Message::fromArray($messageData);

        $this->clientMock->expects($this->once())
            ->method('request')
            ->with('GET', $uri)
            ->willReturn($rawResponse);

        $this->modelFactoryMock->expects($this->once())
            ->method('createMessage')
            ->with($messageData)
            ->willReturn($expectedMessage);

        $actualMessage = $this->api->getPinnedMessage($chatId);

        $this->assertSame($expectedMessage, $actualMessage);
    }

    #[Test]
    public function getPinnedMessageReturnsNullWhenNoMessageIsPinned(): void
    {
        $chatId = 54321;
        $uri = '/chats/' . $chatId . '/pin';
        $rawResponse = ['message' => null];

        $this->clientMock->expects($this->once())
            ->method('request')
            ->with('GET', $uri)
            ->willReturn($rawResponse);

        $this->modelFactoryMock->expects($this->never())
            ->method('createMessage');

        $result = $this->api->getPinnedMessage($chatId);

        $this->assertNull($result);
    }

    #[Test]
    public function unpinMessageCallsClientCorrectly(): void
    {
        $chatId = 98765;
        $uri = '/chats/' . $chatId . '/pin';
        $rawResponse = ['success' => true, 'message' => null];
        $expectedResult = new Result(true, null);

        $this->clientMock
            ->expects($this->once())
            ->method('request')
            ->with(self::equalTo('DELETE'), self::equalTo($uri))
            ->willReturn($rawResponse);

        $this->modelFactoryMock
            ->expects($this->once())
            ->method('createResult')
            ->with($rawResponse)
            ->willReturn($expectedResult);

        $result = $this->api->unpinMessage($chatId);

        $this->assertSame($expectedResult, $result);
        $this->assertTrue($result->success);
    }

    #[Test]
    public function getMembershipReturnsCorrectChatMember(): void
    {
        $chatId = 12345;
        $uri = sprintf('/chats/%d/members/me', $chatId);

        $rawResponse = [
            'user_id' => 1,
            'first_name' => 'MyBot',
            'is_bot' => true,
            'last_activity_time' => 1,
            'last_name' => null,
            'username' => 'my_bot',
            'description' => null,
            'avatar_url' => null,
            'full_avatar_url' => null,
            'last_access_time' => 2,
            'is_owner' => false,
            'is_admin' => true,
            'join_time' => 0,
            'permissions' => ['write'],
        ];
        $expectedMember = ChatMember::fromArray($rawResponse);

        $this->clientMock
            ->expects($this->once())
            ->method('request')
            ->with('GET', $uri)
            ->willReturn($rawResponse);

        $this->modelFactoryMock
            ->expects($this->once())
            ->method('createChatMember')
            ->with($rawResponse)
            ->willReturn($expectedMember);

        $result = $this->api->getMembership($chatId);

        $this->assertSame($expectedMember, $result);
    }

    #[Test]
    public function leaveChatCallsClientCorrectly(): void
    {
        $chatId = 54321;
        $uri = sprintf('/chats/%d/members/me', $chatId);
        $rawResponse = ['success' => true];
        $expectedResult = new Result(true, null);

        $this->clientMock
            ->expects($this->once())
            ->method('request')
            ->with(self::equalTo('DELETE'), self::equalTo($uri))
            ->willReturn($rawResponse);

        $this->modelFactoryMock
            ->expects($this->once())
            ->method('createResult')
            ->with($rawResponse)
            ->willReturn($expectedResult);

        $result = $this->api->leaveChat($chatId);

        $this->assertSame($expectedResult, $result);
    }

    #[Test]
    public function getMessagesCallsClientWithAllParameters(): void
    {
        $chatId = 12345;
        $messageIds = ['mid.1', 'mid.2'];
        $from = 1678880000;
        $to = 1678886400;
        $count = 10;

        $expectedQuery = [
            'chat_id' => $chatId,
            'message_ids' => 'mid.1,mid.2',
            'from' => $from,
            'to' => $to,
            'count' => $count,
        ];

        $messageData = [
            'timestamp' => 1,
            'body' => ['mid' => 'mid.1', 'seq' => 1],
            'recipient' => ['chat_type' => 'chat', 'chat_id' => $chatId],
        ];
        $rawResponse = ['messages' => [$messageData]];
        $expectedMessages = [Message::fromArray($messageData)];

        $this->clientMock->expects($this->once())
            ->method('request')
            ->with('GET', '/messages', $expectedQuery)
            ->willReturn($rawResponse);

        $this->modelFactoryMock->expects($this->once())
            ->method('createMessages')
            ->with($rawResponse)
            ->willReturn($expectedMessages);

        $result = $this->api->getMessages($chatId, $messageIds, $from, $to, $count);

        $this->assertIsArray($result);
        $this->assertSame($expectedMessages, $result);
    }

    #[Test]
    public function getMessagesReturnsEmptyArrayForEmptyResponse(): void
    {
        $chatId = 54321;
        $rawResponse = ['messages' => []];

        $this->clientMock->expects($this->once())
            ->method('request')
            ->with('GET', '/messages', ['chat_id' => $chatId])
            ->willReturn($rawResponse);

        $this->modelFactoryMock->expects($this->once())
            ->method('createMessages')
            ->with($rawResponse)
            ->willReturn([]);

        $result = $this->api->getMessages($chatId);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    #[Test]
    public function deleteMessageCallsClientCorrectly(): void
    {
        $messageId = 'mid.12345.abcdef';
        $expectedQuery = ['message_id' => $messageId];
        $rawResponse = ['success' => true];
        $expectedResult = new Result(true, null);

        $this->clientMock
            ->expects($this->once())
            ->method('request')
            ->with(
                self::equalTo('DELETE'),
                self::equalTo('/messages'),
                self::equalTo($expectedQuery),
            )
            ->willReturn($rawResponse);

        $this->modelFactoryMock
            ->expects($this->once())
            ->method('createResult')
            ->with($rawResponse)
            ->willReturn($expectedResult);

        $result = $this->api->deleteMessage($messageId);

        $this->assertSame($expectedResult, $result);
    }

    #[Test]
    public function getMessageByIdCallsClientAndFactoryCorrectly(): void
    {
        $messageId = 'mid.abcdef.123456';
        $uri = sprintf('/messages/%s', $messageId);

        $rawResponse = [
            'timestamp' => 1679000000,
            'body' => ['mid' => $messageId, 'seq' => 123, 'text' => 'This is a specific message.'],
            'recipient' => ['chat_type' => 'dialog', 'user_id' => 101],
        ];
        $expectedMessage = Message::fromArray($rawResponse);

        $this->clientMock
            ->expects($this->once())
            ->method('request')
            ->with(self::equalTo('GET'), self::equalTo($uri))
            ->willReturn($rawResponse);

        $this->modelFactoryMock
            ->expects($this->once())
            ->method('createMessage')
            ->with($rawResponse)
            ->willReturn($expectedMessage);

        $result = $this->api->getMessageById($messageId);

        $this->assertSame($expectedMessage, $result);
    }

    #[Test]
    public function pinMessageCallsClientWithCorrectBody(): void
    {
        $chatId = 12345;
        $messageId = 'mid.to.pin';
        $notify = false;
        $uri = sprintf('/chats/%d/pin', $chatId);

        $expectedBody = ['message_id' => $messageId, 'notify' => $notify];
        $rawResponse = ['success' => true];
        $expectedResult = new Result(true, null);

        $this->clientMock
            ->expects($this->once())
            ->method('request')
            ->with(
                self::equalTo('PUT'),
                self::equalTo($uri),
                self::equalTo([]),
                self::equalTo($expectedBody),
            )
            ->willReturn($rawResponse);

        $this->modelFactoryMock
            ->expects($this->once())
            ->method('createResult')
            ->with($rawResponse)
            ->willReturn($expectedResult);

        $result = $this->api->pinMessage($chatId, $messageId, $notify);
        $this->assertSame($expectedResult, $result);
    }

    #[Test]
    public function pinMessageUsesDefaultNotificationValue(): void
    {
        $chatId = 54321;
        $messageId = 'mid.another.pin';
        $uri = sprintf('/chats/%d/pin', $chatId);

        $expectedBody = ['message_id' => $messageId, 'notify' => true];
        $rawResponse = ['success' => true];
        $expectedResult = new Result(true, null);

        $this->clientMock
            ->expects($this->once())
            ->method('request')
            ->with('PUT', $uri, [], $expectedBody)
            ->willReturn($rawResponse);

        $this->modelFactoryMock
            ->method('createResult')
            ->willReturn($expectedResult);

        $this->api->pinMessage($chatId, $messageId);
    }

    #[Test]
    public function getAdminsReturnsChatMembersList(): void
    {
        $chatId = 98765;
        $uri = sprintf('/chats/%d/members/admins', $chatId);

        $rawResponse = [
            'members' => [
                [
                    'user_id' => 1,
                    'first_name' => 'AdminBot',
                    'is_bot' => true,
                    'last_activity_time' => 1,
                    'last_access_time' => 2,
                    'is_owner' => false,
                    'is_admin' => true,
                    'join_time' => 0
                ]
            ],
            'marker' => null
        ];
        $expectedList = ChatMembersList::fromArray($rawResponse);

        $this->clientMock
            ->expects($this->once())
            ->method('request')
            ->with('GET', $uri)
            ->willReturn($rawResponse);

        $this->modelFactoryMock
            ->expects($this->once())
            ->method('createChatMembersList')
            ->with($rawResponse)
            ->willReturn($expectedList);

        $result = $this->api->getAdmins($chatId);

        $this->assertSame($expectedList, $result);
        $this->assertCount(1, $result->members);
        $this->assertTrue($result->members[0]->isAdmin);
    }

    #[Test]
    public function getMembersWithPaginationCallsClientCorrectly(): void
    {
        $chatId = 12345;
        $count = 50;
        $marker = 98765;
        $uri = sprintf('/chats/%d/members', $chatId);
        $expectedQuery = ['count' => $count, 'marker' => $marker];

        $rawResponse = ['members' => [], 'marker' => 123];
        $expectedList = new ChatMembersList([], 123);

        $this->clientMock
            ->expects($this->once())
            ->method('request')
            ->with('GET', $uri, $expectedQuery)
            ->willReturn($rawResponse);

        $this->modelFactoryMock
            ->expects($this->once())
            ->method('createChatMembersList')
            ->with($rawResponse)
            ->willReturn($expectedList);

        $result = $this->api->getMembers($chatId, null, $marker, $count);

        $this->assertSame($expectedList, $result);
    }

    #[Test]
    public function getMembersWithUserIdsCallsClientCorrectly(): void
    {
        $chatId = 54321;
        $userIds = [101, 202, 303];
        $uri = sprintf('/chats/%d/members', $chatId);
        $expectedQuery = ['user_ids' => '101,202,303'];

        $rawResponse = [
            'members' => [
                [
                    'user_id' => 101,
                    'first_name' => 'User1',
                    'is_bot' => false,
                    'last_activity_time' => 1,
                    'last_access_time' => 2,
                    'is_owner' => false,
                    'is_admin' => false,
                    'join_time' => 0,
                ]
            ],
            'marker' => null,
        ];
        $expectedList = ChatMembersList::fromArray($rawResponse);

        $this->clientMock
            ->expects($this->once())
            ->method('request')
            ->with('GET', $uri, $expectedQuery)
            ->willReturn($rawResponse);

        $this->modelFactoryMock
            ->expects($this->once())
            ->method('createChatMembersList')
            ->with($rawResponse)
            ->willReturn($expectedList);

        $result = $this->api->getMembers($chatId, $userIds);

        $this->assertSame($expectedList, $result);
    }

    #[Test]
    public function deleteAdminsCallsClientCorrectly(): void
    {
        $chatId = 12345;
        $userId = 987;
        $uri = sprintf('/chats/%d/members/admins/%d', $chatId, $userId);
        $rawResponse = ['success' => true];
        $expectedResult = new Result(true, null);

        $this->clientMock
            ->expects($this->once())
            ->method('request')
            ->with(self::equalTo('DELETE'), self::equalTo($uri))
            ->willReturn($rawResponse);

        $this->modelFactoryMock
            ->expects($this->once())
            ->method('createResult')
            ->with($rawResponse)
            ->willReturn($expectedResult);

        $result = $this->api->deleteAdmin($chatId, $userId);

        $this->assertSame($expectedResult, $result);
    }

    #[Test]
    public function removeMemberCallsClientWithDefaultBlockValue(): void
    {
        $chatId = 12345;
        $userId = 678;
        $uri = sprintf('/chats/%d/members', $chatId);
        $expectedQuery = ['user_id' => $userId, 'block' => false];

        $rawResponse = ['success' => true];
        $expectedResult = new Result(true, null);

        $this->clientMock
            ->expects($this->once())
            ->method('request')
            ->with('DELETE', $uri, $expectedQuery)
            ->willReturn($rawResponse);

        $this->modelFactoryMock
            ->expects($this->once())
            ->method('createResult')
            ->with($rawResponse)
            ->willReturn($expectedResult);

        $result = $this->api->deleteMember($chatId, $userId);

        $this->assertSame($expectedResult, $result);
    }

    #[Test]
    public function removeMemberCallsClientWithBlockTrue(): void
    {
        $chatId = 54321;
        $userId = 910;
        $uri = sprintf('/chats/%d/members', $chatId);
        $expectedQuery = ['user_id' => $userId, 'block' => true];

        $rawResponse = ['success' => true];
        $expectedResult = new Result(true, null);

        $this->clientMock
            ->expects($this->once())
            ->method('request')
            ->with('DELETE', $uri, $expectedQuery)
            ->willReturn($rawResponse);

        $this->modelFactoryMock
            ->expects($this->once())
            ->method('createResult')
            ->with($rawResponse)
            ->willReturn($expectedResult);

        $result = $this->api->deleteMember($chatId, $userId, true);

        $this->assertSame($expectedResult, $result);
    }

    #[Test]
    public function postAdminsCallsClientWithCorrectBody(): void
    {
        $chatId = 12345;
        $uri = sprintf('/chats/%d/members/admins', $chatId);
        $admins = [
            new ChatAdmin(101, [ChatAdminPermission::Write]),
            new ChatAdmin(202, [ChatAdminPermission::PinMessage]),
        ];

        $expectedBody = [
            'admins' => [
                ['user_id' => 101, 'permissions' => ['write']],
                ['user_id' => 202, 'permissions' => ['pin_message']],
            ],
        ];
        $rawResponse = ['success' => true];
        $expectedResult = new Result(true, null);

        $this->clientMock
            ->expects($this->once())
            ->method('request')
            ->with('POST', $uri, [], $expectedBody)
            ->willReturn($rawResponse);

        $this->modelFactoryMock
            ->expects($this->once())
            ->method('createResult')
            ->with($rawResponse)
            ->willReturn($expectedResult);

        $result = $this->api->addAdmins($chatId, $admins);
        $this->assertSame($expectedResult, $result);
    }

    #[Test]
    public function addMembersCallsClientWithCorrectBody(): void
    {
        $chatId = 12345;
        $userIds = [101, 202, 303];
        $uri = sprintf('/chats/%d/members', $chatId);
        $expectedBody = ['user_ids' => $userIds];

        $rawResponse = ['success' => true];
        $expectedResult = new Result(true, null);

        $this->clientMock
            ->expects($this->once())
            ->method('request')
            ->with(
                self::equalTo('POST'),
                self::equalTo($uri),
                self::equalTo([]),
                self::equalTo($expectedBody),
            )
            ->willReturn($rawResponse);

        $this->modelFactoryMock
            ->expects($this->once())
            ->method('createResult')
            ->with($rawResponse)
            ->willReturn($expectedResult);

        $result = $this->api->addMembers($chatId, $userIds);
        $this->assertSame($expectedResult, $result);
    }

    #[Test]
    public function answerOnCallbackWithNotificationOnly(): void
    {
        $callbackId = 'cb.123.abc';
        $notification = 'Action confirmed!';
        $expectedQuery = ['callback_id' => $callbackId];
        $expectedBody = ['notification' => $notification];
        $rawResponse = ['success' => true];
        $expectedResult = new Result(true, null);

        $this->clientMock
            ->expects($this->once())
            ->method('request')
            ->with('POST', '/answers', $expectedQuery, $expectedBody)
            ->willReturn($rawResponse);

        $this->modelFactoryMock
            ->expects($this->once())
            ->method('createResult')
            ->with($rawResponse)
            ->willReturn($expectedResult);

        $result = $this->api->answerOnCallback($callbackId, $notification);
        $this->assertSame($expectedResult, $result);
    }

    #[Test]
    public function answerOnCallbackWithMessageEdit(): void
    {
        $callbackId = 'cb.456.def';
        $newText = 'Message updated!';
        $expectedQuery = ['callback_id' => $callbackId];
        $expectedBody = [
            'message' => [
                'text' => $newText,
                'notify' => true,
            ],
        ];
        $rawResponse = ['success' => true];
        $expectedResult = new Result(true, null);

        $this->clientMock
            ->expects($this->once())
            ->method('request')
            ->with('POST', '/answers', $expectedQuery, $expectedBody)
            ->willReturn($rawResponse);

        $this->modelFactoryMock
            ->expects($this->once())
            ->method('createResult')
            ->with($rawResponse)
            ->willReturn($expectedResult);

        $result = $this->api->answerOnCallback(callbackId: $callbackId, text: $newText);
        $this->assertSame($expectedResult, $result);
    }

    #[Test]
    public function answerOnCallbackWithBothMessageAndNotification(): void
    {
        $callbackId = 'cb.789.ghi';
        $notification = 'Done!';
        $newText = 'Updated!';
        $expectedQuery = ['callback_id' => $callbackId];
        $expectedBody = [
            'notification' => $notification,
            'message' => [
                'text' => $newText,
                'notify' => true,
            ],
        ];
        $rawResponse = ['success' => true];
        $expectedResult = new Result(true, null);

        $this->clientMock
            ->expects($this->once())
            ->method('request')
            ->with('POST', '/answers', $expectedQuery, $expectedBody)
            ->willReturn($rawResponse);

        $this->modelFactoryMock
            ->expects($this->once())
            ->method('createResult')
            ->with($rawResponse)
            ->willReturn($expectedResult);

        $result = $this->api->answerOnCallback(
            callbackId: $callbackId,
            notification: $notification,
            text: $newText
        );
        $this->assertSame($expectedResult, $result);
    }

    #[Test]
    public function editMessageCallsClientWithCorrectParameters(): void
    {
        $messageId = 'mid.123.abc';
        $newText = 'This is the edited text.';
        $expectedQuery = ['message_id' => $messageId];
        $expectedBody = [
            'text' => $newText,
            'notify' => true,
        ];

        $rawResponse = ['success' => true];
        $expectedResult = new Result(true, null);

        $this->clientMock
            ->expects($this->once())
            ->method('request')
            ->with('PUT', '/messages', $expectedQuery, $expectedBody)
            ->willReturn($rawResponse);

        $this->modelFactoryMock
            ->expects($this->once())
            ->method('createResult')
            ->with($rawResponse)
            ->willReturn($expectedResult);

        $result = $this->api->editMessage($messageId, $newText);
        $this->assertSame($expectedResult, $result);
    }

    #[Test]
    public function editMessageCanClearAttachmentsWithEmptyArray(): void
    {
        $messageId = 'mid.456.def';
        $expectedQuery = ['message_id' => $messageId];
        $expectedBody = [
            'attachments' => [],
            'notify' => true,
        ];

        $rawResponse = ['success' => true];
        $expectedResult = new Result(true, null);

        $this->clientMock
            ->expects($this->once())
            ->method('request')
            ->with('PUT', '/messages', $expectedQuery, $expectedBody)
            ->willReturn($rawResponse);

        $this->modelFactoryMock
            ->expects($this->once())
            ->method('createResult')
            ->with($rawResponse)
            ->willReturn($expectedResult);

        $result = $this->api->editMessage($messageId, attachments: []);
        $this->assertSame($expectedResult, $result);
    }

    #[Test]
    public function editBotInfoSendsCorrectPatchBody(): void
    {
        $patch = new BotPatch(name: 'New Bot Name', description: null);

        $expectedBody = [
            'name' => 'New Bot Name',
            'description' => null,
        ];

        $rawResponseData = [
            'user_id' => 123,
            'first_name' => 'New Bot Name',
            'is_bot' => true,
            'last_activity_time' => 1,
            'description' => null,
        ];
        $expectedBotInfo = new BotInfo(123, 'New Bot Name', null, null, true, 1, null, null, null, null);

        $this->clientMock
            ->expects($this->once())
            ->method('request')
            ->with('PATCH', '/me', [], $expectedBody)
            ->willReturn($rawResponseData);

        $this->modelFactoryMock
            ->expects($this->once())
            ->method('createBotInfo')
            ->with($rawResponseData)
            ->willReturn($expectedBotInfo);

        $this->api->editBotInfo($patch);
    }

    #[Test]
    public function editChatSendsCorrectPatchBody(): void
    {
        $chatId = 12345;
        $uri = sprintf('/chats/%d', $chatId);
        $patch = new ChatPatch(title: 'New Chat Title', notify: false);

        $expectedBody = [
            'title' => 'New Chat Title',
            'notify' => false,
        ];

        $rawResponse = [
            'chat_id' => $chatId,
            'title' => 'New Chat Title',
            'type' => 'chat',
            'status' => 'active',
            'last_event_time' => 1,
            'participants_count' => 1,
            'is_public' => false,
        ];
        $expectedChat = Chat::fromArray($rawResponse);

        $this->clientMock
            ->expects($this->once())
            ->method('request')
            ->with('PATCH', $uri, [], $expectedBody)
            ->willReturn($rawResponse);

        $this->modelFactoryMock
            ->expects($this->once())
            ->method('createChat')
            ->with($rawResponse)
            ->willReturn($expectedChat);

        $result = $this->api->editChat($chatId, $patch);
        $this->assertSame($expectedChat, $result);
    }

    #[Test]
    public function getVideoAttachmentDetailsCallsClientCorrectly(): void
    {
        $videoToken = 'some_video_token_xyz';
        $uri = sprintf('/videos/%s', $videoToken);

        $rawResponse = [
            'token' => $videoToken,
            'width' => 1920,
            'height' => 1080,
            'duration' => 120,
            'urls' => ['mp4_1080' => 'http://example.com/video.mp4'],
        ];
        $expectedDetails = VideoAttachmentDetails::fromArray($rawResponse);

        $this->clientMock
            ->expects($this->once())
            ->method('request')
            ->with('GET', $uri)
            ->willReturn($rawResponse);

        $this->modelFactoryMock
            ->expects($this->once())
            ->method('createVideoAttachmentDetails')
            ->with($rawResponse)
            ->willReturn($expectedDetails);

        $result = $this->api->getVideoAttachmentDetails($videoToken);

        $this->assertSame($expectedDetails, $result);
    }

    #[Test]
    public function constructorThrowsExceptionWhenNoTokenAndNoClientProvided(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('You must provide either an access token or a client.');

        new Api(
            accessToken: null,
            client: null
        );
    }

    #[Test]
    public function uploadAttachmentThrowsSerializationExceptionOnInvalidUploadResponse(): void
    {
        $this->expectException(SerializationException::class);
        $this->expectExceptionMessage('Failed to decode upload server response JSON.');

        $filePath = $this->createTempFile('image-content');
        $uploadUrl = 'https://upload.server/image';
        $invalidJsonResponse = '{not-valid-json';

        $this->clientMock->method('request')->willReturn(['url' => $uploadUrl]);
        $this->modelFactoryMock->method('createUploadEndpoint')->willReturn(new UploadEndpoint($uploadUrl));

        $this->clientMock
            ->expects($this->once())
            ->method('multipartUpload')
            ->willReturn($invalidJsonResponse);

        try {
            $this->api->uploadAttachment(UploadType::Image, $filePath);
        } finally {
            unlink($filePath);
        }
    }

    #[Test]
    public function uploadAttachmentForVideoThrowsExceptionOnMissingPreUploadToken(): void
    {
        $this->expectException(SerializationException::class);
        $this->expectExceptionMessage("API did not return a pre-upload token for type 'video'.");

        $filePath = $this->createTempFile('video-content');
        $uploadUrl = 'https://upload.server/video';

        $getUploadUrlResponse = ['url' => $uploadUrl];
        $expectedEndpoint = new UploadEndpoint($uploadUrl, null);

        $this->clientMock
            ->expects($this->once())
            ->method('request')
            ->with('POST', '/uploads', ['type' => UploadType::Video->value])
            ->willReturn($getUploadUrlResponse);

        $this->modelFactoryMock
            ->expects($this->once())
            ->method('createUploadEndpoint')
            ->with($getUploadUrlResponse)
            ->willReturn($expectedEndpoint);

        $this->clientMock->expects($this->never())->method('multipartUpload');

        try {
            $this->api->uploadAttachment(UploadType::Video, $filePath);
        } finally {
            unlink($filePath);
        }
    }

    #[Test]
    public function uploadAttachmentForFileThrowsExceptionOnMissingPostUploadToken(): void
    {
        $this->expectException(SerializationException::class);
        $this->expectExceptionMessage('Could not find "token" in file upload response.');

        $filePath = $this->createTempFile('file-content');
        $uploadUrl = 'https://upload.server/file';

        $invalidUploadResponse = json_encode(['status' => 'success', 'file_id' => 123]);

        $this->clientMock->method('request')->willReturn(['url' => $uploadUrl]);
        $this->modelFactoryMock->method('createUploadEndpoint')->willReturn(new UploadEndpoint($uploadUrl));

        $this->clientMock
            ->expects($this->once())
            ->method('multipartUpload')
            ->willReturn($invalidUploadResponse);

        try {
            $this->api->uploadAttachment(UploadType::File, $filePath);
        } finally {
            unlink($filePath);
        }
    }

    #[Test]
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function uploadFileUsesMultipartForSmallFiles(): void
    {
        $uploadUrl = 'https://upload.server/path';
        $fileName = 'small.txt';
        $fileContents = 'content';
        $fileHandle = fopen('php://memory', 'w+');
        fwrite($fileHandle, $fileContents);
        rewind($fileHandle);

        $smallFileSize = strlen($fileContents);
        $expectedResponse = 'multipart-response';

        $fstatMock = $this->getFunctionMock('BushlanovDev\MaxMessengerBot', 'fstat');
        $fstatMock->expects($this->once())->with($fileHandle)->willReturn(['size' => $smallFileSize]);

        $this->clientMock
            ->expects($this->once())
            ->method('multipartUpload')
            ->with($uploadUrl, $fileHandle, $fileName)
            ->willReturn($expectedResponse);

        $this->clientMock
            ->expects($this->never())
            ->method('resumableUpload');

        $result = $this->api->uploadFile($uploadUrl, $fileHandle, $fileName);

        $this->assertSame($expectedResponse, $result);
        fclose($fileHandle);
    }

    #[Test]
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function uploadFileUsesResumableForLargeFiles(): void
    {
        $uploadUrl = 'https://upload.server/path';
        $fileName = 'large.zip';
        $fileHandle = fopen('php://memory', 'w+');

        rewind($fileHandle);

        $largeFileSize = 10 * 1024 * 1024;
        $expectedResponse = 'resumable-response';

        $fstatMock = $this->getFunctionMock('BushlanovDev\MaxMessengerBot', 'fstat');
        $fstatMock->expects($this->once())->with($fileHandle)->willReturn(['size' => $largeFileSize]);

        $this->clientMock
            ->expects($this->once())
            ->method('resumableUpload')
            ->with($uploadUrl, $fileHandle, $fileName, $largeFileSize)
            ->willReturn($expectedResponse);

        $this->clientMock
            ->expects($this->never())
            ->method('multipartUpload');

        $result = $this->api->uploadFile($uploadUrl, $fileHandle, $fileName);

        $this->assertSame($expectedResponse, $result);
        fclose($fileHandle);
    }

    #[Test]
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function uploadFileThrowsExceptionWhenFstatFails(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('File handle is not a valid resource.');

        $fileHandle = fopen('php://memory', 'r');

        $fstatMock = $this->getFunctionMock('BushlanovDev\MaxMessengerBot', 'fstat');
        $fstatMock->expects($this->once())->with($fileHandle)->willReturn(false);

        $this->api->uploadFile('http://a.b', $fileHandle, 'file.txt');
    }
}
