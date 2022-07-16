<?php

namespace Kuu;

final class PracticeConfig
{
    public const PREFIX = '§dNeptune§f » §r';
    public const Server_Name = '§dNeptune§f ';
    public const SBPREFIX = '§f» §dNeptune §f«';
    public const MOTD = '§d§lNeptune';
    public const PearlForce = 2.5;
    public const DeleteBlockTime = 10;
    public const MaximumCPS = 16;
    public const SplashForce = 0.25;
    public const OITCBowDelay = 100;
    public const BOTNAME = 'PracticeBot';
    public const IPV6 = false;
    public const BuildFFASpawns = [
        [
            'x' => 263,
            'y' => 80,
            'z' => 269
        ],
        [
            'x' => 238,
            'y' => 81,
            'z' => 249
        ],
        [
            'x' => 219,
            'y' => 79,
            'z' => 287
        ]
    ];
    public const OITCSpawns = [
        [
            'x' => 246,
            'y' => 67,
            'z' => 180
        ],
        [
            'x' => 187,
            'y' => 65,
            'z' => 180
        ],
        [
            'x' => 260,
            'y' => 65,
            'z' => 271
        ]
    ];
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