<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Models;

use BushlanovDev\MaxMessengerBot\Models\Attachments\Payloads\PhotoAttachmentRequestPayload;

/**
 * Represents the data to patch for a bot. Instantiate with named arguments.
 *
 * Example: new BotPatch(first_name: 'New Bot Name', description: null);
 *
 * @property-read string|null $first_name
 * @property-read string|null $last_name
 * @property-read string|null $description
 * @property-read BotCommand[]|null $commands
 * @property-read PhotoAttachmentRequestPayload|null $photo
 * @property-read string|null $name @deprecated Use first_name
 *
 * @deprecated The API no longer accepts `PATCH /me`. Use Api::editBotCommands() for commands.
 */
final readonly class BotPatch extends AbstractPatchModel
{
}
