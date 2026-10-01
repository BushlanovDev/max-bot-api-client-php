<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Models\Attachments\Buttons\Reply;

use BushlanovDev\MaxMessengerBot\Enums\Intent;
use BushlanovDev\MaxMessengerBot\Enums\ReplyButtonType;

/**
 * @deprecated Not part of the Bot API schema since 0.0.33.
 */
final readonly class SendMessageButton extends AbstractReplyButton
{
    /**
     * @param string $text Visible text of the button.
     * @param string|null $payload Button payload.
     * @param Intent $intent Intent of button.
     */
    public function __construct(
        string $text,
        public ?string $payload = null,
        public Intent $intent = Intent::Default,
    ) {
        parent::__construct(ReplyButtonType::Message, $text);
    }
}
