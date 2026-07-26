<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Enums;

/**
 * Defines the permissions an administrator can have in a chat.
 */
enum ChatAdminPermission: string
{
    case ReadAllMessages = 'read_all_messages';
    case AddRemoveMembers = 'add_remove_members';
    case AddAdmins = 'add_admins';
    case ChangeChatInfo = 'change_chat_info';
    case PinMessage = 'pin_message';
    case Write = 'write';
    case CanCall = 'can_call';
    case EditLink = 'edit_link';
    case PostEditDeleteMessage = 'post_edit_delete_message';  // @deprecated use write
    case EditMessage = 'edit_message'; // @deprecated use edit
    case DeleteMessage = 'delete_message';
    case Delete = 'delete';
    case Edit = 'edit';
    case ViewStats = 'view_stats';
}
