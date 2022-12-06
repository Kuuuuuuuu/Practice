<?php

namespace Kuu;

use pocketmine\utils\TextFormat;

interface PracticeConfig
{
    public const PREFIX = '§bNeptune§f » §r';
    public const Server_Name = '§bNeptune§f ';
    public const SBPREFIX = '§f» §bNeptune §f«';
    public const MOTD = '§b§lNeptune';
    public const PearlForce = 3;
    public const SplashForce = 0.45;
    public const COLOR = TextFormat::AQUA;
    public const BanCommand = [
        'hub',
        'kill'
    ];
}
