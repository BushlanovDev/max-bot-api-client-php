<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot;

use BushlanovDev\MaxMessengerBot\Enums\UpdateType;
use BushlanovDev\MaxMessengerBot\Exceptions\NetworkException;
use Psr\Log\LoggerInterface;

/**
 * Handles receiving updates via long polling.
 */
final readonly class LongPollingHandler
{
    /**
     * @param Api $api
     * @param UpdateDispatcher $dispatcher The update dispatcher.
     * @param LoggerInterface $logger PSR LoggerInterface.
     * @codeCoverageIgnore
     */
    public function __construct(
        private Api $api,
        private UpdateDispatcher $dispatcher,
        private LoggerInterface $logger,
    ) {
        if (!(\PHP_SAPI === 'cli')) {
            throw new \RuntimeException('LongPollingHandler can only be used in CLI mode.');
        }
    }

    /**
     * Processes a single batch of updates. Useful for custom loop implementations or for testing.
     *
     * @param int $timeout Timeout for the getUpdates call.
     * @param int|null $marker The marker for which updates to fetch.
     * @param UpdateType[]|null $types Update types to fetch, null for all.
     * @return int|null The new marker to be used for the next iteration.
     * @throws \Exception Re-throws exceptions from the API or dispatcher.
     */
    public function processUpdates(int $timeout, ?int $marker, ?array $types = null): ?int
    {
        $updateList = $this->api->getUpdates(timeout: $timeout, marker: $marker, types: $types);

        foreach ($updateList->updates as $update) {
            try {
                $this->dispatcher->dispatch($update);
            } catch (\Throwable $e) {
                $this->logger->error('Error dispatching update', [
                    'message' => $e->getMessage(),
                    'exception' => $e,
                ]);
            }
        }

        return $updateList->marker;
    }

    /**
     * Starts a long-polling loop to process updates.
     * This method will run indefinitely until the script is terminated.
     *
     * @param int $timeout Timeout in seconds for long polling (0-90).
     * @param int|null $marker Initial marker. Pass `null` to get updates you didn't get yet.
     * @param UpdateType[]|null $types Update types to fetch, null for all.
     */
    public function handle(int $timeout = 90, ?int $marker = null, ?array $types = null): void
    {
        $this->listenSignals();
        // @phpstan-ignore-next-line
        while (true) {
            try {
                $marker = $this->processUpdates($timeout, $marker, $types);
            } catch (NetworkException $e) {
                $this->logger->error(
                    'Long-polling network error: {message}',
                    ['message' => $e->getMessage(), 'exception' => $e],
                );
                sleep(5);
            } catch (\Exception $e) {
                $this->logger->error(
                    'An error occurred during long-polling: {message}',
                    ['message' => $e->getMessage(), 'exception' => $e],
                );
                sleep(1);
            }
        }
    }

    /**
     * @codeCoverageIgnore
     */
    protected function listenSignals(): void
    {
        if (extension_loaded('pcntl')) {
            pcntl_async_signals(true);

            $kill = static function () {
                exit(0);
            };

            pcntl_signal(SIGINT, $kill);
            pcntl_signal(SIGQUIT, $kill);
            pcntl_signal(SIGTERM, $kill);
        }
    }
}
