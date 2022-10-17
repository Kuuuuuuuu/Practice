<?php

declare(strict_types=1);

namespace Kuu\Task;

use Kuu\Arena\Duel\BotDuelFactory;
use Kuu\Arena\Duel\DuelFactory;
use Kuu\Misc\AbstractTask;
use Kuu\PracticeCore;

class PracticeTask extends AbstractTask
{
    private static array $DuelTask = [];

    public function __construct()
    {
        parent::__construct();
        PracticeCore::setCoreTask($this);
    var_dump("hello");
    }

    public function onUpdate(int $tick): void
    {
        var_dump("hello2");
        foreach (self::$DuelTask as $duel) {
            if ($duel instanceof DuelFactory || $duel instanceof BotDuelFactory) {
                $duel->update($tick);
            }
        }
        if ($tick % 20 === 0) {
            PracticeCore::getDeleteBlockHandler()->update();
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