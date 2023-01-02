<?php

declare(strict_types=1);

namespace Nayuki\Game\Kits;

use pocketmine\utils\RegistryTrait;

use function assert;

final class KitRegistry
{
    use RegistryTrait;

    /**
     * @param string $name
     * @return Kit
     */
    public static function fromString(string $name): Kit
    {
        $kit = self::_registryFromString(strtolower($name));
        assert($kit instanceof Kit);
        return $kit;
    }

    /**
     * @return array<string, object>
     */
    public static function getKits(): array
    {
        return self::_registryGetAll();
    }

    /**
     * @return void
     */
    protected static function setup(): void
    {
        self::register(new Boxing('Boxing'));
        self::register(new NoDebuff('NoDebuff'));
    }

    /**
     * @param Kit $kit
     * @return void
     */
    public static function register(Kit $kit): void
    {
        self::_registryRegister($kit->getName(), $kit);
    }
}
