<?php

namespace Nayuki;

use Nayuki\Game\Duel\Duel;

final class PracticeCaches
{
    /** @var array */
    public array $targetPlayer = [];
    /** @var bool */
    public bool $Restarting = false;
    /** @var Duel[] */
    public array $RunningDuel = [];
}
