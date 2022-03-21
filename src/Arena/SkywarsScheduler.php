<?php

declare(strict_types=1);

namespace Kohaku\Core\Arena;

use Kohaku\Core\Loader;
use Kohaku\Core\Utils\ArenaUtils;
use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use pocketmine\Server;

class SkywarsScheduler extends Task
{

    public int $startTime = 40;
    public int|float $gameTime = 20 * 60;
    protected SkywarsHandler $plugin;

    public function __construct(SkywarsHandler $plugin)
    {
        $this->plugin = $plugin;
    }

    public function onRun(): void
    {
        if ($this->plugin->setup) return;
        if ($this->plugin->phase === SkywarsHandler::PHASE_LOBBY) {
            if (count($this->plugin->players) >= 2) {
                $this->startTime--;
                if ($this->startTime == 0) {
                    $this->plugin->startGame();
                    foreach ($this->plugin->players as $player) {
                        $player->sendTitle("§b" . $this->startTime, "", 1, 1, 1);
                        ArenaUtils::getInstance()->playSound("random.click", $player);
                    }
                } else {
                    foreach ($this->plugin->players as $player) {
                        ArenaUtils::getInstance()->playSound("random.anvil_use", $player);
                    }
                }
            } else {
                $this->startTime = 40;
            }
        }
        if ($this->plugin->phase === SkywarsHandler::PHASE_GAME) {
            foreach ($this->plugin->players as $player) {
                if ($this->plugin->inGame($player)) {
                    if ($player->getPosition()->getY() <= 20) {
                        $this->plugin->disconnectPlayer($player);
                    } else if ($player->getWorld() !== $this->plugin->level) {
                        $this->plugin->disconnectPlayer($player);
                    }
                }
            }
            switch ($this->gameTime) {
                case 15 * 60:
                    Server::getInstance()->broadcastMessage(Loader::getPrefixCore() . "All chests will be refilled in 5 min.");
                    break;
                case 11 * 60:
                    Server::getInstance()->broadcastMessage(Loader::getPrefixCore() . "All chest will be refilled in 1 min.");
                    break;
                case 10 * 60:
                    Server::getInstance()->broadcastMessage(Loader::getPrefixCore() . "All chests are refilled.");
                    break;
            }
            if ($this->plugin->checkEnd()) $this->plugin->startRestart();
            $this->gameTime--;
        }
        if ($this->plugin->phase === SkywarsHandler::PHASE_RESTART) {
            foreach ($this->plugin->players as $player) {
                /* @var $player Player */
                $player->teleport($this->plugin->plugin->getServer()->getWorldManager()->getDefaultWorld()->getSpawnLocation());
                $player->getInventory()->clearAll();
                $player->getArmorInventory()->clearAll();
                $player->getCursorInventory()->clearAll();
                $player->getHungerManager()->setFood(20);
                $player->setHealth(20);
                $player->setGamemode($this->plugin->plugin->getServer()->getGamemode());
            }
            $this->plugin->loadArena(true);
            $this->reloadTimer();
        }
    }

    public function reloadTimer()
    {
        $this->startTime = 30;
        $this->gameTime = 20 * 60;
    }
}
