<?php

declare(strict_types=1);

namespace Nayuki\Utils\Scoreboard;

use Nayuki\PracticeCore;
use pocketmine\network\mcpe\protocol\RemoveObjectivePacket;
use pocketmine\network\mcpe\protocol\SetDisplayObjectivePacket;
use pocketmine\network\mcpe\protocol\SetScorePacket;
use pocketmine\network\mcpe\protocol\types\ScorePacketEntry;
use pocketmine\player\Player;

final class ScoreboardUtils
{
    /**
     * @param Player $player
     * @param string $objectiveName
     * @param string $displayName
     * @return void
     */
    public function new(Player $player, string $objectiveName, string $displayName): void
    {
        $session = PracticeCore::getSessionManager()->getSession($player);
        if ($session->Scoreboard !== null) {
            $this->remove($player);
        }
        $pk = new SetDisplayObjectivePacket();
        $pk->displaySlot = 'sidebar';
        $pk->objectiveName = $objectiveName;
        $pk->displayName = $displayName;
        $pk->criteriaName = 'dummy';
        $pk->sortOrder = 0;
        $player->getNetworkSession()->sendDataPacket($pk);
        $session->Scoreboard = $objectiveName;
    }

    /**
     * @param Player $player
     * @return void
     */
    public function remove(Player $player): void
    {
        $session = PracticeCore::getSessionManager()->getSession($player);
        if ($session->Scoreboard !== null) {
            $objectiveName = $session->Scoreboard;
            $pk = new RemoveObjectivePacket();
            $pk->objectiveName = $objectiveName;
            $player->getNetworkSession()->sendDataPacket($pk);
            $session->Scoreboard = null;
        }
    }

    /**
     * @param Player $player
     * @param int $score
     * @param string $message
     * @return void
     */
    public function setLine(Player $player, int $score, string $message): void
    {
        $session = PracticeCore::getSessionManager()->getSession($player);
        if ($session->Scoreboard !== null) {
            $objectiveName = $session->Scoreboard;
            $entry = new ScorePacketEntry();
            $entry->objectiveName = $objectiveName;
            $entry->type = $entry::TYPE_FAKE_PLAYER;
            $entry->customName = $message;
            $entry->score = $score;
            $entry->scoreboardId = $score;
            $pk = new SetScorePacket();
            $pk->type = $pk::TYPE_CHANGE;
            $pk->entries[] = $entry;
            $player->getNetworkSession()->sendDataPacket($pk);
        }
    }
}
