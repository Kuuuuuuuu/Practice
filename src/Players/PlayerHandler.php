<?php

namespace Kuu\Players;

use Kuu\PracticeCore;
use pocketmine\player\Player;

class PlayerHandler
{
    private string $path;

    public function __construct()
    {
        $this->path = PracticeCore::getInstance()->getDataFolder() . 'player/';
        $this->initFolder();
    }

    /**
     * @return void
     */
    private function initFolder(): void
    {
        if (!is_dir($this->path)) {
            mkdir($this->path);
        }
    }

    /**
     * @param Player $player
     * @return void
     */
    public function loadPlayerData(Player $player): void
    {
        $name = $player->getName();
        $filePath = $this->path . "$name.yml";
        $task = new AsyncLoadPlayerData($player, $filePath);
        PracticeCore::getInstance()->getServer()->getAsyncPool()->submitTask($task);
    }

    /**
     * @param Player $player
     * @return void
     */
    public function savePlayerData(Player $player): void
    {
        $session = PracticeCore::getPlayerSession()::getSession($player);
        $name = $player->getName();
        $filePath = $this->path . "$name.yml";
        if ($session->loadedData) {
            $task = new AsyncSavePlayerData($player, $filePath);
            PracticeCore::getInstance()->getServer()->getAsyncPool()->submitTask($task);
        }
    }
}
