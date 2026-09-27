<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Enums;

/**
 * @deprecated Not part of the Bot API schema since 0.0.33.
 */
enum ReplyButtonType: string
{
    case Message = 'message';
    case UserGeoLocation = 'user_geo_location';
    case UserContact = 'user_contact';
}
