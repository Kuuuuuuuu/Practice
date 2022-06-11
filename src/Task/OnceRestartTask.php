<?php

namespace Kuu\Task;

use Kuu\Loader;
use pocketmine\scheduler\Task;
use pocketmine\Server;

class OnceRestartTask extends Task
{

    private int $time;

    public function __construct(int $time)
    {
        $this->time = $time;
        Loader::getInstance()->Restarted = true;
    }

    public function onRun(): void
    {
        $this->time--;
        if ($this->time % 5 === 0) {
            Server::getInstance()->broadcastMessage(Loader::getPrefixCore() . '§cServer will restart in §e' . $this->time . '§c seconds');
        } elseif ($this->time <= 1) {
            foreach (Server::getInstance()->getOnlinePlayers() as $player) {
                $player->kick('§cServer restarted');
            }
            Loader::getInstance()->getServer()->shutdown();
        }
    }
}