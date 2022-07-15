<?php

declare(strict_types=1);

namespace Kuu\Arena;

use Kuu\PracticePlayer;

abstract class DuelFactoryBase
{
    abstract public function onEnd(?PracticePlayer $playerLeft = null): void;

    abstract public function update(): void;
}