<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\WebApp;

use BushlanovDev\MaxMessengerBot\Exceptions\SecurityException;
use BushlanovDev\MaxMessengerBot\Models\WebApp\InitData;
use InvalidArgumentException;
use ReflectionException;

/**
 * Checks init data a MAX mini app sends to your server (window.WebApp.initData): the
 * signature by the bot token, then the age of the data.
 *
 * @see https://dev.max.ru/docs/webapps/validation
 */
final readonly class InitDataValidator
{
    private const string HMAC_KEY = 'WebAppData';

    /**
     * @param string $botToken Your bot's access token from @MasterBot.
     *
     * @throws InvalidArgumentException
     */
    public function __construct(
        private string $botToken,
    ) {
        if ($botToken === '') {
            throw new InvalidArgumentException('Bot token cannot be empty.');
        }
    }

    /**
     * Validates init data and returns it parsed. Only data that cannot be trusted is refused:
     * a change in the format of the signed data does not fail validation, see InitData::fromArray().
     *
     * @param string $initData Raw init data, as the mini app read it from window.WebApp.initData.
     * @param int|null $maxAge Seconds the data stays valid after auth_date; null skips the check.
     *                         A mini app open for long keeps sending the same data, so a day is a fair default.
     * @param int|null $now Current Unix time; for tests.
     *
     * @return InitData
     * @throws SecurityException
     * @throws ReflectionException
     */
    public function validate(string $initData, ?int $maxAge = 86400, ?int $now = null): InitData
    {
        $fields = $this->parseFields($initData);

        $hash = $fields['hash'] ?? '';
        if ($hash === '') {
            throw new SecurityException('Init data has no hash.');
        }
        unset($fields['hash']);

        if (!hash_equals($this->calculateHash($fields), strtolower($hash))) {
            throw new SecurityException('Init data signature verification failed.');
        }

        $authDate = filter_var($fields['auth_date'] ?? null, FILTER_VALIDATE_INT);
        if ($authDate === false) {
            throw new SecurityException('Init data has no valid auth_date.');
        }
        if ($maxAge !== null && ($now ?? time()) - $authDate > $maxAge) {
            throw new SecurityException('Init data has expired.');
        }

        return InitData::fromArray($fields);
    }

    /**
     * Splits the query string by hand: parse_str() turns `hash[]=` into an array and rewrites
     * dots and spaces in keys, while the signature covers the keys as sent. A repeated key is
     * refused: the signature would cover one value while a caller reading the raw string with
     * another parser could take the other.
     *
     * @return array<string, string>
     * @throws SecurityException
     */
    private function parseFields(string $initData): array
    {
        $fields = [];
        foreach (explode('&', $initData) as $pair) {
            if ($pair === '') {
                continue;
            }
            [$key, $value] = array_pad(explode('=', $pair, 2), 2, '');
            $key = urldecode($key);
            if (array_key_exists($key, $fields)) {
                throw new SecurityException(sprintf('Init data has a repeated field "%s".', $key));
            }
            $fields[$key] = urldecode($value);
        }

        return $fields;
    }

    /**
     * HMAC-SHA256 of the sorted `key=value` lines, keyed with HMAC-SHA256("WebAppData", bot token).
     *
     * @param array<string, string> $fields
     */
    private function calculateHash(array $fields): string
    {
        ksort($fields, SORT_STRING);

        $lines = [];
        foreach ($fields as $key => $value) {
            $lines[] = $key . '=' . $value;
        }

        $secretKey = hash_hmac('sha256', $this->botToken, self::HMAC_KEY, true);

        return hash_hmac('sha256', implode("\n", $lines), $secretKey);
    }
}
