<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Models\Attachments\Payloads;

use InvalidArgumentException;

/**
 * Payload for a share (URL preview) attachment request.
 */
final readonly class ShareAttachmentRequestPayload extends AbstractAttachmentRequestPayload
{
    /**
     * @param string|null $url URL to be attached to the message for media preview.
     * @param string|null $token Token of a previously generated share attachment.
     */
    public function __construct(
        public ?string $url = null,
        public ?string $token = null,
    ) {
        if ($this->url === null && $this->token === null) {
            throw new InvalidArgumentException(
                'Provide one of "url" or "token" for ShareAttachmentRequestPayload.'
            );
        }
    }
}
