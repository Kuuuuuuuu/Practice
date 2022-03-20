<?php

declare(strict_types=1);

namespace Kohaku\Core\Utils;

use JetBrains\PhpStorm\Pure;
use JsonException;
use Kohaku\Core\Arena\SumoHandler;
use Kohaku\Core\Loader;
use pocketmine\utils\Config;

class YamlDataProvider
{

    public Config $config;

    public function __construct()
    {
        if (!is_dir($this->getDataFolder())) {
            @mkdir($this->getDataFolder());
        }
        if (!is_dir($this->getDataFolder() . "SumoArena")) {
            @mkdir($this->getDataFolder() . "SumoArena");
        }
    }

    #[Pure] private function getDataFolder(): string
    {
        return Loader::getInstance()->getDataFolder();
    }

    public function loadArenas()
    {
        foreach (glob($this->getDataFolder() . "SumoArena" . DIRECTORY_SEPARATOR . "*.yml") as $arenaFile) {
            $config = new Config($arenaFile, Config::YAML);
            Loader::getInstance()->SumoArena[basename($arenaFile, ".yml")] = new SumoHandler(Loader::getInstance(), $config->getAll(false));
        }
    }

    /**
     * @throws JsonException
     */
    public function saveArenas()
    {
        foreach (Loader::getInstance()->SumoArena as $fileName => $arena) {
            $config = new Config($this->getDataFolder() . "SumoArena" . DIRECTORY_SEPARATOR . $fileName . ".yml", Config::YAML);
            $config->setAll($arena->data);
            $config->save();
        }
    }
}