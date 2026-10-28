<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Models\WebApp;

use BushlanovDev\MaxMessengerBot\Enums\ChatType;
use BushlanovDev\MaxMessengerBot\Models\AbstractModel;
use ReflectionException;

/**
 * The chat a MAX mini app was opened from, from its init data.
 */
final readonly class InitDataChat extends AbstractModel
{
    /**
     * @param int $id Chat identifier.
     * @param ChatType|null $type Chat type, null when MAX sends a type this library does not know.
     */
    public function __construct(
        public int $id,
        public ?ChatType $type,
    ) {
    }

    /**
     * MAX sends the type in upper case (DIALOG) while ChatType holds it in lower case. An unknown
     * type becomes null instead of an error, so a new chat type does not break validation.
     *
     * @param array<string, mixed> $data
     *
     * @return static
     * @throws ReflectionException
     */
    public static function fromArray(array $data): static
    {
        $type = $data['type'] ?? null;
        $data['type'] = is_string($type) ? ChatType::tryFrom(strtolower($type)) : null;

        return parent::fromArray($data);
    }
}
