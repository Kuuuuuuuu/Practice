<?php

declare(strict_types=1);

namespace Kuu\Task;

use Kuu\PracticeCore;
use pocketmine\scheduler\Task;
use pocketmine\Server;

class OnceRestartTask extends Task
{

    private static int $time;

    public function __construct(int $time)
    {
        self::$time = $time;
        PracticeCore::getCaches()->Restarted = true;
    }

    public function onRun(): void
    {
        self::$time--;
        if (self::$time % 5 === 0) {
            Server::getInstance()->broadcastMessage(PracticeCore::getPrefixCore() . '§cServer will restart in §e' . self::$time . '§c seconds');
        }
        if (self::$time <= 1) {
            foreach (Server::getInstance()->getOnlinePlayers() as $player) {
                $player->kick('§cServer restarted');
            }
            PracticeCore::getInstance()->getServer()->shutdown();
        }
    }
}