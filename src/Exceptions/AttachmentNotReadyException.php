<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Exceptions;

/**
 * Exception thrown when an attachment is not yet ready for use.
 * This typically occurs when trying to use an attachment that is still being processed.
 */
class AttachmentNotReadyException extends ClientApiException
{
}
