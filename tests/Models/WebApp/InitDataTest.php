<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Tests\Models\WebApp;

use BushlanovDev\MaxMessengerBot\Enums\ChatType;
use BushlanovDev\MaxMessengerBot\Models\WebApp\InitData;
use BushlanovDev\MaxMessengerBot\Models\WebApp\InitDataChat;
use BushlanovDev\MaxMessengerBot\Models\WebApp\InitDataUser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(InitData::class)]
#[CoversClass(InitDataUser::class)]
#[CoversClass(InitDataChat::class)]
final class InitDataTest extends TestCase
{
    private const string AUTH_DATE = '1761580800';

    #[Test]
    public function canBeCreatedFromInitDataFields(): void
    {
        $fields = [
            'auth_date' => self::AUTH_DATE,
            'query_id' => 'f7b3a1c2',
            'start_param' => 'promo_42',
            'ip' => '10.0.0.1',
            'user' => '{"id":400,"first_name":"Иван","last_name":"Петров","username":"ivan",'
                . '"language_code":"ru","photo_url":"https://example.com/400.jpg"}',
            'chat' => '{"id":500,"type":"DIALOG"}',
        ];

        $data = InitData::fromArray($fields);

        $this->assertSame(1761580800, $data->authDate);
        $this->assertSame('f7b3a1c2', $data->queryId);
        $this->assertSame('promo_42', $data->startParam);
        $this->assertSame('10.0.0.1', $data->ip);
        $this->assertEquals(
            new InitDataUser(400, 'Иван', 'Петров', 'ivan', 'ru', 'https://example.com/400.jpg'),
            $data->user,
        );
        $this->assertEquals(new InitDataChat(500, ChatType::Dialog), $data->chat);
        $this->assertSame($fields, $data->fields);
    }

    #[Test]
    public function absentFieldsAreNull(): void
    {
        $data = InitData::fromArray(['auth_date' => self::AUTH_DATE]);

        $this->assertNull($data->queryId);
        $this->assertNull($data->startParam);
        $this->assertNull($data->ip);
        $this->assertNull($data->user);
        $this->assertNull($data->chat);
    }

    #[Test]
    public function unknownFieldsStayOnlyInFields(): void
    {
        $data = InitData::fromArray([
            'auth_date' => self::AUTH_DATE,
            'platform' => 'ios',
            'user' => '{"id":400,"first_name":"Иван","is_premium":true}',
        ]);

        $this->assertSame('ios', $data->fields['platform']);
        $this->assertEquals(new InitDataUser(400, 'Иван', null, null, null, null), $data->user);
    }

    #[Test]
    public function userWithoutFirstNameIsKept(): void
    {
        $user = InitData::fromArray(['auth_date' => self::AUTH_DATE, 'user' => '{"id":400}'])->user;

        $this->assertSame(400, $user?->id);
        $this->assertNull($user?->firstName);
    }

    #[Test]
    public function fieldTurnedIntoObjectBecomesNull(): void
    {
        $user = InitData::fromArray([
            'auth_date' => self::AUTH_DATE,
            'user' => '{"id":400,"first_name":"Иван","photo_url":{"small":"https://example.com/400.jpg"}}',
        ])->user;

        $this->assertSame('Иван', $user?->firstName);
        $this->assertNull($user?->photoUrl);
    }

    /**
     * @return array<string, array{string, ChatType|null}>
     */
    public static function chats(): array
    {
        return [
            'upper case, as MAX sends it' => ['{"id":500,"type":"DIALOG"}', ChatType::Dialog],
            'lower case' => ['{"id":500,"type":"channel"}', ChatType::Channel],
            'unknown type' => ['{"id":500,"type":"GROUP"}', null],
            'no type' => ['{"id":500}', null],
            'unknown field' => ['{"id":500,"type":"CHAT","title":"Команда"}', ChatType::Chat],
        ];
    }

    #[Test]
    #[DataProvider('chats')]
    public function chatTypeIsMappedToEnumOrNull(string $json, ?ChatType $type): void
    {
        $chat = InitData::fromArray(['auth_date' => self::AUTH_DATE, 'chat' => $json])->chat;

        $this->assertSame(500, $chat?->id);
        $this->assertSame($type, $chat?->type);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function unparsableObjects(): array
    {
        return [
            'not JSON' => ['oops'],
            'JSON scalar' => ['"oops"'],
            'no id' => ['{"first_name":"Иван","type":"DIALOG"}'],
        ];
    }

    #[Test]
    #[DataProvider('unparsableObjects')]
    public function unparsableUserAndChatAreNull(string $json): void
    {
        $data = InitData::fromArray(['auth_date' => self::AUTH_DATE, 'user' => $json, 'chat' => $json]);

        $this->assertNull($data->user);
        $this->assertNull($data->chat);
        $this->assertSame($json, $data->fields['user']);
    }
}
