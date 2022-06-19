<?php

/** @noinspection PhpMethodParametersCountMismatchInspection */
/** @noinspection PhpMissingFieldTypeInspection */
/** @noinspection PhpParamsInspection */

declare(strict_types=1);

namespace Kuu;

use Kuu\Arena\ArenaFactory;
use Kuu\Arena\ArenaManager;
use Kuu\Arena\BotDuelManager;
use Kuu\Arena\DuelManager;
use Kuu\Task\PracticeTask;
use Kuu\Utils\ArenaUtils;
use Kuu\Utils\ClickHandler;
use Kuu\Utils\CosmeticHandler;
use Kuu\Utils\DeleteBlocksHandler;
use Kuu\Utils\FormUtils;
use Kuu\Utils\KnockbackManager;
use Kuu\Utils\ScoreboardManager;
use Kuu\utils\ScoreboardsUtils;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use SQLite3;

class PracticeCore extends PluginBase
{
    private static self $plugin;
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
    private static ?PracticeTask $CoreTask;
    private static BotDuelManager $botduelmanager;
    public Config|array $MessageData;
    public Config $CapeData;
    public Config $ArtifactData;
    public SQLite3 $BanData;
    public Config $KitData;
    public bool $Restarted = false;
    public array $targetPlayer = [];
    public array $KillLeaderboard = [];
    public array $DeathLeaderboard = [];

    public static function getCoreTask(): ?PracticeTask
    {
        return self::$CoreTask;
    }

    public static function setCoreTask(?PracticeTask $task): void
    {
        self::$CoreTask = $task;
    }

    public static function getScoreboardTitle(): string
    {
        return PracticeConfig::SBPREFIX;
    }

    public static function getPrefixCore(): string
    {
        return PracticeConfig::PREFIX;
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

    public static function getInstance(): PracticeCore
    {
        return self::$plugin;
    }

    protected function onLoad(): void
    {
        self::$plugin = $this;
        self::$cps = new ClickHandler();
        self::$score = new ScoreboardsUtils();
        self::$form = new FormUtils();
        self::$arenafac = new ArenaFactory();
        self::$arena = new ArenaManager();
        self::$blockhandle = new DeleteBlocksHandler();
        self::$knockback = new KnockbackManager();
        self::$cosmetics = new CosmeticHandler();
        self::$arenautils = new ArenaUtils();
        self::$scoremanager = new ScoreboardManager();
        self::$duelmanager = new DuelManager();
        self::$botduelmanager = new BotDuelManager();
    }

    protected function onEnable(): void
    {
        self::getArenaUtils()->Enable();
    }

    public static function getArenaUtils(): ArenaUtils
    {
        return self::$arenautils;
    }

    protected function onDisable(): void
    {
        self::getArenaUtils()->Disable();
        self::setCoreTask(null);
    }
}
