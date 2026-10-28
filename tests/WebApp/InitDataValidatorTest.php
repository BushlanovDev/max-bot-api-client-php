<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Tests\WebApp;

use BushlanovDev\MaxMessengerBot\Enums\ChatType;
use BushlanovDev\MaxMessengerBot\Exceptions\SecurityException;
use BushlanovDev\MaxMessengerBot\Models\WebApp\InitData;
use BushlanovDev\MaxMessengerBot\Models\WebApp\InitDataChat;
use BushlanovDev\MaxMessengerBot\Models\WebApp\InitDataUser;
use BushlanovDev\MaxMessengerBot\WebApp\InitDataValidator;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(InitDataValidator::class)]
#[UsesClass(InitData::class)]
#[UsesClass(InitDataUser::class)]
#[UsesClass(InitDataChat::class)]
final class InitDataValidatorTest extends TestCase
{
    private const string BOT_TOKEN = '123456:test-token-not-real';
    private const int AUTH_DATE = 1761580800;

    /**
     * Signs init data independently of the validator: HMAC-SHA256 of the sorted `key=value` lines
     * with the key HMAC-SHA256("WebAppData", bot token), as https://dev.max.ru/docs/webapps/validation describes.
     *
     * @param array<string, string> $fields
     */
    private static function sign(array $fields, string $botToken = self::BOT_TOKEN): string
    {
        $sorted = $fields;
        ksort($sorted);
        $dataCheckString = implode("\n", array_map(
            static fn (string $key, string $value): string => $key . '=' . $value,
            array_keys($sorted),
            $sorted,
        ));
        $secretKey = hash_hmac('sha256', $botToken, 'WebAppData', true);

        return http_build_query(
            $fields + ['hash' => hash_hmac('sha256', $dataCheckString, $secretKey)],
            '',
            '&',
            PHP_QUERY_RFC3986,
        );
    }

    /**
     * @return array<string, string>
     */
    private static function fields(): array
    {
        return [
            'auth_date' => (string)self::AUTH_DATE,
            'query_id' => 'f7b3a1c2',
            'start_param' => 'promo_42',
            'user' => '{"id":400,"first_name":"Иван","last_name":"","username":null,"language_code":"ru"}',
            'chat' => '{"id":500,"type":"DIALOG"}',
            'ip' => '10.0.0.1',
        ];
    }

    #[Test]
    public function validDataIsParsed(): void
    {
        $data = (new InitDataValidator(self::BOT_TOKEN))->validate(self::sign(self::fields()), now: self::AUTH_DATE + 60);

        $this->assertSame(self::AUTH_DATE, $data->authDate);
        $this->assertSame('f7b3a1c2', $data->queryId);
        $this->assertSame('promo_42', $data->startParam);
        $this->assertSame('10.0.0.1', $data->ip);
        $this->assertSame(400, $data->user?->id);
        $this->assertSame('Иван', $data->user?->firstName);
        $this->assertSame(500, $data->chat?->id);
        $this->assertSame(ChatType::Dialog, $data->chat?->type);
        $this->assertSame('10.0.0.1', $data->fields['ip']);
        $this->assertArrayNotHasKey('hash', $data->fields);
    }

    #[Test]
    public function optionalFieldsMayBeAbsent(): void
    {
        // A trailing `&` leaves an empty pair, which is skipped.
        $data = (new InitDataValidator(self::BOT_TOKEN))->validate(
            self::sign(['auth_date' => (string)self::AUTH_DATE]) . '&',
            now: self::AUTH_DATE,
        );

        $this->assertNull($data->queryId);
        $this->assertNull($data->startParam);
        $this->assertNull($data->ip);
        $this->assertNull($data->user);
        $this->assertNull($data->chat);
    }

    #[Test]
    public function changedFormatOfSignedDataDoesNotFailValidation(): void
    {
        $initData = self::sign([
            'auth_date' => (string)self::AUTH_DATE,
            'platform' => 'ios',
            'user' => '{"first_name":"Иван"}',
            'chat' => '{"id":500,"type":"GROUP"}',
        ]);

        $data = (new InitDataValidator(self::BOT_TOKEN))->validate($initData, now: self::AUTH_DATE);

        $this->assertNull($data->user);
        $this->assertSame(500, $data->chat?->id);
        $this->assertNull($data->chat?->type);
        $this->assertSame('ios', $data->fields['platform']);
    }

    #[Test]
    public function changedFieldIsRefused(): void
    {
        $initData = str_replace('promo_42', 'promo_43', self::sign(self::fields()));

        $this->expectException(SecurityException::class);
        $this->expectExceptionMessage('signature verification failed');
        (new InitDataValidator(self::BOT_TOKEN))->validate($initData, now: self::AUTH_DATE);
    }

    #[Test]
    public function anotherBotIsRefused(): void
    {
        $this->expectException(SecurityException::class);
        (new InitDataValidator('999:other-bot'))->validate(self::sign(self::fields()), now: self::AUTH_DATE);
    }

    #[Test]
    public function repeatedFieldInFrontOfValidDataIsRefused(): void
    {
        $forged = 'user=' . rawurlencode('{"id":1,"first_name":"Forged"}') . '&' . self::sign(self::fields());

        $this->expectException(SecurityException::class);
        $this->expectExceptionMessage('repeated field "user"');
        (new InitDataValidator(self::BOT_TOKEN))->validate($forged, now: self::AUTH_DATE);
    }

    #[Test]
    public function missingHashIsRefused(): void
    {
        $this->expectException(SecurityException::class);
        $this->expectExceptionMessage('no hash');
        (new InitDataValidator(self::BOT_TOKEN))->validate('auth_date=' . self::AUTH_DATE, now: self::AUTH_DATE);
    }

    #[Test]
    public function dataIsValidForAnHourByDefault(): void
    {
        $validator = new InitDataValidator(self::BOT_TOKEN);
        $initData = self::sign(self::fields());

        $this->assertSame(self::AUTH_DATE, $validator->validate($initData, now: self::AUTH_DATE + 86400)->authDate);

        $this->expectException(SecurityException::class);
        $this->expectExceptionMessage('expired');
        $validator->validate($initData, now: self::AUTH_DATE + 86401);
    }

    #[Test]
    public function expiryCheckCanBeTurnedOff(): void
    {
        $data = (new InitDataValidator(self::BOT_TOKEN))->validate(
            self::sign(self::fields()),
            maxAge: null,
            now: self::AUTH_DATE + 86400 * 365,
        );

        $this->assertSame(self::AUTH_DATE, $data->authDate);
    }

    #[Test]
    public function signedDataWithoutAuthDateIsRefused(): void
    {
        $this->expectException(SecurityException::class);
        $this->expectExceptionMessage('auth_date');
        (new InitDataValidator(self::BOT_TOKEN))->validate(self::sign(['query_id' => 'f7b3a1c2']));
    }

    #[Test]
    public function emptyTokenIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new InitDataValidator('');
    }
}
