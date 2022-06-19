<?php

namespace Kuu;

interface PracticeConfig
{
    public const PREFIX = '§dNeptune§f » §r';
    public const Server_Name = '§dNeptune§f ';
    public const SBPREFIX = '§f» §dNeptune §f«';
    public const MOTD = '§d§lNeptune';
    public const PearlForce = 2.5;
    public const DeleteBlockTime = 10;
    public const MaximumCPS = 20;
    public const EnderPearlCooldown = 10;
    public const SplashForce = 0.25;
    public const OITCBowDelay = 100;
    public const SkillCooldownDelay = 250;
    public const IPV6 = false;
    public const BanCommand = [
        'hub',
        'kill'
    ];
    public const ControlList = [
        'Unknown',
        'Keyboard',
        'Touch',
        'Controller'
    ];
    public const OSList = [
        'Unknown',
        'Android',
        'iOS',
        'macOS',
        'FireOS',
        'GearVR',
        'HoloLens',
        'Win10',
        'Win32',
        'EducalVersion',
        'Dedicated',
        'PS4',
        'Switch',
        'Xbox'
    ];
}