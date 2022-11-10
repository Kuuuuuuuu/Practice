<?php

namespace Kuu\Arena\Duel;

use Kuu\PracticePlayer;

abstract class DuelFactoryBase
{
    /**
     * @param PracticePlayer|null $playerLeft
     * @return void
     */
    abstract public function onEnd(?PracticePlayer $playerLeft = null): void;

    /**
     * @param int $tick
     * @return void
     */
    abstract public function update(int $tick): void;
}