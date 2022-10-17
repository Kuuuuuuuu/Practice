<?php

namespace Kuu;

class PracticeCaches
{
    public array $DuelMatch = [];
    public array $targetPlayer = [];
    public array $KillLeaderboard = [];
    public array $DeathLeaderboard = [];
    public array $ParkourLeaderboard = [];
    public array $ClickData = [];
    public array $buildBlocks = [];
    public bool $Restarting = false;
    public array $LeapCooldown = [];
}