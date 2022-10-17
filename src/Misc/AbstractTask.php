<?php

declare(strict_types=1);

namespace Kuu\Misc;

use Kuu\PracticeCore;
use pocketmine\scheduler\Task;

abstract class AbstractTask extends Task
{

    private int $currentTick = 0;
    private int $period;

    public function __construct(int $period = 1)
    {
        PracticeCore::getInstance()->getScheduler()->scheduleRepeatingTask($this, $period);
        $this->period = $period;
    }

    public function onRun(): void
    {
        $this->onUpdate($this->currentTick);
        $this->currentTick += $this->period;
    }

    abstract protected function onUpdate(int $tick): void;
}