<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Models\WebApp;

use BushlanovDev\MaxMessengerBot\Models\AbstractModel;
use ReflectionException;
use TypeError;
use ValueError;

/**
 * Init data of a MAX mini app (window.WebApp.initData).
 *
 * fromArray() does not check the signature: take trusted data from InitDataValidator::validate().
 *
 * @see https://dev.max.ru/docs/webapps/bridge
 */
final readonly class InitData extends AbstractModel
{
    /**
     * @param int $authDate Unix time the data was signed.
     * @param string|null $queryId Unique id of the current session.
     * @param string|null $startParam The start parameter of the link (?startapp=...).
     * @param string|null $ip IP address of the user.
     * @param InitDataUser|null $user The user who opened the mini app.
     * @param InitDataChat|null $chat The chat the mini app was opened from.
     * @param array<string, string> $fields Every signed field as MAX sent it, values decoded.
     */
    public function __construct(
        public int $authDate,
        public ?string $queryId,
        public ?string $startParam,
        public ?string $ip,
        public ?InitDataUser $user,
        public ?InitDataChat $chat,
        public array $fields,
    ) {
    }

    /**
     * Builds init data from its fields as MAX sends them, where user and chat are JSON. A user or
     * chat that does not fit its model becomes null rather than an error, so a change in the format
     * does not break validation; the raw value stays in fields.
     *
     * @param array<string, mixed> $data
     *
     * @return static
     * @throws ReflectionException
     */
    public static function fromArray(array $data): static
    {
        return parent::fromArray([
            ...$data,
            'user' => self::nested(InitDataUser::class, $data['user'] ?? null),
            'chat' => self::nested(InitDataChat::class, $data['chat'] ?? null),
            'fields' => $data,
        ]);
    }

    /**
     * @param class-string<InitDataUser>|class-string<InitDataChat> $class
     *
     * @throws ReflectionException
     */
    private static function nested(string $class, mixed $value): InitDataUser|InitDataChat|null
    {
        if (is_string($value)) {
            $value = json_decode($value, true);
        }

        if (!is_array($value)) {
            return null;
        }

        try {
            // Both models hold scalars only. A value MAX turned into an object is dropped: casting
            // it to string would raise a warning, and Laravel turns warnings into exceptions.
            return $class::fromArray(array_filter($value, is_scalar(...)));
        } catch (TypeError|ValueError) {
            // A required field, the id, is missing.
            return null;
        }
    }
}
