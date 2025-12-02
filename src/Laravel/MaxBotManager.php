<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Laravel;

use BushlanovDev\MaxMessengerBot\Api;
use BushlanovDev\MaxMessengerBot\Enums\UpdateType;
use BushlanovDev\MaxMessengerBot\Exceptions\SecurityException;
use BushlanovDev\MaxMessengerBot\Exceptions\SerializationException;
use BushlanovDev\MaxMessengerBot\Models\Updates\AbstractUpdate;
use BushlanovDev\MaxMessengerBot\UpdateDispatcher;
use BushlanovDev\MaxMessengerBot\WebhookHandler;
use BushlanovDev\MaxMessengerBot\LongPollingHandler;
use GuzzleHttp\Psr7\ServerRequest;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Max Bot Manager for Laravel integration.
 *
 * Provides convenient methods for integrating Max Bot with Laravel applications.
 * Handles webhook processing, long polling, and event dispatching within Laravel context.
 */
class MaxBotManager
{
    /**
     * @param Container $container
     * @param Api $api
     * @param UpdateDispatcher $dispatcher
     */
    public function __construct(
        private readonly Container $container,
        private readonly Api $api,
        private readonly UpdateDispatcher $dispatcher,
    ) {
    }

    /**
     * Handle webhook request in Laravel controller.
     *
     * Example usage in a controller:
     * ```php
     * public function webhook(Request $request, MaxBotManager $botManager)
     * {
     *     return $botManager->handleWebhook($request);
     * }
     * ```
     */
    public function handleWebhook(Request $request): JsonResponse|Response
    {
        try {
            /** @var WebhookHandler $webhookHandler */
            $webhookHandler = $this->container->make(WebhookHandler::class);

            $headers = array_map(function ($values) {
                return array_filter($values, fn($value) => $value !== null);
            }, $request->headers->all());

            $webhookHandler->handle(
                new ServerRequest(
                    $request->getMethod(),
                    $request->getUri(),
                    $headers,
                    $request->getContent(),
                    $request->getProtocolVersion() ?? '1.1',
                )
            );

            return new Response('', 200);
        } catch (SecurityException $e) {
            Log::warning("Webhook security error: {$e->getMessage()}", [
                'exception' => $e,
                'headers' => $request->headers->all(),
            ]);

            return new JsonResponse([
                'status' => 'error',
                'message' => 'Forbidden',
            ], 403);
        } catch (SerializationException $e) {
            Log::error("Webhook serialization error: {$e->getMessage()}", [
                'exception' => $e,
                'request_content' => $request->getContent(),
            ]);

            return new JsonResponse([
                'status' => 'error',
                'message' => 'Bad Request',
            ], 400);
        } catch (Throwable $e) {
            Log::error("Webhook processing error: {$e->getMessage()}", [
                'exception' => $e,
                'request_content' => $request->getContent(),
                'headers' => $request->headers->all(),
            ]);

            return new JsonResponse([
                'status' => 'error',
                'message' => 'Internal Server Error',
            ], 500);
        }
    }

    /**
     * Start long polling in Laravel context.
     * This method should be called from a Laravel command or job.
     * It will run indefinitely until stopped.
     *
     * @throws BindingResolutionException
     * @codeCoverageIgnore
     */
    public function startLongPolling(int $timeout = 90, ?int $marker = null): void
    {
        /** @var LongPollingHandler $longPolling */
        $longPolling = $this->container->make(LongPollingHandler::class);

        $longPolling->handle($timeout, $marker);
    }

    /**
     * Registers a handler for a specific update type.
     *
     * @param UpdateType $type The type of update to handle.
     * @param callable|string $handler Can be a closure, callable, or Laravel container binding.
     *
     * @throws BindingResolutionException
     * @codeCoverageIgnore
     */
    public function addHandler(UpdateType $type, callable|string $handler): void
    {
        $this->dispatcher->addHandler($type, $this->resolveHandler($handler));
    }

    /**
     * Register a command handler.
     *
     * @param string $command Command name (without slash)
     * @param callable|string $handler Can be a closure, callable, or Laravel container binding.
     *
     * @throws BindingResolutionException
     * @codeCoverageIgnore
     */
    public function onCommand(string $command, callable|string $handler): void
    {
        $this->dispatcher->onCommand($command, $this->resolveHandler($handler));
    }

    /**
     * Register a message created handler.
     *
     * @param callable|string $handler Can be a closure, callable, or Laravel container binding.
     *
     * @throws BindingResolutionException
     * @codeCoverageIgnore
     */
    public function onMessageCreated(callable|string $handler): void
    {
        $this->dispatcher->onMessageCreated($this->resolveHandler($handler));
    }

    /**
     * Register a callback handler.
     *
     * @param callable|string $handler Can be a closure, callable, or Laravel container binding.
     *
     * @throws BindingResolutionException
     * @codeCoverageIgnore
     */
    public function onMessageCallback(callable|string $handler): void
    {
        $this->dispatcher->onMessageCallback($this->resolveHandler($handler));
    }

    /**
     * Register a message edited handler.
     *
     * @param callable|string $handler Can be a closure, callable, or Laravel container binding.
     *
     * @throws BindingResolutionException
     * @codeCoverageIgnore
     */
    public function onMessageEdited(callable|string $handler): void
    {
        $this->dispatcher->onMessageEdited($this->resolveHandler($handler));
    }

