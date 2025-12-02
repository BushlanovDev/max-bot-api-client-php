<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Enums;

/**
 * Represents the different types of events (updates) that can happen in a chat.
 */
enum UpdateType: string
{
    case MessageCreated = 'message_created';
    case MessageCallback = 'message_callback';
    case MessageEdited = 'message_edited';
    case MessageRemoved = 'message_removed';
    case BotAdded = 'bot_added';
    case BotRemoved = 'bot_removed';
    case DialogMuted = 'dialog_muted';
    case DialogUnmuted = 'dialog_unmuted';
    case DialogCleared = 'dialog_cleared';
    case DialogRemoved = 'dialog_removed';
    case UserAdded = 'user_added';
    case UserRemoved = 'user_removed';
    case BotStarted = 'bot_started';
    case BotStopped = 'bot_stopped';
    case ChatTitleChanged = 'chat_title_changed';
    case MessageChatCreated = 'message_chat_created';
}
