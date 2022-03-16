<?php /** @noinspection PhpMissingFieldTypeInspection */

declare(strict_types=1);

namespace Kohaku\Core\utils;

use JetBrains\PhpStorm\Pure;
use pocketmine\network\mcpe\protocol\RemoveObjectivePacket;
use pocketmine\network\mcpe\protocol\SetDisplayObjectivePacket;
use pocketmine\network\mcpe\protocol\SetScorePacket;
use pocketmine\network\mcpe\protocol\types\ScorePacketEntry;
use pocketmine\player\Player;

class Scoreboards
{

    public static $instance;
    public array $scoreboards = [];

    public function new(Player $player, string $objectiveName, string $displayName): void
    {
        if (isset($this->scoreboards[$player->getName()])) {
            $this->remove($player);
        }
        $pk = new SetDisplayObjectivePacket();
        $pk->displaySlot = "sidebar";
        $pk->objectiveName = $objectiveName;
        $pk->displayName = $displayName;
        $pk->criteriaName = "dummy";
        $pk->sortOrder = 0;
        $player->getNetworkSession()->sendDataPacket($pk);
        $this->scoreboards[$player->getName()] = $objectiveName;
    }

    public function remove(Player $player): void
    {
        $objectiveName = $this->getObjectiveName($player);
        $pk = new RemoveObjectivePacket();
        $pk->objectiveName = $objectiveName ?? "Unknown";
        $player->getNetworkSession()->sendDataPacket($pk);
        unset($this->scoreboards[$player->getName()]);
    }

    #[Pure] public function getObjectiveName(Player $player): ?string
    {
        return $this->scoreboards[$player->getName()] ?? null;
    }

    public function setLine(Player $player, int $score, string $message): void
    {
        $objectiveName = $this->getObjectiveName($player);
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