    /**
     * Register a message removed handler.
     *
     * @param callable|string $handler Can be a closure, callable, or Laravel container binding.
     *
     * @throws BindingResolutionException
     * @codeCoverageIgnore
     */
    public function onMessageRemoved(callable|string $handler): void
    {
        $this->dispatcher->onMessageRemoved($this->resolveHandler($handler));
    }

    /**
     * Register a bot added handler.
     *
     * @param callable|string $handler Can be a closure, callable, or Laravel container binding.
     *
     * @throws BindingResolutionException
     * @codeCoverageIgnore
     */
    public function onBotAdded(callable|string $handler): void
    {
        $this->dispatcher->onBotAdded($this->resolveHandler($handler));
    }

    /**
     * Register a bot removed handler.
     *
     * @param callable|string $handler Can be a closure, callable, or Laravel container binding.
     *
     * @throws BindingResolutionException
     * @codeCoverageIgnore
     */
    public function onBotRemoved(callable|string $handler): void
    {
        $this->dispatcher->onBotRemoved($this->resolveHandler($handler));
    }

    /**
     * Register a dialog mute handler.
     *
     * @param callable|string $handler Can be a closure, callable, or Laravel container binding.
     *
     * @throws BindingResolutionException
     * @codeCoverageIgnore
     */
    public function onDialogMuted(callable|string $handler): void
    {
        $this->dispatcher->onDialogMuted($this->resolveHandler($handler));
    }

    /**
     * Register a user added handler.
     *
     * @param callable|string $handler Can be a closure, callable, or Laravel container binding.
     *
     * @throws BindingResolutionException
     * @codeCoverageIgnore
     */
    public function onUserAdded(callable|string $handler): void
    {
        $this->dispatcher->onUserAdded($this->resolveHandler($handler));
    }

    /**
     * Register a user removed handler.
     *
     * @param callable|string $handler Can be a closure, callable, or Laravel container binding.
     *
     * @throws BindingResolutionException
     * @codeCoverageIgnore
     */
    public function onUserRemoved(callable|string $handler): void
    {
        $this->dispatcher->onUserRemoved($this->resolveHandler($handler));
    }

    /**
     * Register a bot started handler.
     *
     * @param callable|string $handler Can be a closure, callable, or Laravel container binding.
     *
     * @throws BindingResolutionException
     * @codeCoverageIgnore
     */
    public function onBotStarted(callable|string $handler): void
    {
        $this->dispatcher->onBotStarted($this->resolveHandler($handler));
    }

    /**
     * Register a bot stopped handler.
     *
     * @param callable|string $handler Can be a closure, callable, or Laravel container binding.
     *
     * @throws BindingResolutionException
     * @codeCoverageIgnore
     */
    public function onBotStopped(callable|string $handler): void
    {
        $this->dispatcher->onBotStopped($this->resolveHandler($handler));
    }

    /**
     * Register a chat title changed handler.
     *
     * @param callable|string $handler Can be a closure, callable, or Laravel container binding.
     *
     * @throws BindingResolutionException
     * @codeCoverageIgnore
     */
    public function onChatTitleChanged(callable|string $handler): void
    {
        $this->dispatcher->onChatTitleChanged($this->resolveHandler($handler));
    }

    /**
     * Register a message chat created handler.
     *
     * @param callable|string $handler Can be a closure, callable, or Laravel container binding.
     *
     * @throws BindingResolutionException
     * @codeCoverageIgnore
     */
    public function onMessageChatCreated(callable|string $handler): void
    {
        $this->dispatcher->onMessageChatCreated($this->resolveHandler($handler));
    }

    /**
     * Get the API instance.
     * @codeCoverageIgnore
     */
    public function getApi(): Api
    {
        return $this->api;
    }

    /**
     * Get the update dispatcher.
     * @codeCoverageIgnore
     */
    public function getDispatcher(): UpdateDispatcher
    {
        return $this->dispatcher;
    }

    /**
     * Resolve a handler that might be a Laravel container binding.
     *
     * @param callable|string $handler
     * @return callable
     * @phpstan-return callable(AbstractUpdate, Api): void
     * @throws BindingResolutionException
     */
    private function resolveHandler(callable|string $handler): callable
    {
        if (is_string($handler)) {
            if ($this->container->bound($handler)) {
                $resolved = $this->container->make($handler);

                if (is_callable($resolved)) {
                    return $resolved;
                }

                if (is_object($resolved) && method_exists($resolved, 'handle')) {
                    /** @var callable */
                    return [$resolved, 'handle'];
                }

                throw new \InvalidArgumentException(
                    "Handler class '$handler' is not callable and doesn't have a handle method."
                );
            }

            if (str_contains($handler, '@')) {
                [$class, $method] = explode('@', $handler, 2);
                $instance = $this->container->make($class);
                /** @var callable */
                return [$instance, $method];
            }

            if (class_exists($handler)) {
                $instance = $this->container->make($handler);
                if (method_exists($instance, 'handle')) {
                    /** @var callable */
                    return [$instance, 'handle'];
                }
            }

            throw new \InvalidArgumentException("Unable to resolve handler: $handler");
        }

        return $handler;
    }
}
