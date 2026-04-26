<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Tests;

use BushlanovDev\MaxMessengerBot\Api;
use BushlanovDev\MaxMessengerBot\Enums\UpdateType;
use BushlanovDev\MaxMessengerBot\Exceptions\NetworkException;
use BushlanovDev\MaxMessengerBot\LongPollingHandler;
use BushlanovDev\MaxMessengerBot\Models\UpdateList;
use BushlanovDev\MaxMessengerBot\Models\Updates\AbstractUpdate;
use BushlanovDev\MaxMessengerBot\Models\Updates\BotStartedUpdate;
use BushlanovDev\MaxMessengerBot\Models\UserWithPhoto;
use BushlanovDev\MaxMessengerBot\UpdateDispatcher;
use Error;
use Exception;
use phpmock\phpunit\PHPMock;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

#[CoversClass(LongPollingHandler::class)]
#[AllowMockObjectsWithoutExpectations]
#[UsesClass(UpdateDispatcher::class)]
#[UsesClass(UpdateList::class)]
#[UsesClass(AbstractUpdate::class)]
#[UsesClass(BotStartedUpdate::class)]
#[UsesClass(UserWithPhoto::class)]
final class LongPollingHandlerTest extends TestCase
{
    use PHPMock;

    /**
     * @param AbstractUpdate[] $updatesToReturn
     * @param int $expectedDispatchCount
     * @param int|null $expectedMarker
     */
    #[Test]
    #[DataProvider('processUpdatesProvider')]
    public function processUpdates(
        array $updatesToReturn,
        int $expectedDispatchCount,
        ?int $expectedMarker,
    ): void {
        $apiMock = $this->createMock(Api::class);
        $loggerMock = $this->createMock(LoggerInterface::class);
        $dispatcher = new UpdateDispatcher($apiMock);

        $updateList = new UpdateList($updatesToReturn, $expectedMarker);

        $apiMock->expects($this->once())
            ->method('getUpdates')
            ->with($this->isNull(), $this->equalTo(90), $this->isNull())
            ->willReturn($updateList);

        $dispatchCount = 0;
        $dispatcher->addHandler(UpdateType::BotStarted, function () use (&$dispatchCount) {
            $dispatchCount++;
        });

        $handler = new LongPollingHandler($apiMock, $dispatcher, $loggerMock);

        $returnedMarker = $handler->processUpdates(90, null);

        $this->assertSame(
            $expectedDispatchCount,
            $dispatchCount,
            "Dispatcher should be called $expectedDispatchCount times."
        );
        $this->assertSame($expectedMarker, $returnedMarker, 'Method should return the correct marker.');
    }

    public static function processUpdatesProvider(): array
    {
        $user = new UserWithPhoto(1, 'Test', null, null, false, time(), null, null, null);
        $update1 = new BotStartedUpdate(time(), 1, $user, null, null);
        $update2 = new BotStartedUpdate(time(), 2, $user, null, null);

        return [
            'with two updates' => [
                'updatesToReturn' => [$update1, $update2],
                'expectedDispatchCount' => 2,
                'expectedMarker' => 12345,
            ],
            'with no updates' => [
                'updatesToReturn' => [],
                'expectedDispatchCount' => 0,
                'expectedMarker' => 54321,
            ],
        ];
    }

    #[Test]
    #[PreserveGlobalState(false)]
    #[RunInSeparateProcess]
    public function runCatchesNetworkExceptionAndSleeps5Seconds(): void
    {
        $apiMock = $this->createMock(Api::class);
        $loggerMock = $this->createMock(LoggerInterface::class);
        $dispatcher = new UpdateDispatcher($apiMock);

        $sleepMock = $this->getFunctionMock('BushlanovDev\MaxMessengerBot', 'sleep');
        $sleepMock->expects($this->once())->with(5);

        $apiMock->expects($this->exactly(2))
            ->method('getUpdates')
            ->willReturnOnConsecutiveCalls(
                $this->throwException(new NetworkException('Connection timeout')),
                $this->throwException(new Error('Stop test loop')),
            );

        $loggerMock->expects($this->once())
            ->method('error')
            ->with($this->stringContains('Long-polling network error'), $this->anything());

        $handler = new LongPollingHandler($apiMock, $dispatcher, $loggerMock);

        try {
            $handler->handle();
        } catch (Error $e) {
            $this->assertSame('Stop test loop', $e->getMessage());
        }
    }

    #[Test]
    #[PreserveGlobalState(false)]
    #[RunInSeparateProcess]
    public function runCatchesGenericExceptionAndSleeps1Second(): void
    {
        $apiMock = $this->createMock(Api::class);
        $loggerMock = $this->createMock(LoggerInterface::class);
        $dispatcher = new UpdateDispatcher($apiMock);

        $sleepMock = $this->getFunctionMock('BushlanovDev\MaxMessengerBot', 'sleep');
        $sleepMock->expects($this->once())->with(1);

        $apiMock->expects($this->exactly(2))
            ->method('getUpdates')
            ->willReturnOnConsecutiveCalls(
                $this->throwException(new Exception('Something went wrong')),
                $this->throwException(new Error('Stop test loop')),
            );

        $loggerMock->expects($this->once())
            ->method('error')
            ->with($this->stringContains('An error occurred during long-polling'), $this->anything());

        $handler = new LongPollingHandler($apiMock, $dispatcher, $loggerMock);

        try {
            $handler->handle();
        } catch (Error $e) {
            $this->assertSame('Stop test loop', $e->getMessage());
        }
    }

    #[Test]
    public function processUpdatesContinuesAndLogsWhenHandlerThrows(): void
    {
        $apiMock = $this->createMock(Api::class);
        $loggerMock = $this->createMock(LoggerInterface::class);

        $dispatcher = new UpdateDispatcher($apiMock, $loggerMock);

        $user = new UserWithPhoto(1, 'Test', null, null, false, time(), null, null, null);
        $updateToFail = new BotStartedUpdate(time(), 1, $user, null, null);
        $updateToSucceed = new BotStartedUpdate(time(), 2, $user, null, null);

        $updateList = new UpdateList([$updateToFail, $updateToSucceed], 12345);
        $exception = new Exception('Error inside handler');

        $apiMock->expects($this->once())
            ->method('getUpdates')
            ->willReturn($updateList);

        $handlerCallCount = 0;
        $dispatcher->addHandler(UpdateType::BotStarted, function () use (&$handlerCallCount, $exception) {
            $currentCall = $handlerCallCount++;
            if ($currentCall === 0) {
                throw $exception;
            }
        });

        $loggerMock->expects($this->once())
            ->method('error')
            ->with('Error dispatching update', ['message' => 'Error inside handler', 'exception' => $exception]);

        $handler = new LongPollingHandler($apiMock, $dispatcher, $loggerMock);

        $returnedMarker = $handler->processUpdates(90, null);

        $this->assertSame(12345, $returnedMarker, 'Method should return marker even if a handler failed.');
        $this->assertSame(2, $handlerCallCount, 'Dispatcher should have attempted to process both updates.');
    }
}
