<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Tests\Models;

use BushlanovDev\MaxMessengerBot\Attributes\ArrayOf;
use BushlanovDev\MaxMessengerBot\Models\BotCommand;
use BushlanovDev\MaxMessengerBot\Models\BotCommandsInfo;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(BotCommandsInfo::class)]
#[UsesClass(BotCommand::class)]
#[UsesClass(ArrayOf::class)]
final class BotCommandsInfoTest extends TestCase
{
    #[Test]
    public function canBeCreatedFromArray(): void
    {
        $info = BotCommandsInfo::fromArray([
            'commands' => [
                ['name' => 'start', 'description' => 'Start the bot'],
            ],
        ]);

        $this->assertCount(1, $info->commands);
        $this->assertInstanceOf(BotCommand::class, $info->commands[0]);
        $this->assertSame('start', $info->commands[0]->name);
        $this->assertSame('Start the bot', $info->commands[0]->description);
    }

    #[Test]
    public function commandsAreNullWhenTheBotHasNone(): void
    {
        $this->assertNull(BotCommandsInfo::fromArray([])->commands);
    }

    #[Test]
    public function toArraySerializesCommands(): void
    {
        $info = new BotCommandsInfo([new BotCommand('help', 'Help')]);

        $this->assertSame(['commands' => [['name' => 'help', 'description' => 'Help']]], $info->toArray());
    }
}
