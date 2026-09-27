<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Tests\Models\Markup;

use BushlanovDev\MaxMessengerBot\Enums\MarkupType;
use BushlanovDev\MaxMessengerBot\Models\Markup\EmphasizedMarkup;
use BushlanovDev\MaxMessengerBot\Models\Markup\HeadingMarkup;
use BushlanovDev\MaxMessengerBot\Models\Markup\HighlightedMarkup;
use BushlanovDev\MaxMessengerBot\Models\Markup\LinkMarkup;
use BushlanovDev\MaxMessengerBot\Models\Markup\MonospacedMarkup;
use BushlanovDev\MaxMessengerBot\Models\Markup\QuoteMarkup;
use BushlanovDev\MaxMessengerBot\Models\Markup\StrikethroughMarkup;
use BushlanovDev\MaxMessengerBot\Models\Markup\StrongMarkup;
use BushlanovDev\MaxMessengerBot\Models\Markup\UnderlineMarkup;
use BushlanovDev\MaxMessengerBot\Models\Markup\UserMentionMarkup;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(LinkMarkup::class)]
#[CoversClass(UserMentionMarkup::class)]
#[CoversClass(StrongMarkup::class)]
#[CoversClass(UnderlineMarkup::class)]
#[CoversClass(StrikethroughMarkup::class)]
#[CoversClass(MonospacedMarkup::class)]
#[CoversClass(EmphasizedMarkup::class)]
#[CoversClass(HeadingMarkup::class)]
#[CoversClass(HighlightedMarkup::class)]
#[CoversClass(QuoteMarkup::class)]
final class MarkupTest extends TestCase
{
    #[Test]
    public function linkMarkupIsCreatedCorrectly(): void
    {
        $data = ['type' => 'link', 'from' => 6, 'length' => 10, 'url' => 'https://dev.max.ru'];
        $markup = LinkMarkup::fromArray($data);
        $this->assertSame(MarkupType::Link, $markup->type);
        $this->assertSame('https://dev.max.ru', $markup->url);
        $this->assertEquals($data, $markup->toArray());
    }

    #[Test]
    public function userMentionMarkupIsCreatedCorrectly(): void
    {
        $data = ['type' => 'user_mention', 'from' => 17, 'length' => 8, 'user_link' => '@username', 'user_id' => 12345];
        $markup = UserMentionMarkup::fromArray($data);
        $this->assertSame(MarkupType::UserMention, $markup->type);
        $this->assertSame('@username', $markup->userLink);
        $this->assertSame(12345, $markup->userId);
        $this->assertEquals($data, $markup->toArray());
    }

    #[Test]
    public function strongMarkupIsCreatesCorrectly(): void
    {
        $data = ['type' => 'strong', 'from' => 0, 'length' => 5];
        $markup = StrongMarkup::fromArray($data);

        $this->assertInstanceOf(StrongMarkup::class, $markup);
        $this->assertSame(MarkupType::Strong, $markup->type);
        $this->assertSame(0, $markup->from);
        $this->assertSame(5, $markup->length);
        $this->assertEquals($data, $markup->toArray());
    }

    #[Test]
    public function underlineMarkupIsCreatesCorrectly(): void
    {
        $data = ['type' => 'underline', 'from' => 1, 'length' => 4];
        $markup = UnderlineMarkup::fromArray($data);

        $this->assertInstanceOf(UnderlineMarkup::class, $markup);
        $this->assertSame(MarkupType::Underline, $markup->type);
        $this->assertSame(1, $markup->from);
        $this->assertSame(4, $markup->length);
        $this->assertEquals($data, $markup->toArray());
    }

    #[Test]
    public function strikethroughMarkupIsCreatesCorrectly(): void
    {
        $data = ['type' => 'strikethrough', 'from' => 1, 'length' => 4];
        $markup = StrikethroughMarkup::fromArray($data);

        $this->assertInstanceOf(StrikethroughMarkup::class, $markup);
        $this->assertSame(MarkupType::Strikethrough, $markup->type);
        $this->assertSame(1, $markup->from);
        $this->assertSame(4, $markup->length);
        $this->assertEquals($data, $markup->toArray());
    }

    #[Test]
    public function monospacedMarkupIsCreatesCorrectly(): void
    {
        $data = ['type' => 'monospaced', 'from' => 1, 'length' => 4];
        $markup = MonospacedMarkup::fromArray($data);

        $this->assertInstanceOf(MonospacedMarkup::class, $markup);
        $this->assertSame(MarkupType::Monospaced, $markup->type);
        $this->assertSame(1, $markup->from);
        $this->assertSame(4, $markup->length);
        $this->assertEquals($data, $markup->toArray());
    }

    #[Test]
    public function emphasizedMarkupIsCreatesCorrectly(): void
    {
        $data = ['type' => 'emphasized', 'from' => 1, 'length' => 4];
        $markup = EmphasizedMarkup::fromArray($data);

        $this->assertInstanceOf(EmphasizedMarkup::class, $markup);
        $this->assertSame(MarkupType::Emphasized, $markup->type);
        $this->assertSame(1, $markup->from);
        $this->assertSame(4, $markup->length);
        $this->assertEquals($data, $markup->toArray());
    }

    #[Test]
    public function headingMarkupIsCreatesCorrectly(): void
    {
        $data = ['type' => 'heading', 'from' => 1, 'length' => 4];
        $markup = HeadingMarkup::fromArray($data);

        $this->assertInstanceOf(HeadingMarkup::class, $markup);
        $this->assertSame(MarkupType::Heading, $markup->type);
        $this->assertSame(1, $markup->from);
        $this->assertSame(4, $markup->length);
        $this->assertEquals($data, $markup->toArray());
    }

    #[Test]
    public function highlightedMarkupIsCreatesCorrectly(): void
    {
        $data = ['type' => 'highlighted', 'from' => 1, 'length' => 4];
        $markup = HighlightedMarkup::fromArray($data);

        $this->assertInstanceOf(HighlightedMarkup::class, $markup);
        $this->assertSame(MarkupType::Highlighted, $markup->type);
        $this->assertSame(1, $markup->from);
        $this->assertSame(4, $markup->length);
        $this->assertEquals($data, $markup->toArray());
    }

    #[Test]
    public function quoteMarkupIsCreatedCorrectly(): void
    {
        $data = ['type' => 'quote', 'from' => 0, 'length' => 12];
        $markup = QuoteMarkup::fromArray($data);

        $this->assertInstanceOf(QuoteMarkup::class, $markup);
        $this->assertSame(MarkupType::Quote, $markup->type);
        $this->assertSame(0, $markup->from);
        $this->assertSame(12, $markup->length);
        $this->assertEquals($data, $markup->toArray());
    }
}
