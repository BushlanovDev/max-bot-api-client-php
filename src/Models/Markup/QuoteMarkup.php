<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Models\Markup;

use BushlanovDev\MaxMessengerBot\Enums\MarkupType;

/**
 * Represents a quote block in the text.
 */
final readonly class QuoteMarkup extends AbstractMarkup
{
    /**
     * @param int $from Element start index (zero-based) in text.
     * @param int $length Length of the markup element.
     */
    public function __construct(
        int $from,
        int $length,
    ) {
        parent::__construct(MarkupType::Quote, $from, $length);
    }
}
