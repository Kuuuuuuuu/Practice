<?php

declare(strict_types=1);

namespace Nayuki\Game\Duel;

interface DuelStatus
{
    public const STARTING = 0;
    public const INGAME = 1;
    public const ENDING = 2;
}
