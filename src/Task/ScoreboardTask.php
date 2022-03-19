<?php /** @noinspection PhpMissingFieldTypeInspection */

declare(strict_types=1);

namespace Kohaku\Core\Task;

use Kohaku\Core\Loader;
use Kohaku\Core\Utils\ScoreboardUtils;
use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use pocketmine\Server;

class ScoreboardTask extends Task
{

    private Player $player;

    public function __construct(Player $player)
    {
        $this->player = $player;
    }

    public function onRun(): void
    {
        if ($this->player->isOnline()) {
            if ($this->player->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName("aqua")) {
                Loader::$score->remove($this->player);
                $this->getHandler()->cancel();
            } else if ($this->player->getWorld() === Server::getInstance()->getWorldManager()->getDefaultWorld()) {
                ScoreboardUtils::getInstance()->sb($this->player);
            } else if ($this->player->getWorld() !== Server::getInstance()->getWorldManager()->getDefaultWorld() and $this->player->getWorld() !== Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getParkourArena())) {
                ScoreboardUtils::getInstance()->sb2($this->player);
            } else if ($this->player->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getParkourArena())) {
                ScoreboardUtils::getInstance()->Parkour($this->player);
            }
        }
    }
}
