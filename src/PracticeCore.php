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

    /**
     * @return PracticeTask|null
     */
    public static function getCoreTask(): ?PracticeTask
    {
        return self::$CoreTask;
    }

    /**
     * @param PracticeTask|null $task
     * @return void
     */
    public static function setCoreTask(?PracticeTask $task): void
    {
        self::$CoreTask = $task;
    }

    /**
     * @return string
     */
    public static function getScoreboardTitle(): string
    {
        return PracticeConfig::SBPREFIX;
    }

    /**
     * @return string
     */
    public static function getPrefixCore(): string
    {
        return PracticeConfig::PREFIX;
    }

    /**
     * @return DeleteBlocksHandler
     */
    public static function getDeleteBlockHandler(): DeleteBlocksHandler
    {
        return self::$blockhandle;
    }

    /**
     * @return FormUtils
     */
    public static function getFormUtils(): FormUtils
    {
        return self::$form;
    }

    /**
     * @return ArenaFactory
     */
    public static function getArenaFactory(): ArenaFactory
    {
        return self::$arenafac;
    }

    /**
     * @return ArenaManager
     */
    public static function getArenaManager(): ArenaManager
    {
        return self::$arena;
    }

    /**
     * @return ScoreboardUtils
     */
    public static function getScoreboardUtils(): ScoreboardUtils
    {
        return self::$score;
    }

    /**
     * @return ClickHandler
     */
    public static function getClickHandler(): ClickHandler
    {
        return self::$cps;
    }

    /**
     * @return KnockbackManager
     */
    public static function getKnockbackManager(): KnockbackManager
    {
        return self::$knockback;
    }

    /**
     * @return CosmeticManager
     */
    public static function getCosmeticHandler(): CosmeticManager
    {
        return self::$cosmetics;
    }

    /**
     * @return ScoreboardManager
     */
    public static function getScoreboardManager(): ScoreboardManager
    {
        return self::$scoremanager;
    }

    /**
     * @return DuelManager
     */
    public static function getDuelManager(): DuelManager
    {
        return self::$duelmanager;
    }

    /**
     * @return PlayerHandler
     */
    public static function getPlayerHandler(): PlayerHandler
    {
        return self::$playerHandler;
    }

    /**
     * @return PracticeCaches
     */
    public static function getCaches(): PracticeCaches
    {
        return self::$caches;
    }

    /**
     * @return PracticeCore
     */
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

    /**
     * @return PracticeUtils
     */
    public static function getPracticeUtils(): PracticeUtils
    {
        return self::$PracticeUtils;
    }

    public function onDisable(): void
    {
        self::setCoreTask(null);
    }
}
