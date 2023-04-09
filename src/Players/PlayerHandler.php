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
            $test = [
                'kills' => $session->getKills(),
                'deaths' => $session->getDeaths(),
                'tag' => $session->getCustomTag(),
                'killStreak' => $session->getStreak(),
                'scoreboard' => $session->ScoreboardEnabled,
                'cps' => $session->CpsCounterEnabled,
                'cape' => $session->cape,
                'artifact' => $session->artifact,
                'purchasedArtifacts' => $session->purchasedArtifacts,
                'coins' => $session->coins,
                'lightningKill' => $session->isLightningKill,
            ];
            $parsed = yaml_parse_file($filePath);
            $parsed = array_merge($parsed, $test);
            $yaml = yaml_emit($parsed);
            file_put_contents($filePath, $yaml);
            PracticeCore::getSessionManager()->removeSession($player);
        }
    }
}
