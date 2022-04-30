<?php

declare(strict_types=1);

namespace Kuu\Utils;

use JsonException;
use Kuu\Arena\SumoHandler;
use Kuu\Loader;
use pocketmine\utils\Config;
use RuntimeException;

class YamlManager
{

    public Config $config;

    public function __construct()
    {
        if (!is_dir(Loader::getInstance()->getDataFolder()) && !mkdir($concurrentDirectory = Loader::getInstance()->getDataFolder()) && !is_dir($concurrentDirectory)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
        }
        if (!is_dir(Loader::getInstance()->getDataFolder() . 'SumoArenas') && !mkdir($concurrentDirectory = Loader::getInstance()->getDataFolder() . 'SumoArenas') && !is_dir($concurrentDirectory)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
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