<?php

declare(strict_types=1);

namespace Kohaku\Utils;

use JsonException;
use Kohaku\Arena\SumoHandler;
use Kohaku\Loader;
use pocketmine\utils\Config;

class YamlManager
{

    public Config $config;

    public function __construct()
    {
        if (!is_dir(Loader::getInstance()->getDataFolder())) {
            @mkdir(Loader::getInstance()->getDataFolder());
        }
        if (!is_dir(Loader::getInstance()->getDataFolder() . 'SumoArenas')) {
            @mkdir(Loader::getInstance()->getDataFolder() . 'SumoArenas');
        }
    }

    public function loadArenas(): void
    {
        foreach (glob(Loader::getInstance()->getDataFolder() . 'SumoArenas' . DIRECTORY_SEPARATOR . '*.yml') as $arenaFile) {
            $config = new Config($arenaFile, Config::YAML);
            Loader::getInstance()->SumoArenas[basename($arenaFile, '.yml')] = new SumoHandler($config->getAll());
        }
    }

    /**
     * @throws JsonException
     */
    public function saveArenas(): void
    {
        foreach (Loader::getInstance()->SumoArenas as $fileName => $arena) {
            $config = new Config(Loader::getInstance()->getDataFolder() . 'SumoArenas' . DIRECTORY_SEPARATOR . $fileName . '.yml', Config::YAML);
            $config->setAll($arena->data);
            $config->save();
        }
    }
}