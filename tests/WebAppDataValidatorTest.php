<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Tests;

use BushlanovDev\MaxMessengerBot\Exceptions\SecurityException;
use BushlanovDev\MaxMessengerBot\WebAppData;
use BushlanovDev\MaxMessengerBot\WebAppDataValidator;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(WebAppDataValidator::class)]
#[UsesClass(WebAppData::class)]
final class WebAppDataValidatorTest extends TestCase
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
        $data = (new WebAppDataValidator(self::BOT_TOKEN))->validate(self::sign(self::fields()), now: self::AUTH_DATE + 60);

        $this->assertSame(self::AUTH_DATE, $data->authDate);
        $this->assertSame('f7b3a1c2', $data->queryId);
        $this->assertSame('promo_42', $data->startParam);
        $this->assertSame(400, $data->user['id'] ?? null);
        $this->assertSame('Иван', $data->user['first_name'] ?? null);
        $this->assertSame(['id' => 500, 'type' => 'DIALOG'], $data->chat);
        $this->assertSame('10.0.0.1', $data->fields['ip']);
        $this->assertArrayNotHasKey('hash', $data->fields);
    }

    #[Test]
    public function optionalFieldsMayBeAbsent(): void
    {
        // A trailing `&` leaves an empty pair, which is skipped.
        $data = (new WebAppDataValidator(self::BOT_TOKEN))->validate(
            self::sign(['auth_date' => (string)self::AUTH_DATE]) . '&',
            now: self::AUTH_DATE,
        );

        $this->assertNull($data->queryId);
        $this->assertNull($data->startParam);
        $this->assertNull($data->user);
        $this->assertNull($data->chat);
    }

    #[Test]
    public function changedFieldIsRefused(): void
    {
        $initData = str_replace('promo_42', 'promo_43', self::sign(self::fields()));

        $this->expectException(SecurityException::class);
        $this->expectExceptionMessage('signature verification failed');
        (new WebAppDataValidator(self::BOT_TOKEN))->validate($initData, now: self::AUTH_DATE);
    }

    #[Test]
    public function anotherBotIsRefused(): void
    {
        $this->expectException(SecurityException::class);
        (new WebAppDataValidator('999:other-bot'))->validate(self::sign(self::fields()), now: self::AUTH_DATE);
    }

    #[Test]
    public function repeatedFieldInFrontOfValidDataIsRefused(): void
    {
        $forged = 'user=' . rawurlencode('{"id":1,"first_name":"Forged"}') . '&' . self::sign(self::fields());

        $this->expectException(SecurityException::class);
        $this->expectExceptionMessage('repeated field "user"');
        (new WebAppDataValidator(self::BOT_TOKEN))->validate($forged, now: self::AUTH_DATE);
    }

    #[Test]
    public function missingHashIsRefused(): void
    {
        $this->expectException(SecurityException::class);
        $this->expectExceptionMessage('no hash');
        (new WebAppDataValidator(self::BOT_TOKEN))->validate('auth_date=' . self::AUTH_DATE, now: self::AUTH_DATE);
    }

    #[Test]
    public function expiredDataIsRefusedUnlessTheCheckIsOff(): void
    {
        $validator = new WebAppDataValidator(self::BOT_TOKEN);
        $initData = self::sign(self::fields());
        $dayLater = self::AUTH_DATE + 86401;

        $this->assertSame(self::AUTH_DATE, $validator->validate($initData, maxAge: null, now: $dayLater)->authDate);

        $this->expectException(SecurityException::class);
        $this->expectExceptionMessage('expired');
        $validator->validate($initData, now: $dayLater);
    }

    #[Test]
    public function signedDataWithoutAuthDateIsRefused(): void
    {
        $this->expectException(SecurityException::class);
        $this->expectExceptionMessage('auth_date');
        (new WebAppDataValidator(self::BOT_TOKEN))->validate(self::sign(['query_id' => 'f7b3a1c2']));
    }

    #[Test]
    public function emptyTokenIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new WebAppDataValidator('');
    }
}
