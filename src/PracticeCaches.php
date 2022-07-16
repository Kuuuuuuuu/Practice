<?php

declare(strict_types=1);

namespace Kuu;

class PracticeCaches
{
    public array $BoxingPoint = [];
    public array $DuelMatch = [];
    public array $targetPlayer = [];
    public array $KillLeaderboard = [];
    public array $DeathLeaderboard = [];
    public array $ClickData = [];
    public array $buildBlocks = [];
    public array $LeapCooldown = [];
    public bool $Restarted = false;
}