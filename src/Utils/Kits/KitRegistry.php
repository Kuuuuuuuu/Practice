<?php
declare(strict_types=1);

namespace Kohaku\Utils\Kits;

use pocketmine\utils\RegistryTrait;

class KitRegistry
{
    use RegistryTrait;

    public static function fromString(string $name): KitManager
    {
        /** @var KitManager $kit */
        $kit = self::_registryFromString(strtolower($name));
        return $kit;
    }

    public static function getKits(): array
    {
        $kits = self::_registryGetAll();
        return $kits;
    }

    protected static function setup(): void
    {
        self::register(new BuildUHC("BuildUHC"));
        self::register(new Classic("Classic"));
        self::register(new Fist("Fist"));
        self::register(new NoDebuff("NoDebuff"));
        self::register(new SG("SG"));
    }

    public static function register(KitManager $kit): void
    {
        self::_registryRegister($kit->getName(), $kit);
    }
}