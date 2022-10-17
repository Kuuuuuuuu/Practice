<?php

/** @noinspection PhpMethodParametersCountMismatchInspection */
/** @noinspection PhpMissingFieldTypeInspection */
/** @noinspection PhpParamsInspection */

declare(strict_types=1);

namespace Kuu;

use Kuu\Arena\ArenaFactory;
use Kuu\Arena\ArenaManager;
use Kuu\Arena\Duel\DuelManager;
use Kuu\Players\PlayerHandler;
use Kuu\Task\PracticeTask;
use Kuu\Utils\ClickHandler;
use Kuu\Utils\CosmeticManager;
use Kuu\Utils\DeleteBlocksHandler;
use Kuu\Utils\FormUtils;
use Kuu\Utils\KnockbackManager;
use Kuu\Utils\Scoreboard\ScoreboardManager;
use Kuu\Utils\Scoreboard\ScoreboardUtils;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use SQLite3;

class PracticeCore extends PluginBase
{
    private static self $plugin;
    private static ClickHandler $cps;
    private static ScoreboardUtils $score;
    private static FormUtils $form;
    private static ArenaFactory $arenafac;
    private static ArenaManager $arena;
    private static DeleteBlocksHandler $blockhandle;
    private static KnockbackManager $knockback;
    private static CosmeticManager $cosmetics;
    private static PracticeUtils $PracticeUtils;
    private static ScoreboardManager $scoremanager;
    private static DuelManager $duelmanager;
    private static ?PracticeTask $CoreTask;
    private static PracticeCaches $caches;
    private static PlayerHandler $playerHandler;
    public Config|array $MessageData;
    public SQLite3 $BanData;
    public Config $KitData;

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

    public static function getScoreboardUtils(): ScoreboardUtils
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

    public static function getCosmeticHandler(): CosmeticManager
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

    public static function getPlayerHandler(): PlayerHandler
    {
        return self::$playerHandler;
    }

    public static function getCaches(): PracticeCaches
    {
        return self::$caches;
    }

    public static function getInstance(): PracticeCore
    {
        return self::$plugin;
    }

    public function onLoad(): void
    {
        self::$plugin = $this;
        self::$cps = new ClickHandler();
        self::$score = new ScoreboardUtils();
        self::$form = new FormUtils();
        self::$arenafac = new ArenaFactory();
        self::$arena = new ArenaManager();
        self::$blockhandle = new DeleteBlocksHandler();
        self::$knockback = new KnockbackManager();
        self::$cosmetics = new CosmeticManager();
        self::$PracticeUtils = new PracticeUtils();
        self::$scoremanager = new ScoreboardManager();
        self::$duelmanager = new DuelManager();
        self::$caches = new PracticeCaches();
        self::$playerHandler = new PlayerHandler();
    }

    public function onEnable(): void
    {
        self::getPracticeUtils()->initialize();
    }

    public static function getPracticeUtils(): PracticeUtils
    {
        return self::$PracticeUtils;
    }

    public function onDisable(): void
    {
        self::setCoreTask(null);
    }
}
