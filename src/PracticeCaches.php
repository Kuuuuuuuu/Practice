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
    public array $LeapCooldown = [];
    public bool $Restarted = false;
}