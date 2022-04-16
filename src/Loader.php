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
use Kohaku\Task\NeptuneTask;
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
    private static self $plugin;
    private static YamlManager $YamlLoader;
    private static ClickHandler $cps;
    private static ScoreboardsUtils $score;
    private static FormUtils $form;
    private static ArenaFactory $arenafac;
    private static ArenaManager $arena;
    private static DeleteBlocksHandler $blockhandle;
    private static BotUtils $bot;
    private static KnockbackManager $knockback;
    private static CosmeticHandler $cosmetics;
    private static ArenaUtils $arenautils;
    private static ScoreboardManager $scoremanager;
    private static DuelManager $duelmanager;
    private static ?NeptuneTask $CoreTask;
    public Config|array $MessageData;
    public Config $CapeData;
    public Config $ArtifactData;
    public SQLite3 $BanData;
    public Config $KitData;
    public int $RestartTime = 31;
    public int $DeleteBlockTime = 8;
    public int $MaximumCPS = 20;
    public bool $Restarted = false;
    public float $EnderPearlForce = 2.5;
    public array $targetPlayer = [];
    public array $SumoArenas = [];
    public array $SumoSetup = [];
    public array $SumoData = [];
    public array $KillLeaderboard = [];
    public array $DeathLeaderboard = [];
    public array $ParkourLeaderboard = [];
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

    public static function getCoreTask(): ?NeptuneTask
    {
        return self::$CoreTask;
    }

    public static function setCoreTask(NeptuneTask $task)
    {
        self::$CoreTask = $task;
    }

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

    public static function getInstance(): Loader
    {
        return self::$plugin;
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
        self::$duelmanager = new DuelManager();
    }

    public function onEnable(): void
    {
        self::$YamlLoader = new YamlManager();
        self::$YamlLoader->loadArenas();
        $this->getArenaUtils()->Enable();
    }

    public static function getArenaUtils(): ArenaUtils
    {
        return self::$arenautils;
    }

    /**
     * @throws JsonException
     */
    public function onDisable(): void
    {
        self::$YamlLoader->saveArenas();
        self::getArenaUtils()->Disable();
    }
}
