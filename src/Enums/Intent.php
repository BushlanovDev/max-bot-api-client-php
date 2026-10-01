<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Enums;

/**
 * @deprecated Not part of the Bot API schema since 0.0.33.
 */
enum Intent: string
{
    case Positive = 'positive';
    case Negative = 'negative';
    case Default = 'default';
}
