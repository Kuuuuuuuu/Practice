<?php

declare(strict_types=1);

namespace Kohaku\Core\Utils;

use JsonException;
use Kohaku\Core\Arena\SumoHandler;
use Kohaku\Core\Loader;
use pocketmine\utils\Config;

class YamlDataProvider
{

    public Config $config;

    public function __construct()
    {
        $this->loadArenas();
        if (!is_dir(Loader::getInstance()->getDataFolder())) {
            @mkdir(Loader::getInstance()->getDataFolder());
        }
        if (!is_dir(Loader::getInstance()->getDataFolder() . "SumoArenas")) {
            @mkdir(Loader::getInstance()->getDataFolder() . "SumoArenas");
        }
    }

    public function loadArenas()
    {
        foreach (glob(Loader::getInstance()->getDataFolder() . "SumoArenas" . DIRECTORY_SEPARATOR . "*.yml") as $arenaFile) {
            $config = new Config($arenaFile, Config::YAML);
            Loader::getInstance()->SumoArenas[basename($arenaFile, ".yml")] = new SumoHandler(Loader::getInstance(), $config->getAll(false));
        }
    }

    /**
     * @throws JsonException
     */
    public function saveArenas()
    {
        foreach (Loader::getInstance()->SumoArenas as $fileName => $arena) {
            $config = new Config(Loader::getInstance()->getDataFolder() . "SumoArenas" . DIRECTORY_SEPARATOR . $fileName . ".yml", Config::YAML);
            $config->setAll($arena->data);
            $config->save();
        }
    }
}