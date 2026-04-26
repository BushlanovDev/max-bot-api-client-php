<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Models\Attachments\Buttons\Inline;

use BushlanovDev\MaxMessengerBot\Enums\InlineButtonType;

/**
 * Opens the bot's mini-application.
 */
final readonly class ClipboardButton extends AbstractInlineButton
{
    
    public ?string $payload;

    /**
     * @param string $text Visible button text (1 to 128 characters).
     * @param string $payload Text which will be copied.
     */
    public function __construct(string $text, string $payload)
    {
        parent::__construct(InlineButtonType::Clipboard, $text);
        
        $this->payload = $payload;
    }
}
