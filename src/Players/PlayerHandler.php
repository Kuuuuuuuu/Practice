<?php

namespace Nayuki\Players;

use Nayuki\PracticeCore;
use pocketmine\player\Player;

final class PlayerHandler
{
    private string $path;

    public function __construct()
    {
        $this->path = PracticeCore::getPlayerDataPath();
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
        $session = PracticeCore::getSessionManager()->getSession($player);
        $name = $player->getName();
        $filePath = $this->path . "$name.yml";
        if ($session->loadedData) {
		 $test = ['kills' => $session->getKills(), 'deaths' => $session->getDeaths(), 'tag' => $session->getCustomTag(), 'killStreak' => $session->getStreak(), 'scoreboard' => $session->ScoreboardEnabled, 'cps' => $session->CpsCounterEnabled,];
		 $parsed = yaml_parse_file($filePath);
		 foreach ($test as $key => $value) {
		    $parsed[$key] = $value;
		 }
		 $yaml = yaml_emit($parsed);
		 file_put_contents($filePath, $yaml);
		 PracticeCore::getSessionManager()->removeSession($player);
	   }
    }
}
