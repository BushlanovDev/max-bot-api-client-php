<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Models\Attachments\Buttons\Reply;

use BushlanovDev\MaxMessengerBot\Enums\ReplyButtonType;
use BushlanovDev\MaxMessengerBot\Models\AbstractModel;

/**
 * @deprecated Not part of the Bot API schema since 0.0.33.
 */
abstract readonly class AbstractReplyButton extends AbstractModel
{
    /**
     * @param ReplyButtonType $type The type of the reply button.
     * @param string $text Visible text of the button.
     */
    public function __construct(
        public ReplyButtonType $type,
        public string $text,
    ) {
    }
}
