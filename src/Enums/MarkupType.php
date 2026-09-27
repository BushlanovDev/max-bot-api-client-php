<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Enums;

enum MarkupType: string
{
    case Strong = 'strong';
    case Emphasized = 'emphasized';
    case Monospaced = 'monospaced';
    case Link = 'link';
    case Strikethrough = 'strikethrough';
    case Underline = 'underline';
    case UserMention = 'user_mention';
    case Heading = 'heading';
    case Highlighted = 'highlighted';
    case Quote = 'quote';
}
