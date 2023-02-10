<?php

namespace Nayuki\Task;

use Nayuki\Misc\AbstractTask;
use Nayuki\PracticeCore;
use pocketmine\Server;

class OnceRestartTask extends AbstractTask
{
    /** @var int */
    private int $time;

    public function __construct(int $time)
    {
        parent::__construct(20);
        $this->time = $time;
        PracticeCore::getCaches()->Restarting = true;
    }

    /**
     * @param int $tick
     * @return void
     */
    public function onUpdate(int $tick): void
    {
        $this->time--;
        if ($this->time <= 1) {
            foreach (Server::getInstance()->getOnlinePlayers() as $player) {
                $player->kick('§cServer restarted');
            }
            PracticeCore::getInstance()->getServer()->shutdown();
        } elseif ($this->time % 5 === 0) {
            Server::getInstance()->broadcastMessage(PracticeCore::getPrefixCore() . '§cServer will restart in §e' . $this->time . '§c seconds');
        }
    }
}
