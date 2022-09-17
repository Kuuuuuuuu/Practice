<?php

namespace Kuu;

class PracticeCaches
{
    public array $DuelMatch = [];
    public array $targetPlayer = [];
    public array $KillLeaderboard = [];
    public array $DeathLeaderboard = [];
    public array $ClickData = [];
    public array $buildBlocks = [];
    public bool $Restarted = false;
    public array $AvailableDuel = [];
    public array $LeapCooldown = [];
}