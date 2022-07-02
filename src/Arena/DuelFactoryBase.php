<?php

namespace Kuu\Arena;

use Kuu\PracticePlayer;

abstract class DuelFactoryBase
{
    abstract public function onEnd(?PracticePlayer $playerLeft = null): void;

    abstract public function update(): void;
}