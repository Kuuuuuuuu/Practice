<?php

namespace Nayuki;

use Nayuki\Duel\Duel;
use Nayuki\Players\PlayerSession;
use pocketmine\player\Player;

class PracticeCaches
{
    /** @var array */
    public array $targetPlayer = [];
    /** @var array */
    public array $ClickData = [];
    /** @var bool */
    public bool $Restarting = false;
    /** @var array<PlayerSession> */
    public array $PlayerSession = [];
    /** @var array<Duel> */
    public array $RunningDuel = [];
}
