<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Tests\Models\Attachments;

use BushlanovDev\MaxMessengerBot\Models\Attachments\ContactAttachment;
use BushlanovDev\MaxMessengerBot\Models\Attachments\Payloads\ContactAttachmentPayload;
use BushlanovDev\MaxMessengerBot\Models\UserWithPhoto;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ContactAttachment::class)]
#[UsesClass(ContactAttachmentPayload::class)]
#[UsesClass(UserWithPhoto::class)]
final class ContactAttachmentTest extends TestCase
{
    #[Test]
    public function canBeCreatedFromArray(): void
    {
        $data = ['type' => 'contact', 'payload' => ['vcf_info' => 'vcf', 'max_info' => null]];
        $attachment = ContactAttachment::fromArray($data);
        $this->assertInstanceOf(ContactAttachment::class, $attachment);
        $this->assertSame('vcf', $attachment->payload->vcfInfo);
        $this->assertNull($attachment->payload->hash);
    }

    #[Test]
    public function payloadCarriesTheVcfHash(): void
    {
        $attachment = ContactAttachment::fromArray([
            'type' => 'contact',
            'payload' => ['vcf_info' => 'vcf', 'max_info' => null, 'hash' => 'abc123'],
        ]);

        $this->assertSame('abc123', $attachment->payload->hash);
    }
}
