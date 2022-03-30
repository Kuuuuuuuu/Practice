<?php

declare(strict_types=1);

namespace Kohaku\Core\Task;

use Kohaku\Core\HorizonPlayer;
use Kohaku\Core\Loader;
use Kohaku\Core\Utils\DeleteBlocksHandler;
use pocketmine\scheduler\Task;
use pocketmine\Server;

class HorizonTask extends Task
{

    private int $tick = 0;

    public function onRun(): void
    {
        $this->tick++;
        if ($this->tick % 20 === 0) {
            DeleteBlocksHandler::getInstance()->update();
            if (Loader::getInstance()->Restarted) {
                $this->RestartServer();
            }
            foreach (Server::getInstance()->getOnlinePlayers() as $player) {
                /* @var HorizonPlayer $player */
                $player->updatePlayer();
            }
        }
        foreach (Server::getInstance()->getOnlinePlayers() as $player) {
            switch ($player->getWorld()) {
                case Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getParkourArena()):
                    $player->parkourTimer();
                    break;
                case Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getBoxingArena()):
                    if ($this->tick % 3 === 0) {
                        $player->boxingTip();
                    }
                    break;
                default:
                    if ($this->tick % 3 === 0) {
                        $player->sendTip("§bCPS: §f" . Loader::$cps->getClicks($player));
                    }
                    break;
            }
        }
    }

    private function RestartServer()
    {
        Loader::getInstance()->RestartTime--;
        if (Loader::getInstance()->RestartTime <= 15) {
            Server::getInstance()->broadcastMessage(Loader::getPrefixCore() . "§cServer will restart in §e" . Loader::getInstance()->RestartTime . "§c seconds");
        }
        if (Loader::getInstance()->RestartTime <= 1) {
            Loader::getInstance()->getServer()->shutdown();
        }
    }
}