<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Tests\Laravel\Commands;

use BushlanovDev\MaxMessengerBot\Api;
use BushlanovDev\MaxMessengerBot\Enums\UpdateType;
use BushlanovDev\MaxMessengerBot\Laravel\Commands\WebhookSubscribeCommand;
use BushlanovDev\MaxMessengerBot\Models\Result;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\Console\Tester\CommandTester;

#[CoversClass(WebhookSubscribeCommand::class)]
#[AllowMockObjectsWithoutExpectations]
#[UsesClass(Api::class)]
#[UsesClass(Result::class)]
final class WebhookSubscribeCommandTest extends TestCase
{
    private MockObject&Api $apiMock;
    private MockObject&Config $configMock;
    private WebhookSubscribeCommand $command;

    protected function setUp(): void
    {
        parent::setUp();

        $this->apiMock = $this->createMock(Api::class);
        $this->configMock = $this->createMock(Config::class);
        $this->container->instance(Api::class, $this->apiMock);
        $this->container->instance(Config::class, $this->configMock);

        $this->command = new WebhookSubscribeCommand();
        $this->command->setLaravel($this->container);

        $application = new ConsoleApplication();
        $application->add($this->command);
        $commandInApp = $application->find('maxbot:webhook:subscribe');
        $this->tester = new CommandTester($commandInApp);
    }

    #[Test]
    public function handleSuccessfullySubscribesWithAllOptions(): void
    {
        $url = 'https://example.com/webhook';
        $secret = 'my-super-secret';
        $types = ['message_created', 'bot_started'];
        $expectedUpdateTypes = [UpdateType::MessageCreated, UpdateType::BotStarted];

        $this->apiMock
            ->expects($this->once())
            ->method('subscribe')
            ->with($url, $secret, $expectedUpdateTypes)
            ->willReturn(new Result(true, null));

        $this->tester->execute([
            'url' => $url,
            '--secret' => $secret,
            '--types' => $types,
        ]);
        $this->tester->assertCommandIsSuccessful();

        $output = $this->tester->getDisplay();
        $this->assertStringContainsString('✅ Successfully subscribed to webhook!', $output);
        $this->assertStringContainsString("URL: $url", $output);
        $this->assertStringContainsString("Secret: ***************", $output);
        $this->assertStringContainsString("Update types: message_created, bot_started", $output);
    }

    #[Test]
    public function handleUsesSecretFromConfigWhenOptionIsNotProvided(): void
    {
        $url = 'https://example.com/webhook';
        $configSecret = 'secret-from-config';

        $this->configMock
            ->expects($this->once())
            ->method('get')
            ->with('maxbot.webhook_secret')
            ->willReturn($configSecret);

        $this->apiMock
            ->expects($this->once())
            ->method('subscribe')
            ->with($url, $configSecret, null)
            ->willReturn(new Result(true, null));

        $this->tester->execute(['url' => $url]);
        $this->tester->assertCommandIsSuccessful();
    }

    #[Test]
    public function handleFailsForInvalidUrl(): void
    {
        $this->apiMock->expects($this->never())->method('subscribe');

        $statusCode = $this->tester->execute(['url' => 'not-a-valid-url']);

        $this->assertSame(1, $statusCode);
        $this->assertStringContainsString('Invalid URL provided.', $this->tester->getDisplay());
    }

    #[Test]
    public function handleFailsForInvalidUpdateType(): void
    {
        $this->apiMock->expects($this->never())->method('subscribe');

        $statusCode = $this->tester->execute([
            'url' => 'https://example.com',
            '--types' => ['message_created', 'invalid_type'],
        ]);

        $this->assertSame(1, $statusCode);
        $this->assertStringContainsString('Invalid update type: invalid_type', $this->tester->getDisplay());
    }

    #[Test]
    public function handleDisplaysApiErrorMessageOnFailure(): void
    {
        $url = 'https://example.com/webhook';
        $apiErrorMessage = 'URL is already subscribed';

        $this->apiMock
            ->expects($this->once())
            ->method('subscribe')
            ->willReturn(new Result(false, $apiErrorMessage));

        $statusCode = $this->tester->execute(['url' => $url]);

        $this->assertSame(1, $statusCode);
        $output = $this->tester->getDisplay();
        $this->assertStringContainsString('❌ Failed to subscribe to webhook.', $output);
        $this->assertStringContainsString("Response: $apiErrorMessage", $output);
    }

    #[Test]
    public function handleCatchesExceptionAndLogsError(): void
    {
        $url = 'https://example.com/webhook';
        $exceptionMessage = 'Network error';
        $exception = new \RuntimeException($exceptionMessage);

        $this->apiMock
            ->expects($this->once())
            ->method('subscribe')
            ->willThrowException($exception);

        Log::shouldReceive('error')
            ->once()
            ->with("Webhook subscription error: $exceptionMessage", ['exception' => $exception]);

        $statusCode = $this->tester->execute(['url' => $url]);

        $this->assertSame(1, $statusCode);
        $output = $this->tester->getDisplay();
        $this->assertStringContainsString("❌ Webhook subscription error: $exceptionMessage", $output);
    }
}
