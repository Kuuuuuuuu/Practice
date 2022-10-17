<?php

namespace Kuu\Task;

use Kuu\Misc\AbstractTask;
use Kuu\PracticeCore;
use pocketmine\Server;

class OnceRestartTask extends AbstractTask
{
    private int $time;

    public function __construct(int $time)
    {
        parent::__construct(20);
        $this->time = $time;
        PracticeCore::getCaches()->Restarting = true;
    }

    public function onUpdate(int $tick): void
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