<?php

declare(strict_types=1);

namespace Kohaku\Task;

use Kohaku\Arena\DuelFactory;
use Kohaku\Loader;
use Kohaku\NeptunePlayer;
use pocketmine\scheduler\Task;
use pocketmine\Server;

class NeptuneTask extends Task
{
    public array $DuelTask = [];
    private int $tick = 0;

    public function __Construct()
    {
        Loader::setCoreTask($this);
    }

    public function onRun(): void
    {
        $this->tick++;
        foreach (Server::getInstance()->getOnlinePlayers() as $player) {
            if ($player instanceof NeptunePlayer) {
                $player->update();
            }
        }
        $this->updateServer();
    }

    private function updateServer(): void
    {
        if ($this->tick % 20 === 0) {
            Loader::getDeleteBlockHandler()->update();
            foreach ($this->DuelTask as $duel) {
                if ($duel instanceof DuelFactory) {
                    $duel->update();
                }
            }
            if (Loader::getInstance()->Restarted) {
                Loader::getInstance()->RestartTime--;
                if (Loader::getInstance()->RestartTime !== 0 and Loader::getInstance()->RestartTime % 5 === 0) {
                    Server::getInstance()->broadcastMessage(Loader::getPrefixCore() . '§cServer will restart in §e' . Loader::getInstance()->RestartTime . '§c seconds');
                } elseif (Loader::getInstance()->RestartTime === 0) {
                    Loader::getInstance()->getServer()->shutdown();
                }
            }
        }
    }
}