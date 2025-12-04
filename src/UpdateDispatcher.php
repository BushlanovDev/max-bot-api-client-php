<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot;

use BushlanovDev\MaxMessengerBot\Enums\UpdateType;
use BushlanovDev\MaxMessengerBot\Models\Updates\AbstractUpdate;
use BushlanovDev\MaxMessengerBot\Models\Updates\MessageCreatedUpdate;

/**
 * Dispatches updates to registered handlers. Supports handling specific update types and text commands.
 */
final class UpdateDispatcher
{
    /**
     * @var array<string, callable>
     */
    private array $handlers = [];

    /**
     * @var array<string, callable>
     */
    private array $commandHandlers = [];

    /**
     * @param Api $api
     */
    public function __construct(private readonly Api $api)
    {
    }

    /**
     * Registers a handler for a specific update type.
     *
     * @param UpdateType $type The type of update to handle.
     * @param callable $handler The function to execute when the update is received.
     *
     * @return $this
     */
    public function addHandler(UpdateType $type, callable $handler): self
    {
        $this->handlers[$type->value] = $handler;

        return $this;
    }

    /**
     * Registers a handler for a text command without a command prefix "/" (e.g., "start").
     * The command must be the first word in a message.
     *
     * @param string $command The command string (e.g., "start").
     * @param callable(MessageCreatedUpdate, Api): void $handler The handler to execute.
     *
     * @return $this
     */
    public function onCommand(string $command, callable $handler): self
    {
        $this->commandHandlers[$command] = $handler;

        return $this;
    }

    /**
     * Dispatches a parsed Update object to its registered handler.
     * Command handlers are prioritized over generic message handlers.
     *
     * @param AbstractUpdate $update The update object to dispatch.
     */
    public function dispatch(AbstractUpdate $update): void
    {
        if ($update instanceof MessageCreatedUpdate && $update->message->body?->text) {
            $text = $update->message->body->text;
            $parts = explode(' ', trim($text));
            $command = $parts[0];

            if (isset($this->commandHandlers[$command])) {
                $this->commandHandlers[$command]($update, $this->api);
                return;
            }
        }

        $handler = $this->handlers[$update->updateType->value] ?? null;
        if ($handler) {
            $handler($update, $this->api);
        }
    }

    /**
     * A convenient alias for addHandler(UpdateType::MessageCreated, $handler).
     *
     * @param callable(Models\Updates\MessageCreatedUpdate, Api): void $handler
     *
     * @return $this
     * @codeCoverageIgnore
     */
    public function onMessageCreated(callable $handler): self
    {
        return $this->addHandler(UpdateType::MessageCreated, $handler);
    }

    /**
     * A convenient alias for addHandler(UpdateType::MessageCallback, $handler).
     *
     * @param callable(Models\Updates\MessageCallbackUpdate, Api): void $handler
     *
     * @return $this
     * @codeCoverageIgnore
     */
    public function onMessageCallback(callable $handler): self
    {
        return $this->addHandler(UpdateType::MessageCallback, $handler);
    }

    /**
     * A convenient alias for addHandler(UpdateType::MessageEdited, $handler).
     *
     * @param callable(Models\Updates\MessageEditedUpdate, Api): void $handler
     *
     * @return $this
     * @codeCoverageIgnore
     */
    public function onMessageEdited(callable $handler): self
    {
        return $this->addHandler(UpdateType::MessageEdited, $handler);
    }

    /**
     * A convenient alias for addHandler(UpdateType::MessageRemoved, $handler).
     *
     * @param callable(Models\Updates\MessageRemovedUpdate, Api): void $handler
     *
     * @return $this
     * @codeCoverageIgnore
     */
    public function onMessageRemoved(callable $handler): self
    {
        return $this->addHandler(UpdateType::MessageRemoved, $handler);
    }

    /**
     * A convenient alias for addHandler(UpdateType::BotAdded, $handler).
     *
     * @param callable(Models\Updates\BotAddedToChatUpdate, Api): void $handler
     *
     * @return $this
     * @codeCoverageIgnore
     */
    public function onBotAdded(callable $handler): self
    {
        return $this->addHandler(UpdateType::BotAdded, $handler);
    }

