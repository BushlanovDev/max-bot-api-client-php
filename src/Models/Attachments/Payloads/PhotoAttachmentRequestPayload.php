<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Models\Attachments\Payloads;

use BushlanovDev\MaxMessengerBot\Attributes\ArrayOf;
use InvalidArgumentException;

/**
 * Request to attach image. All fields are mutually exclusive.
 */
final readonly class PhotoAttachmentRequestPayload extends AbstractAttachmentRequestPayload
{
    /**
     * @param string|null $url Any external image URL you want to attach.
     * @param string|null $token Token of any existing attachment.
     * @param PhotoToken[]|null $photos Tokens were obtained after uploading images.
     */
    public function __construct(
        public ?string $url = null,
        public ?string $token = null,
        #[ArrayOf(PhotoToken::class)]
        public ?array $photos = null,
    ) {
        if ($this->url === null && $this->token === null && $this->photos === null) {
            throw new InvalidArgumentException(
                'Provide one of "url", "token", or "photos" for PhotoAttachmentRequestPayload.'
            );
        }
    }
}
