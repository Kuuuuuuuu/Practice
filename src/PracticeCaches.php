<?php

namespace Nayuki;

use Nayuki\Game\Duel\Duel;
use Nayuki\Players\PlayerSession;

final class PracticeCaches
{
    /** @var array */
    public array $targetPlayer = [];
    /** @var array */
    public array $ClickData = [];
    /** @var bool */
    public bool $Restarting = false;
    /** @var PlayerSession[] */
    public array $PlayerSession = [];
    /** @var Duel[] */
    public array $RunningDuel = [];
}
