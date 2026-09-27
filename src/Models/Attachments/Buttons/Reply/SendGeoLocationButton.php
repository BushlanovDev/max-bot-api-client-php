<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Models\Attachments\Buttons\Reply;

use BushlanovDev\MaxMessengerBot\Enums\ReplyButtonType;

/**
 * @deprecated Not part of the Bot API schema since 0.0.33.
 */
final readonly class SendGeoLocationButton extends AbstractReplyButton
{
    /**
     * @param string $text Visible text of the button.
     * @param bool $quick If `true`, sends location without asking user's confirmation.
     */
    public function __construct(
        string $text,
        public bool $quick = false,
    ) {
        parent::__construct(ReplyButtonType::UserGeoLocation, $text);
    }
}
