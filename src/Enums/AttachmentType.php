<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Enums;

enum  AttachmentType: string
{
    case Image = 'image';
    case Video = 'video';
    case Audio = 'audio';
    case File = 'file';
    case Sticker = 'sticker';
    case Contact = 'contact';
    case InlineKeyboard = 'inline_keyboard';
    case ReplyKeyboard = 'reply_keyboard';
    case Location = 'location';
    case Share = 'share';
}
