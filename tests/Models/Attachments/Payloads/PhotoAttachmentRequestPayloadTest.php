<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Tests\Models\Attachments\Payloads;

use BushlanovDev\MaxMessengerBot\Attributes\ArrayOf;
use BushlanovDev\MaxMessengerBot\Models\Attachments\Payloads\PhotoAttachmentRequestPayload;
use BushlanovDev\MaxMessengerBot\Models\Attachments\Payloads\PhotoToken;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PhotoAttachmentRequestPayload::class)]
#[UsesClass(PhotoToken::class)]
#[UsesClass(ArrayOf::class)]
final class PhotoAttachmentRequestPayloadTest extends TestCase
{
    #[Test]
    public function canBeCreatedWithUrlOnly(): void
    {
        $payload = new PhotoAttachmentRequestPayload(url: 'https://example.com/photo.jpg');

        $this->assertSame('https://example.com/photo.jpg', $payload->url);
        $this->assertNull($payload->token);
        $this->assertNull($payload->photos);

        $expectedArray = [
            'url' => 'https://example.com/photo.jpg',
            'token' => null,
            'photos' => null,
        ];
        $this->assertEquals($expectedArray, $payload->toArray());
    }

    #[Test]
    public function canBeCreatedWithTokenOnly(): void
    {
        $payload = new PhotoAttachmentRequestPayload(token: 'uploaded_token_abc');

        $this->assertSame('uploaded_token_abc', $payload->token);
        $this->assertNull($payload->url);
        $this->assertNull($payload->photos);

        $expectedArray = [
            'token' => 'uploaded_token_abc',
            'url' => null,
            'photos' => null,
        ];
        $this->assertEquals($expectedArray, $payload->toArray());
    }

    #[Test]
    public function canBeCreatedWithPhotosOnly(): void
    {
        $photos = [
            new PhotoToken('token_1'),
            new PhotoToken('token_2'),
        ];
        $payload = new PhotoAttachmentRequestPayload(photos: $photos);

        $this->assertSame($photos, $payload->photos);
        $this->assertNull($payload->url);
        $this->assertNull($payload->token);

        $expectedArray = [
            'photos' => [
                ['token' => 'token_1'],
                ['token' => 'token_2'],
            ],
            'url' => null,
            'token' => null,
        ];
        $this->assertEquals($expectedArray, $payload->toArray());
    }

    #[Test]
    public function constructorThrowsExceptionForInvalidArguments(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Provide one of "url", "token", or "photos" for PhotoAttachmentRequestPayload.');

        new PhotoAttachmentRequestPayload(null, null, null);
    }
}
