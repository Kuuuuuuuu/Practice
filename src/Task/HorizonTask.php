<?php

declare(strict_types=1);

namespace Kohaku\Core\Task;

use Kohaku\Core\HorizonPlayer;
use Kohaku\Core\Loader;
use pocketmine\scheduler\Task;
use pocketmine\Server;

class HorizonTask extends Task
{

    private int $tick = 0;

    public function onRun(): void
    {
        $this->tick++;
        if ($this->tick % 20 === 0) {
            Loader::getDeleteBlockHandler()->update();
            if (Loader::getInstance()->Restarted) {
                $this->RestartServer();
            }
            foreach (Server::getInstance()->getOnlinePlayers() as $player) {
                /* @var HorizonPlayer $player */
                $player->updatePlayer();
            }
        }
        if ($this->tick % 60 === 0) {
            $this->clearChunk();
        }
        foreach (Server::getInstance()->getOnlinePlayers() as $player) {
            switch ($player->getWorld()) {
                case Server::getInstance()->getWorldManager()->getWorldByName(Loader::getArenaFactory()->getParkourArena()):
                    $player->parkourTimer();
                    break;
                case Server::getInstance()->getWorldManager()->getWorldByName(Loader::getArenaFactory()->getBoxingArena()):
                    $player->boxingTip();
                    break;
                default:
                    $player->sendTip("§bCPS: §f" . Loader::getClickHandler()->getClicks($player));
                    break;
            }
        }
    }

    private function RestartServer()
    {
        Loader::getInstance()->RestartTime--;
        if (Loader::getInstance()->RestartTime !== 0 and Loader::getInstance()->RestartTime % 5 === 0) {
            Server::getInstance()->broadcastMessage(Loader::getPrefixCore() . "§cServer will restart in §e" . Loader::getInstance()->RestartTime . "§c seconds");
        } else if (Loader::getInstance()->RestartTime === 0) {
            Loader::getInstance()->getServer()->shutdown();
        }
    }

    private function clearChunk()
    {
        foreach (Server::getInstance()->getWorldManager()->getWorlds() as $world) {
            foreach ($world->getLoadedChunks() as $chunk) {
                $chunk->clearTerrainDirtyFlags();
                $chunk->collectGarbage();
            }
        }
    }
}