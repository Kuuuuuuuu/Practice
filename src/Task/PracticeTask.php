<?php

declare(strict_types=1);

namespace Kuu\Task;

use Kuu\Arena\Duel\BotDuelFactory;
use Kuu\Arena\Duel\DuelFactory;
use Kuu\PracticeCore;
use pocketmine\scheduler\Task;

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