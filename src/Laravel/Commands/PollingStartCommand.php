<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Laravel\Commands;

use BushlanovDev\MaxMessengerBot\Enums\UpdateType;
use BushlanovDev\MaxMessengerBot\Laravel\MaxBotManager;
use Illuminate\Console\Command;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Artisan command to start processing updates via long polling.
 */
class PollingStartCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'maxbot:polling:start
                            {--timeout= : Timeout in seconds for long polling (0-90, defaults to maxbot.polling.timeout or 90)}
                            {--types=* : Update types to process (optional, e.g. --types=message_created --types=message_callback)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start the bot to process updates via long polling';

    /**
     * Execute the console command.
     */
    public function handle(MaxBotManager $botManager, Config $config): int
    {
        $timeout = (int)($this->option('timeout') ?? $config->get('maxbot.polling.timeout', 90));

        $updateTypes = null;
        $types = $this->option('types');
        if (is_array($types) && !empty($types)) {
            $updateTypes = [];
            foreach ($types as $type) {
                try {
                    $updateTypes[] = UpdateType::from($type);
                } catch (\ValueError $e) {
                    $this->error("Invalid update type: $type");
                    $this->line('Valid types: ' . implode(', ', array_map(
                        static fn(UpdateType $t) => $t->value,
                        UpdateType::cases(),
                    )));

                    return self::FAILURE;
                }
            }
        }

        $this->info("Starting long polling with a timeout of $timeout seconds... Press Ctrl+C to stop.");

        try {
            $botManager->startLongPolling($timeout, null, $updateTypes);

            // @codeCoverageIgnoreStart
            // This part is unreachable as startLongPolling is an infinite loop
            return self::SUCCESS;
            // @codeCoverageIgnoreEnd
        } catch (Throwable $e) {
            Log::error("Long polling failed to start or crashed: {$e->getMessage()}", [
                'exception' => $e,
            ]);
            $this->error("❌ Long polling failed: {$e->getMessage()}");

            return self::FAILURE;
        }
    }
}
