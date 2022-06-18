<?php

declare(strict_types=1);

namespace Kuu\Task;

use Kuu\Arena\BotDuelFactory;
use Kuu\Arena\DuelFactory;
use Kuu\Loader;
use Kuu\NeptunePlayer;
use pocketmine\scheduler\Task;
use pocketmine\Server;

class NeptuneTask extends Task
{
    private static array $DuelTask = [];
    private static int $tick = 0;

    public function __construct()
    {
        Loader::setCoreTask($this);
    }

    public function onRun(): void
    {
        self::$tick++;
        if (self::$tick % 20 === 0) {
            Loader::getDeleteBlockHandler()->update();
            foreach (Server::getInstance()->getOnlinePlayers() as $player) {
                if ($player instanceof NeptunePlayer) {
                    $player->update();
                }
            }
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