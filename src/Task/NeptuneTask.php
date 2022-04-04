<?php

declare(strict_types=1);

namespace Kohaku\Core\Task;

use Kohaku\Core\NeptunePlayer;
use Kohaku\Core\Loader;
use pocketmine\scheduler\Task;
use pocketmine\Server;

class NeptuneTask extends Task
{

    private int $tick = 0;

    public function onRun(): void
    {
        $this->tick++;
        foreach (Server::getInstance()->getOnlinePlayers() as $player) {
            if ($this->tick % 20 === 0) {
                /* @var NeptunePlayer $player */
                $player->updatePlayer();
            }
            $player->updateCPS();
        }
        $this->updateServer();
    }

    private function updateServer(): void
    {
        if ($this->tick % 20 === 0) {
            Loader::getDeleteBlockHandler()->update();
            if (Loader::getInstance()->Restarted) {
                Loader::getInstance()->RestartTime--;
                if (Loader::getInstance()->RestartTime !== 0 and Loader::getInstance()->RestartTime % 5 === 0) {
                    Server::getInstance()->broadcastMessage(Loader::getPrefixCore() . "§cServer will restart in §e" . Loader::getInstance()->RestartTime . "§c seconds");
                } else if (Loader::getInstance()->RestartTime === 0) {
                    Loader::getInstance()->getServer()->shutdown();
                }
            }
        }
    }
}