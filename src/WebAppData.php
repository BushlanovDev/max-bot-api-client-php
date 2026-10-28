<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot;

/**
 * Init data of a MAX mini app (window.WebApp.initData), after its signature was checked
 * by WebAppDataValidator. Field names follow the init data as MAX sends it.
 */
final readonly class WebAppData
{
    /**
     * @param int $authDate Unix time the data was signed.
     * @param string|null $queryId Query id, when the mini app was opened from a message button.
     * @param string|null $startParam The start parameter of the link (?startapp=...).
     * @param array<string, mixed>|null $user The user: id, first_name, last_name, username, language_code, photo_url.
     * @param array<string, mixed>|null $chat The chat the mini app was opened from: id, type.
     * @param array<string, string> $fields Every signed field, values decoded.
     */
    public function __construct(
        public int $authDate,
        public ?string $queryId,
        public ?string $startParam,
        public ?array $user,
        public ?array $chat,
        public array $fields,
    ) {
    }
}
