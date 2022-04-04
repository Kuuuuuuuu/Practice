<?php

/** @noinspection PhpMethodParametersCountMismatchInspection */
/** @noinspection PhpMissingFieldTypeInspection */
/** @noinspection PhpParamsInspection */

declare(strict_types=1);

namespace Kohaku;

use JsonException;
use Kohaku\Arena\ArenaFactory;
use Kohaku\Arena\ArenaManager;
use Kohaku\Arena\DuelManager;
use Kohaku\Utils\ArenaUtils;
use Kohaku\Utils\BotUtils;
use Kohaku\Utils\ClickHandler;
use Kohaku\Utils\CosmeticHandler;
use Kohaku\Utils\DeleteBlocksHandler;
use Kohaku\Utils\FormUtils;
use Kohaku\Utils\KnockbackManager;
use Kohaku\Utils\ScoreboardManager;
use Kohaku\utils\ScoreboardsUtils;
use Kohaku\Utils\YamlManager;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use SQLite3;

class Loader extends PluginBase
{
    public static self $plugin;
    public static ClickHandler $cps;
    public static ScoreboardsUtils $score;
    public static FormUtils $form;
    public static ArenaFactory $arenafac;
    public static ArenaManager $arena;
    public static YamlManager $YamlLoader;
    public static DeleteBlocksHandler $blockhandle;
    public static BotUtils $bot;
    public static KnockbackManager $knockback;
    public static CosmeticHandler $cosmetics;
    public static ArenaUtils $arenautils;
    public static ScoreboardManager $scoremanager;
    public static DuelManager $duelmanager;
    public Config|array $MessageData;
    public Config $CapeData;
    public Config $ArtifactData;
    public Config $KitData;
    public SQLite3 $BanData;
    public int $RestartTime = 31;
    public int $DeleteBlockTime = 8;
    public int $MaximumCPS = 20;
    public bool $Restarted = false;
    public float $EnderPearlForce = 2.5;
    public array $CombatTimer = [];
    public array $PlayerOpponent = [];
    public array $TimerData = [];
    public array $TimerTask = [];
    public array $targetPlayer = [];
    public array $BoxingPoint = [];
    public array $ToolboxCheck = [];
    public array $PlayerDevice = [];
    public array $PlayerOS = [];
    public array $PlayerControl = [];
    public array $SkillCooldown = [];
    public array $SumoArenas = [];
    public array $SumoSetup = [];
    public array $SumoData = [];
    public array $ParkourCheckPoint = [];
    public array $EditKit = [];
    public array $KillLeaderboard = [];
    public array $DeathLeaderboard = [];
    public array $BanCommand = [
        "hub",
        "kill"
    ];
    public array $ControlList = [
        "Unknown",
        "Mouse",
        "Touch",
        "Controller"
    ];
    public array $OSList = [
        "Unknown",
        "Android",
        "iOS",
        "macOS",
        "FireOS",
        "GearVR",
        "HoloLens",
        "Windows",
        "Windows",
        "EducalVersion",
        "Dedicated",
        "PlayStation",
        "Switch",
        "XboxOne"
    ];

    public static function getScoreboardTitle(): string
    {
        return "§f» §dNeptune §f«";
    }

    public static function getPrefixCore(): string
    {
        return "§dNeptune§f » §r";
    }

    public static function getDeleteBlockHandler(): DeleteBlocksHandler
    {
        return self::$blockhandle;
    }

    public static function getFormUtils(): FormUtils
    {
        return self::$form;
    }

    public static function getArenaFactory(): ArenaFactory
    {
        return self::$arenafac;
    }

    public static function getArenaManager(): ArenaManager
    {
        return self::$arena;
    }

    public static function getScoreboardUtils(): ScoreboardsUtils
    {
        return self::$score;
    }

    public static function getClickHandler(): ClickHandler
    {
        return self::$cps;
    }

    public static function getBotUtils(): BotUtils
    {
        return self::$bot;
    }

    public static function getKnockbackManager(): KnockbackManager
    {
        return self::$knockback;
    }

    public static function getCosmeticHandler(): CosmeticHandler
    {
        return self::$cosmetics;
    }

    public static function getScoreboardManager(): ScoreboardManager
    {
        return self::$scoremanager;
    }

    public static function getDuelManager(): DuelManager
    {
        return self::$duelmanager;
    }

    public function onLoad(): void
    {
        self::$plugin = $this;
        self::$cps = new ClickHandler();
        self::$score = new ScoreboardsUtils();
        self::$form = new FormUtils();
        self::$arenafac = new ArenaFactory();
        self::$arena = new ArenaManager();
        self::$YamlLoader = new YamlManager();
        self::$blockhandle = new DeleteBlocksHandler();
        self::$bot = new BotUtils();
        self::$knockback = new KnockbackManager();
        self::$cosmetics = new CosmeticHandler();
        self::$arenautils = new ArenaUtils();
        self::$scoremanager = new ScoreboardManager();
    }

    public function onEnable(): void
    {
        Loader::getInstance()->getArenaUtils()->Enable();
    }

    public static function getArenaUtils(): ArenaUtils
    {
        return self::$arenautils;
    }

    public static function getInstance(): Loader
    {
        return self::$plugin;
    }

    /**
     * @throws JsonException
     */
    public function onDisable(): void
    {
        Loader::getArenaUtils()->Disable();
    }
}
