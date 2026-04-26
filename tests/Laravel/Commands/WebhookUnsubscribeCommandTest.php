<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Tests\Laravel\Commands;

use BushlanovDev\MaxMessengerBot\Api;
use BushlanovDev\MaxMessengerBot\Laravel\Commands\WebhookUnsubscribeCommand;
use BushlanovDev\MaxMessengerBot\Models\Result;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\Console\Tester\CommandTester;

#[CoversClass(WebhookUnsubscribeCommand::class)]
#[AllowMockObjectsWithoutExpectations]
#[UsesClass(Api::class)]
#[UsesClass(Result::class)]
final class WebhookUnsubscribeCommandTest extends TestCase
{
    private MockObject&Api $apiMock;
    private WebhookUnsubscribeCommand $command;

    protected function setUp(): void
    {
        parent::setUp();

        $this->apiMock = $this->createMock(Api::class);
        $this->container->instance(Api::class, $this->apiMock);

        $this->command = new WebhookUnsubscribeCommand();
        $this->command->setLaravel($this->container);

        $application = new ConsoleApplication();
        $application->add($this->command);
        $commandInApp = $application->find('maxbot:webhook:unsubscribe');
        $this->tester = new CommandTester($commandInApp);
    }

    #[Test]
    public function handleSuccessfullyUnsubscribesWithConfirmationFlag(): void
    {
        $url = 'https://example.com/webhook';

        $this->apiMock
            ->expects($this->once())
            ->method('unsubscribe')
            ->with($url)
            ->willReturn(new Result(true, null));

        $this->tester->execute([
            'url' => $url,
            '--confirm' => true,
        ]);
        $this->tester->assertCommandIsSuccessful();

        $output = $this->tester->getDisplay();
        $this->assertStringContainsString('✅ Successfully unsubscribed from webhook!', $output);
    }

    #[Test]
    public function handleCancelsWhenNotConfirmed(): void
    {
        $url = 'https://example.com/webhook';

        $this->apiMock->expects($this->never())->method('unsubscribe');
        $this->tester->setInputs(['no']);
        $this->tester->execute(['url' => $url]);
        $this->tester->assertCommandIsSuccessful();

        $output = $this->tester->getDisplay();
        $this->assertStringContainsString('Are you sure you want to unsubscribe', $output);
        $this->assertStringContainsString('Operation cancelled.', $output);
    }

    #[Test]
    public function handleFailsForInvalidUrl(): void
    {
        $this->apiMock->expects($this->never())->method('unsubscribe');

        $statusCode = $this->tester->execute(['url' => 'not-a-valid-url']);

        $this->assertSame(1, $statusCode);
        $this->assertStringContainsString('Invalid URL provided.', $this->tester->getDisplay());
    }

    #[Test]
    public function handleDisplaysApiErrorMessageOnFailure(): void
    {
        $url = 'https://example.com/webhook';
        $apiErrorMessage = 'Subscription not found';

        $this->apiMock
            ->expects($this->once())
            ->method('unsubscribe')
            ->willReturn(new Result(false, $apiErrorMessage));

        $statusCode = $this->tester->execute(['url' => $url, '--confirm' => true]);

        $this->assertSame(1, $statusCode);
        $output = $this->tester->getDisplay();
        $this->assertStringContainsString('❌ Failed to unsubscribe from webhook.', $output);
        $this->assertStringContainsString("Response: $apiErrorMessage", $output);
    }

    #[Test]
    public function handleCatchesExceptionAndLogsError(): void
    {
        $url = 'https://example.com/webhook';
        $exceptionMessage = 'API connection refused';
        $exception = new \RuntimeException($exceptionMessage);

        $this->apiMock
            ->expects($this->once())
            ->method('unsubscribe')
            ->willThrowException($exception);

        Log::shouldReceive('error')
            ->once()
            ->with("Webhook unsubscribe error: $exceptionMessage", ['exception' => $exception]);

        $statusCode = $this->tester->execute(['url' => $url, '--confirm' => true]);

        $this->assertSame(1, $statusCode);
        $output = $this->tester->getDisplay();
        $this->assertStringContainsString("❌ Webhook unsubscribe error: $exceptionMessage", $output);
    }
}
