<?php

declare(strict_types=1);

namespace Nayuki\Misc;

use Nayuki\PracticeCore;
use pocketmine\scheduler\Task;

abstract class AbstractTask extends Task
{
    /** @var int */
    private int $currentTick = 0;
    /** @var int */
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

    /**
     * @param int $tick
     * @return void
     */
    abstract protected function onUpdate(int $tick): void;
}
