<?php

namespace Nayuki\Players;

use Exception;
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
        $uuid = $player->getUniqueId()->toString();
        $filePath = $this->path . "$uuid.yml";
        $task = new AsyncLoadPlayerData($player, $filePath);
        PracticeCore::getInstance()->getServer()->getAsyncPool()->submitTask($task);
    }

    /**
     * @param Player $player
     * @return void
     * This is not a proper way to do it
     */
    public function savePlayerData(Player $player): void
    {
        $session = PracticeCore::getSessionManager()->getSession($player);
        $uuid = $player->getUniqueId()->toString();
        $filePath = $this->path . "$uuid.yml";

        if ($session->isDueling || $session->isCombat) {
            $player->kill();
        }

        if ($session->loadedData) {
            $data = [
                'name' => $player->getName(),
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

            try {
                yaml_emit_file($filePath, $data);
                PracticeCore::getSessionManager()->removeSession($player);
            } catch (Exception) {
            }
        }
    }
}
