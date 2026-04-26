<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Tests\Models\Attachments\Buttons\Inline;

use BushlanovDev\MaxMessengerBot\Enums\InlineButtonType;
use BushlanovDev\MaxMessengerBot\Models\Attachments\Buttons\Inline\ClipboardButton;
use BushlanovDev\MaxMessengerBot\Models\Attachments\Buttons\Inline\OpenAppButton;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ClipboardButton::class)]
final class ClipboardButtonTest extends TestCase
{
    #[Test]
    public function toArraySerializesCorrectly(): void
    {
        $button = new ClipboardButton('display', 'copy');

        $expectedArray = [
            'text' => 'display',
            'payload' => 'copy',
            'type' => InlineButtonType::Clipboard->value,

        ];

        $resultArray = $button->toArray();

        $this->assertSame($expectedArray, $resultArray);
    }

    #[Test]
    public function fromArrayHydratesCorrectly(): void
    {
        $data = [
            'type' => 'clipboard',
            'text' => 'display',
            'payload' => 'copy'
        ];

        $button = ClipboardButton::fromArray($data);

        $this->assertInstanceOf(ClipboardButton::class, $button);
        $this->assertSame(InlineButtonType::Clipboard, $button->type);
        $this->assertSame('display', $button->text);
        $this->assertSame('copy', $button->payload);
    }
}
