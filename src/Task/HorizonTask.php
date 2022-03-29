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
            $this->RestartServer();
            foreach (Server::getInstance()->getOnlinePlayers() as $player) {
                /* @var HorizonPlayer $player */
                $player->updatePlayer();
            }
        }
        foreach (Server::getInstance()->getOnlinePlayers() as $player) {
            if ($player->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getParkourArena())) {
                $player->parkourTimer();
            } else {
                if (isset(Loader::getInstance()->TimerTask[$player->getName()])) {
                    unset(Loader::getInstance()->TimerTask[$player->getName()]);
                } else if (isset(Loader::getInstance()->TimerData[$player->getName()])) {
                    unset(Loader::getInstance()->TimerData[$player->getName()]);
                }
            }
            if ($player->getWorld() !== Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getParkourArena()) and $player->getWorld() !== Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getBoxingArena())) {
                $player->sendTip("§bCPS: §f" . Loader::$cps->getClicks($player));
            }
            if ($this->tick % 5 === 0) {
                if ($player->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getBoxingArena())) {
                    $player->boxingTip();
                }
            }
        }
    }

    private function RestartServer()
    {
        if (Loader::getInstance()->Restarted) {
            Loader::getInstance()->RestartTime--;
            if (Loader::getInstance()->RestartTime <= 15) {
                Server::getInstance()->broadcastMessage(Loader::getPrefixCore() . "§cServer will restart in §e" . Loader::getInstance()->RestartTime . "§c seconds");
            }
            if (Loader::getInstance()->RestartTime <= 0) {
                Loader::getInstance()->getServer()->shutdown();
            }
        }
    }
}