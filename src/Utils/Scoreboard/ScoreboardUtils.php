<?php /** @noinspection PhpMissingFieldTypeInspection */

declare(strict_types=1);

namespace Kuu\Utils\Scoreboard;

use pocketmine\network\mcpe\protocol\RemoveObjectivePacket;
use pocketmine\network\mcpe\protocol\SetDisplayObjectivePacket;
use pocketmine\network\mcpe\protocol\SetScorePacket;
use pocketmine\network\mcpe\protocol\types\ScorePacketEntry;
use pocketmine\player\Player;

class ScoreboardUtils
{
    /** @var array */
    private array $scoreboards = [];

    /**
     * @param Player $player
     * @param string $objectiveName
     * @param string $displayName
     * @return void
     */
    public function new(Player $player, string $objectiveName, string $displayName): void
    {
        if (isset($this->scoreboards[$player->getName()])) {
            $this->remove($player);
        }
        $pk = new SetDisplayObjectivePacket();
        $pk->displaySlot = 'sidebar';
        $pk->objectiveName = $objectiveName;
        $pk->displayName = $displayName;
        $pk->criteriaName = 'dummy';
        $pk->sortOrder = 0;
        $player->getNetworkSession()->sendDataPacket($pk);
        $this->scoreboards[$player->getName()] = $objectiveName;
    }

    /**
     * @param Player $player
     * @return void
     */
    public function remove(Player $player): void
    {
        $objectiveName = $this->scoreboards[$player->getName()] ?? null;
        $pk = new RemoveObjectivePacket();
        $pk->objectiveName = $objectiveName ?? 'Unknown';
        $player->getNetworkSession()->sendDataPacket($pk);
        unset($this->scoreboards[$player->getName()]);
    }

    /**
     * @param Player $player
     * @param int $score
     * @param string $message
     * @return void
     */
    public function setLine(Player $player, int $score, string $message): void
    {
        $objectiveName = $this->scoreboards[$player->getName()] ?? null;
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
