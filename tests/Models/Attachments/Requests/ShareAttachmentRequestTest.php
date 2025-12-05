<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Tests\Models\Attachments\Requests;

use BushlanovDev\MaxMessengerBot\Enums\AttachmentType;
use BushlanovDev\MaxMessengerBot\Models\Attachments\Payloads\ShareAttachmentRequestPayload;
use BushlanovDev\MaxMessengerBot\Models\Attachments\Requests\ShareAttachmentRequest;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ShareAttachmentRequest::class)]
#[UsesClass(ShareAttachmentRequestPayload::class)]
final class ShareAttachmentRequestTest extends TestCase
{
    #[Test]
    public function fromUrlCreatesCorrectRequestAndSerializes(): void
    {
        $request = ShareAttachmentRequest::fromUrl('https://dev.max.ru');
        $this->assertSame(AttachmentType::Share, $request->type);
        $this->assertSame('https://dev.max.ru', $request->payload->url);
        $this->assertNull($request->payload->token);

        $expected = ['type' => 'share', 'payload' => ['url' => 'https://dev.max.ru', 'token' => null]];
        $this->assertEquals($expected, $request->toArray());
    }

    #[Test]
    public function fromTokenCreatesCorrectRequestAndSerializes(): void
    {
        $request = ShareAttachmentRequest::fromToken('share_token_123');
        $this->assertSame(AttachmentType::Share, $request->type);
        $this->assertSame('share_token_123', $request->payload->token);
        $this->assertNull($request->payload->url);

        $expected = ['type' => 'share', 'payload' => ['token' => 'share_token_123', 'url' => null]];
        $this->assertEquals($expected, $request->toArray());
    }

    #[Test]
    public function payloadThrowsExceptionForInvalidArguments(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Provide one of "url" or "token" for ShareAttachmentRequestPayload.');
        new ShareAttachmentRequestPayload(null, null);
    }
}
