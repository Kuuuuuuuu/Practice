<?php

namespace Kuu\Players;

use Kuu\PracticeCore;
use Kuu\PracticePlayer;

class PlayerHandler
{
    private string $path;

    public function __construct()
    {
        $this->path = PracticeCore::getInstance()->getDataFolder() . 'player/';
        $this->initFolder();
    }

    private function initFolder(): void
    {
        if (!is_dir($this->path)) {
            mkdir($this->path);
        }
    }

    public function loadPlayerData(PracticePlayer $player): void
    {
        $name = $player->getName();
        $filePath = $this->path . "$name.yml";
        $task = new AsyncLoadPlayerData($player, $filePath);
        PracticeCore::getInstance()->getServer()->getAsyncPool()->submitTask($task);
    }

    public function savePlayerData(PracticePlayer $player): void
    {
        $name = $player->getName();
        $filePath = $this->path . "$name.yml";
        if ($player->hasLoadedData()) {
            $task = new AsyncSavePlayerData($player, $filePath);
            PracticeCore::getInstance()->getServer()->getAsyncPool()->submitTask($task);
        }
    }

    public function updatePlayerData(PracticePlayer $player, array $values = []): void
    {
        $name = $player->getName();
        $filePath = $this->path . "$name.yml";
        $task = new AsyncUpdatePlayerData($name, $filePath, $values);
        PracticeCore::getInstance()->getServer()->getAsyncPool()->submitTask($task);
    }
}