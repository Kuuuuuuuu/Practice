<?php

namespace Kuu\Arena;

abstract class DuelFactoryBase
{
    abstract public function onEnd($playerLeft = null): void;

    abstract public function update(): void;
}