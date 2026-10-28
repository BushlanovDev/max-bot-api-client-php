<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Models\WebApp;

use BushlanovDev\MaxMessengerBot\Models\AbstractModel;

/**
 * The user who opened a MAX mini app, from its init data.
 */
final readonly class InitDataUser extends AbstractModel
{
    /**
     * @param int $id User identifier.
     * @param string|null $firstName First name.
     * @param string|null $lastName Last name.
     * @param string|null $username Username.
     * @param string|null $languageCode Language of the MAX app interface (RFC 5646).
     * @param string|null $photoUrl URL of the profile photo.
     */
    public function __construct(
        public int $id,
        public ?string $firstName,
        public ?string $lastName,
        public ?string $username,
        public ?string $languageCode,
        public ?string $photoUrl,
    ) {
    }
}