    /**
     * A convenient alias for addHandler(UpdateType::BotRemoved, $handler).
     *
     * @param callable(Models\Updates\BotRemovedFromChatUpdate, Api): void $handler
     *
     * @return $this
     * @codeCoverageIgnore
     */
    public function onBotRemoved(callable $handler): self
    {
        return $this->addHandler(UpdateType::BotRemoved, $handler);
    }

    /**
     * A convenient alias for addHandler(UpdateType::DialogMuted, $handler).
     *
     * @param callable(Models\Updates\DialogMutedUpdate, Api): void $handler
     *
     * @return $this
     * @codeCoverageIgnore
     */
    public function onDialogMuted(callable $handler): self
    {
        return $this->addHandler(UpdateType::DialogMuted, $handler);
    }

    /**
     * A convenient alias for addHandler(UpdateType::DialogUnmuted, $handler).
     *
     * @param callable(Models\Updates\DialogUnmutedUpdate, Api): void $handler
     *
     * @return $this
     * @codeCoverageIgnore
     */
    public function onDialogUnmuted(callable $handler): self
    {
        return $this->addHandler(UpdateType::DialogUnmuted, $handler);
    }

    /**
     * A convenient alias for addHandler(UpdateType::DialogCleared, $handler).
     *
     * @param callable(Models\Updates\DialogClearedUpdate, Api): void $handler
     *
     * @return $this
     * @codeCoverageIgnore
     */
    public function onDialogCleared(callable $handler): self
    {
        return $this->addHandler(UpdateType::DialogCleared, $handler);
    }

    /**
     * A convenient alias for addHandler(UpdateType::DialogRemoved, $handler).
     *
     * @param callable(Models\Updates\DialogRemovedUpdate, Api): void $handler
     *
     * @return $this
     * @codeCoverageIgnore
     */
    public function onDialogRemoved(callable $handler): self
    {
        return $this->addHandler(UpdateType::DialogRemoved, $handler);
    }

    /**
     * A convenient alias for addHandler(UpdateType::UserAdded, $handler).
     *
     * @param callable(Models\Updates\UserAddedToChatUpdate, Api): void $handler
     *
     * @return $this
     * @codeCoverageIgnore
     */
    public function onUserAdded(callable $handler): self
    {
        return $this->addHandler(UpdateType::UserAdded, $handler);
    }

    /**
     * A convenient alias for addHandler(UpdateType::UserRemoved, $handler).
     *
     * @param callable(Models\Updates\UserRemovedFromChatUpdate, Api): void $handler
     *
     * @return $this
     * @codeCoverageIgnore
     */
    public function onUserRemoved(callable $handler): self
    {
        return $this->addHandler(UpdateType::UserRemoved, $handler);
    }

    /**
     * A convenient alias for addHandler(UpdateType::BotStarted, $handler).
     *
     * @param callable(Models\Updates\BotStartedUpdate, Api): void $handler
     *
     * @return $this
     * @codeCoverageIgnore
     */
    public function onBotStarted(callable $handler): self
    {
        return $this->addHandler(UpdateType::BotStarted, $handler);
    }

    /**
     * A convenient alias for addHandler(UpdateType::BotStopped, $handler).
     *
     * @param callable(Models\Updates\BotStoppedUpdate, Api): void $handler
     *
     * @return $this
     * @codeCoverageIgnore
     */
    public function onBotStopped(callable $handler): self
    {
        return $this->addHandler(UpdateType::BotStopped, $handler);
    }

    /**
     * A convenient alias for addHandler(UpdateType::ChatTitleChanged, $handler).
     *
     * @param callable(Models\Updates\ChatTitleChangedUpdate, Api): void $handler
     *
     * @return $this
     * @codeCoverageIgnore
     */
    public function onChatTitleChanged(callable $handler): self
    {
        return $this->addHandler(UpdateType::ChatTitleChanged, $handler);
    }

    /**
     * A convenient alias for addHandler(UpdateType::MessageChatCreated, $handler).
     *
     * @param callable(Models\Updates\MessageChatCreatedUpdate, Api): void $handler
     *
     * @return $this
     * @codeCoverageIgnore
     */
    public function onMessageChatCreated(callable $handler): self
    {
        return $this->addHandler(UpdateType::MessageChatCreated, $handler);
    }
}
