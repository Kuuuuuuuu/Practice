<?php

/** @noinspection PhpMethodParametersCountMismatchInspection */
/** @noinspection PhpMissingFieldTypeInspection */
/** @noinspection PhpParamsInspection */

declare(strict_types=1);

namespace Kuu;

use JsonException;
use Kuu\Arena\ArenaFactory;
use Kuu\Arena\ArenaManager;
use Kuu\Arena\BotDuelManager;
use Kuu\Arena\DuelManager;
use Kuu\Task\NeptuneTask;
use Kuu\Utils\ArenaUtils;
use Kuu\Utils\ClickHandler;
use Kuu\Utils\CosmeticHandler;
use Kuu\Utils\DeleteBlocksHandler;
use Kuu\Utils\FormUtils;
use Kuu\Utils\KnockbackManager;
use Kuu\Utils\ScoreboardManager;
use Kuu\utils\ScoreboardsUtils;
use Kuu\Utils\YamlManager;
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
    private static KnockbackManager $knockback;
    private static CosmeticHandler $cosmetics;
    private static ArenaUtils $arenautils;
    private static ScoreboardManager $scoremanager;
    private static DuelManager $duelmanager;
    private static ?NeptuneTask $CoreTask;
    private static BotDuelManager $botduelmanager;
    public Config|array $MessageData;
    public Config $CapeData;
    public Config $ArtifactData;
    public SQLite3 $BanData;
    public Config $KitData;
    public int $RestartTime = 31;
    public bool $Restarted = false;
    public array $targetPlayer = [];
    public array $SumoArenas = [];
    public array $SumoSetup = [];
    public array $SumoData = [];
    public array $KillLeaderboard = [];
    public array $DeathLeaderboard = [];
    public array $PartyData = [];
    public array $PartyInvite = [];
    public array $TargetInvites = [];
    public array $TargetParty = [];
    public array $TargetPlayer = [];
    public array $BanCommand = [
        'hub',
        'kill'
    ];
    public array $ControlList = [
        'Unknown',
        'Keyboard',
        'Touch',
        'Controller'
    ];
    public array $OSList = [
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

    public static function getCoreTask(): ?NeptuneTask
    {
        return self::$CoreTask;
    }

    public static function setCoreTask(?NeptuneTask $task)
    {
        self::$CoreTask = $task;
    }

    public static function getScoreboardTitle(): string
    {
        return ConfigCore::SBPREFIX;
    }

    public static function getPrefixCore(): string
    {
        return ConfigCore::PREFIX;
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

    public static function getBotDuelManager(): BotDuelManager
    {
        return self::$botduelmanager;
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
        self::$knockback = new KnockbackManager();
        self::$cosmetics = new CosmeticHandler();
        self::$arenautils = new ArenaUtils();
        self::$scoremanager = new ScoreboardManager();
        self::$duelmanager = new DuelManager();
        self::$botduelmanager = new BotDuelManager();
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
