<?php
declare(strict_types=1);

namespace Kuu\Utils\Kits;

use pocketmine\utils\RegistryTrait;

class KitRegistry
{
    use RegistryTrait;

    /**
     * @param string $name
     * @return KitManager
     */
    public static function fromString(string $name): KitManager
    {
        $kit = self::_registryFromString(strtolower($name));
        assert($kit instanceof KitManager);
        return $kit;
    }

    /**
     * @return array
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
        self::register(new BuildUHC('BuildUHC'));
        self::register(new Classic('Classic'));
        self::register(new Fist('Fist'));
        self::register(new NoDebuff('NoDebuff'));
        self::register(new SG('SG'));
        self::register(new Sumo('Sumo'));
    }

    /**
     * @param KitManager $kit
     * @return void
     */
    public static function register(KitManager $kit): void
    {
        self::_registryRegister($kit->getName(), $kit);
    }
}