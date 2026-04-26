<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Tests\Laravel\Commands;

use BushlanovDev\MaxMessengerBot\Api;
use BushlanovDev\MaxMessengerBot\Enums\UpdateType;
use BushlanovDev\MaxMessengerBot\Laravel\Commands\WebhookListCommand;
use BushlanovDev\MaxMessengerBot\Models\Subscription;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\Console\Tester\CommandTester;

#[CoversClass(WebhookListCommand::class)]
#[AllowMockObjectsWithoutExpectations]
#[UsesClass(Api::class)]
#[UsesClass(Subscription::class)]
final class WebhookListCommandTest extends TestCase
{
    private MockObject&Api $apiMock;
    private WebhookListCommand $command;

    protected function setUp(): void
    {
        parent::setUp();

        $this->apiMock = $this->createMock(Api::class);
        $this->container->instance(Api::class, $this->apiMock);

        $this->command = new WebhookListCommand();
        $this->command->setLaravel($this->container);

        $application = new ConsoleApplication();
        $application->add($this->command);
        $commandInApp = $application->find('maxbot:webhook:list');
        $this->tester = new CommandTester($commandInApp);
    }

    #[Test]
    public function handleDisplaysTableWithActiveSubscriptions(): void
    {
        $timestamp = 1678886400; // 2023-03-15 13:20:00 UTC
        $subscriptions = [
            new Subscription(
                'https://example.com/hook1',
                $timestamp,
                [UpdateType::MessageCreated, UpdateType::BotStarted],
                '0.0.6'
            ),
            new Subscription(
                'https://example.com/hook2',
                $timestamp + 3600,
                null, // Should be rendered as 'all'
                '0.0.6'
            ),
        ];

        $this->apiMock
            ->expects($this->once())
            ->method('getSubscriptions')
            ->willReturn($subscriptions);

        $this->tester->execute([]);
        $this->tester->assertCommandIsSuccessful();

        $output = $this->tester->getDisplay();

        $this->assertStringContainsString('https://example.com/hook1', $output);
        $this->assertStringContainsString('https://example.com/hook2', $output);
        $this->assertStringContainsString('message_created, bot_started', $output);
        $this->assertStringContainsString('all', $output);
        $this->assertStringContainsString(date('Y-m-d H:i:s', $timestamp), $output);
        $this->assertStringContainsString(date('Y-m-d H:i:s', $timestamp + 3600), $output);
    }

    #[Test]
    public function handleDisplaysMessageWhenNoSubscriptionsExist(): void
    {
        $this->apiMock
            ->expects($this->once())
            ->method('getSubscriptions')
            ->willReturn([]);

        $this->tester->execute([]);
        $this->tester->assertCommandIsSuccessful();

        $output = $this->tester->getDisplay();
        $this->assertStringContainsString('No active webhook subscriptions found.', $output);
        $this->assertStringNotContainsString('URL', $output, 'Table headers should not be displayed.');
    }

    #[Test]
    public function handleCatchesExceptionAndLogsError(): void
    {
        $exceptionMessage = 'API is down';
        $exception = new \RuntimeException($exceptionMessage);

        $this->apiMock
            ->expects($this->once())
            ->method('getSubscriptions')
            ->willThrowException($exception);

        Log::shouldReceive('error')
            ->once()
            ->with("Webhook list error: $exceptionMessage", ['exception' => $exception]);

        $statusCode = $this->tester->execute([]);

        $this->assertSame(1, $statusCode, 'Command should return a failure exit code.');

        $output = $this->tester->getDisplay();
        $this->assertStringContainsString("❌ Webhook list error: $exceptionMessage", $output);
    }
}
