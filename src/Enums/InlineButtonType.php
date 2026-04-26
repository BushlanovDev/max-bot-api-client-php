<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Enums;

enum InlineButtonType: string
{
    case Callback = 'callback';
    case Link = 'link';
    case RequestGeoLocation = 'request_geo_location';
    case RequestContact = 'request_contact';
    case OpenApp = 'open_app';
    case Message = 'message';
    case Chat = 'chat';
    case Clipboard = 'clipboard';
}
