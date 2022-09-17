<?php

namespace Kuu\Arena\Duel;

use Kuu\PracticePlayer;

abstract class DuelFactoryBase
{
    abstract public function onEnd(?PracticePlayer $playerLeft = null): void;

    abstract public function update(int $tick): void;
}