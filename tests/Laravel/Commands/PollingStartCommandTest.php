<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Tests\Laravel\Commands;

use BushlanovDev\MaxMessengerBot\Laravel\Commands\PollingStartCommand;
use BushlanovDev\MaxMessengerBot\Laravel\MaxBotManager;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\Console\Tester\CommandTester;

#[CoversClass(PollingStartCommand::class)]
#[AllowMockObjectsWithoutExpectations]
#[UsesClass(MaxBotManager::class)]
final class PollingStartCommandTest extends TestCase
{
    private MockObject&MaxBotManager $botManagerMock;
    private PollingStartCommand $command;

    protected function setUp(): void
    {
        parent::setUp();

        $this->botManagerMock = $this->createMock(MaxBotManager::class);
        $this->container->instance(MaxBotManager::class, $this->botManagerMock);
        $this->container->alias(MaxBotManager::class, 'maxbot.manager');

        $this->command = new PollingStartCommand();
        $this->command->setLaravel($this->container);

        $application = new ConsoleApplication();
        $application->add($this->command);
        $commandInApp = $application->find('maxbot:polling:start');
        $this->tester = new CommandTester($commandInApp);
    }

    #[Test]
    public function handleSuccessfullyCallsManagerWithCustomTimeout(): void
    {
        $timeout = 60;

        $this->botManagerMock
            ->expects($this->once())
            ->method('startLongPolling')
            ->with($timeout);

        $this->tester->execute(['--timeout' => $timeout]);
        $this->tester->assertCommandIsSuccessful();

        $output = $this->tester->getDisplay();
        $this->assertStringContainsString("Starting long polling with a timeout of $timeout seconds...", $output);
    }

    #[Test]
    public function handleSuccessfullyUsesDefaultTimeout(): void
    {
        $defaultTimeout = 90;

        $this->botManagerMock
            ->expects($this->once())
            ->method('startLongPolling')
            ->with($defaultTimeout);

        $this->tester->execute([]);
        $this->tester->assertCommandIsSuccessful();

        $output = $this->tester->getDisplay();
        $this->assertStringContainsString(
            "Starting long polling with a timeout of $defaultTimeout seconds...",
            $output
        );
    }

    #[Test]
    public function handleCatchesExceptionAndLogsError(): void
    {
        $exceptionMessage = 'Something went wrong';
        $exception = new \RuntimeException($exceptionMessage);

        $this->botManagerMock
            ->expects($this->once())
            ->method('startLongPolling')
            ->willThrowException($exception);

        Log::shouldReceive('error')
            ->once()
            ->with(
                "Long polling failed to start or crashed: $exceptionMessage",
                ['exception' => $exception],
            );

        $statusCode = $this->tester->execute([]);

        $this->assertSame(1, $statusCode, 'Command should return a failure exit code.');

        $output = $this->tester->getDisplay();
        $this->assertStringContainsString("❌ Long polling failed: $exceptionMessage", $output);
    }
}
