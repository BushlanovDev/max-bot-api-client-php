<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Models\Attachments\Payloads;

use BushlanovDev\MaxMessengerBot\Models\AbstractModel;
use BushlanovDev\MaxMessengerBot\Models\UserWithPhoto;

/**
 * Payload of a contact attachment.
 */
final readonly class ContactAttachmentPayload extends AbstractModel
{
    /**
     * @param string|null $vcfInfo User info in VCF format.
     * @param UserWithPhoto|null $maxInfo User info if the contact is a Max user.
     * @param string|null $hash Hash of the user info in VCF format.
     */
    public function __construct(
        public ?string $vcfInfo,
        public ?UserWithPhoto $maxInfo,
        public ?string $hash = null,
    ) {
    }
}
