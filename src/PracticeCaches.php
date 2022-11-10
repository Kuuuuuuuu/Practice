<?php

namespace Kuu;

class PracticeCaches
{
    /** @var array */
    public array $DuelMatch = [];
    /** @var array */
    public array $targetPlayer = [];
    /** @var array */
    public array $KillLeaderboard = [];
    /** @var array */
    public array $DeathLeaderboard = [];
    /** @var array */
    public array $ParkourLeaderboard = [];
    /** @var array */
    public array $ClickData = [];
    /** @var array */
    public array $buildBlocks = [];
    /** @var bool */
    public bool $Restarting = false;
    /** @var array */
    public array $LeapCooldown = [];
}