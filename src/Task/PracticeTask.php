<?php

declare(strict_types=1);

namespace Kuu\Task;

use Kuu\Arena\Duel\BotDuelFactory;
use Kuu\Arena\Duel\DuelFactory;
use Kuu\PracticeCore;
use Kuu\PracticePlayer;
use pocketmine\scheduler\Task;
use pocketmine\Server;

class PracticeTask extends Task
{
    private static array $DuelTask = [];
    private static int $tick = 0;

    public function __construct()
    {
        PracticeCore::setCoreTask($this);
    }

    public function onRun(): void
    {
        self::$tick++;
        if (self::$tick % 20 === 0) {
            foreach (Server::getInstance()->getOnlinePlayers() as $player) {
                if ($player instanceof PracticePlayer) {
                    $player->update();
                }
            }
            PracticeCore::getDeleteBlockHandler()->update();
            foreach (self::$DuelTask as $duel) {
                if ($duel instanceof DuelFactory || $duel instanceof BotDuelFactory) {
                    $duel->update();
                }
            }
        }
    }

    public function removeDuelTask(string $name): void
    {
        unset(self::$DuelTask[$name]);
    }

    public function addDuelTask(string $name, DuelFactory|BotDuelFactory $duel): void
    {
        self::$DuelTask[$name] = $duel;
    }
}