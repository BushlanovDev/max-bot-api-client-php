<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Tests\Models\Attachments\Payloads;

use BushlanovDev\MaxMessengerBot\Models\Attachments\Payloads\ContactAttachmentPayload;
use BushlanovDev\MaxMessengerBot\Models\UserWithPhoto;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ContactAttachmentPayload::class)]
#[UsesClass(UserWithPhoto::class)]
final class ContactAttachmentPayloadTest extends TestCase
{
    #[Test]
    public function canBeCreatedAndSerialized(): void
    {
        $user = UserWithPhoto::fromArray(
            ['user_id' => 101, 'first_name' => 'MaxUser', 'is_bot' => false, 'last_activity_time' => time()]
        );
        $payload = new ContactAttachmentPayload('vcf_info_string', $user);
        $this->assertSame('vcf_info_string', $payload->vcfInfo);
        $this->assertSame($user, $payload->maxInfo);
        $this->assertEquals(['vcf_info' => 'vcf_info_string', 'max_info' => $user->toArray(), 'hash' => null], $payload->toArray());
    }
}
