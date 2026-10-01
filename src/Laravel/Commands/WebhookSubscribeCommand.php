<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Laravel\Commands;

use BushlanovDev\MaxMessengerBot\Api;
use BushlanovDev\MaxMessengerBot\Enums\UpdateType;
use Illuminate\Console\Command;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Artisan command for subscribing to webhook updates.
 */
class WebhookSubscribeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'maxbot:webhook:subscribe
                            {url : The webhook URL to subscribe to}
                            {--secret= : Secret key for webhook verification (optional)}
                            {--types=* : Update types to subscribe to (optional)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Subscribe bot to webhook updates';

    /**
     * Execute the console command.
     */
    public function handle(Api $api, Config $config): int
    {
        $url = (string)$this->argument('url'); // @phpstan-ignore-line
        $secret = $this->option('secret') ?? $config->get('maxbot.webhook_secret');
        $types = $this->option('types');

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            $this->error('Invalid URL provided.');

            return self::FAILURE;
        }

        $updateTypes = null;
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

        $this->info('Subscribing to webhook...');
        $this->line("URL: $url");
        if ($secret) {
            $this->line("Secret: " . str_repeat('*', strlen($secret)));
        }
        if ($updateTypes) {
            $this->line("Update types: " . implode(', ', array_map(fn($type) => $type->value, $updateTypes)));
        } else {
            $this->line("Update types: All (default)");
        }

        try {
            $result = $api->subscribe($url, $secret, $updateTypes);

            if ($result->success) {
                $this->info('✅ Successfully subscribed to webhook!');

                return self::SUCCESS;
            } else {
                $this->error('❌ Failed to subscribe to webhook.');
                $this->line("Response: $result->message");

                return self::FAILURE;
            }
        } catch (Throwable $e) {
            Log::error("Webhook subscription error: {$e->getMessage()}", [
                'exception' => $e,
            ]);
            $this->error("❌ Webhook subscription error: {$e->getMessage()}");

            return self::FAILURE;
        }
    }
}
