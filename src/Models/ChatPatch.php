<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Models;

use BushlanovDev\MaxMessengerBot\Models\Attachments\Payloads\PhotoAttachmentRequestPayload;

/**
 * Represents the data to patch for a chat. Instantiate with named arguments.
 *
 * Example: new ChatPatch(title: 'New Chat Title');
 *
 * @property-read PhotoAttachmentRequestPayload|null $icon
 * @property-read string|null $title
 * @property-read string|null $description Up to 16000 characters; an empty string removes the description.
 * @property-read string|null $pin Message ID to be pinned.
 * @property-read bool|null $notify
 */
final readonly class ChatPatch extends AbstractPatchModel
{
}
