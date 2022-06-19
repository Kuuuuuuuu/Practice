<?php

namespace Kuu\Task;

use Kuu\PracticeCore;
use pocketmine\scheduler\Task;
use pocketmine\Server;

class OnceRestartTask extends Task
{

    private int $time;

    public function __construct(int $time)
    {
        $this->time = $time;
        PracticeCore::getInstance()->Restarted = true;
    }

    public function onRun(): void
    {
        $this->time--;
        if ($this->time % 5 === 0) {
            Server::getInstance()->broadcastMessage(PracticeCore::getPrefixCore() . '§cServer will restart in §e' . $this->time . '§c seconds');
        }
        if ($this->time <= 1) {
            foreach (Server::getInstance()->getOnlinePlayers() as $player) {
                $player->kick('§cServer restarted');
            }
            PracticeCore::getInstance()->getServer()->shutdown();
        }
    }
}