<?php

declare(strict_types=1);

namespace Kuu;

use Kuu\Arena\ArenaFactory;
use Kuu\Arena\ArenaManager;
use Kuu\Commands\HubCommand;
use Kuu\Commands\PracticeCommand;
use Kuu\Commands\RestartCommand;
use Kuu\Commands\SetTagCommand;
use Kuu\Commands\TbanCommand;
use Kuu\Commands\TcheckCommand;
use Kuu\Commands\TpsCommand;
use Kuu\Entity\EnderPearlEntity;
use Kuu\Events\PracticeListener;
use Kuu\Items\CustomSplashPotion;
use Kuu\Items\EnderPearl;
use Kuu\Players\PlayerHandler;
use Kuu\Players\PlayerSession;
use Kuu\Task\PracticeTask;
use Kuu\Utils\ClickHandler;
use Kuu\Utils\FormUtils;
use Kuu\Utils\Scoreboard\ScoreboardManager;
use Kuu\Utils\Scoreboard\ScoreboardUtils;
use pocketmine\data\bedrock\EntityLegacyIds;
use pocketmine\data\bedrock\PotionTypeIdMap;
use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\EntityFactory;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemIds;
use pocketmine\item\PotionType;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\world\World;
use SQLite3;

class PracticeCore extends PluginBase
{
    private static self $plugin;
    private static ClickHandler $cps;
    private static ScoreboardUtils $score;
    private static FormUtils $form;
    private static ArenaFactory $arenafac;
    private static ArenaManager $arena;
    private static PracticeUtils $PracticeUtils;
    private static ScoreboardManager $scoremanager;
    private static PracticeCaches $caches;
    private static PlayerHandler $playerHandler;
    private static PlayerSession $playerSession;
    public SQLite3 $BanData;

    /**
     * @return string
     */
    public static function getScoreboardTitle(): string
    {
        return PracticeConfig::SBPREFIX;
    }

    /**
     * @return PlayerSession
     */
    public static function getPlayerSession(): PlayerSession
    {
        return self::$playerSession;
    }

    /**
     * @return string
     */
    public static function getPrefixCore(): string
    {
        return PracticeConfig::PREFIX;
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
     * @return ScoreboardManager
     */
    public static function getScoreboardManager(): ScoreboardManager
    {
        return self::$scoremanager;
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
     * @return PracticeUtils
     */
    public static function getPracticeUtils(): PracticeUtils
    {
        return self::$PracticeUtils;
    }

    public function onLoad(): void
    {
        self::$plugin = $this;
        self::$cps = new ClickHandler();
        self::$score = new ScoreboardUtils();
        self::$form = new FormUtils();
        self::$arenafac = new ArenaFactory();
        self::$arena = new ArenaManager();
        self::$PracticeUtils = new PracticeUtils();
        self::$scoremanager = new ScoreboardManager();
        self::$caches = new PracticeCaches();
        self::$playerHandler = new PlayerHandler();
        self::$playerSession = new PlayerSession();
    }

    public function onEnable(): void
    {
        $this->registerItems();
        $this->registerConfigs();
        $this->registerCommands();
        $this->registerEvents();
        $this->registerTasks();
        $this->registerEntities();
        $check = glob(Server::getInstance()->getDataPath() . 'worlds/*');
        if (is_array($check)) {
            foreach ($check as $world) {
                $world = str_replace(Server::getInstance()->getDataPath() . 'worlds/', '', $world);
                if (Server::getInstance()->getWorldManager()->isWorldLoaded($world)) {
                    continue;
                }
                Server::getInstance()->getWorldManager()->loadWorld($world, true);
            }
        }
        Server::getInstance()->getNetwork()->setName(PracticeConfig::MOTD);
    }

    /**
     * @return void
     */
    private function registerItems(): void
    {
        foreach (PotionType::getAll() as $type) {
            $typeId = PotionTypeIdMap::getInstance()->toId($type);
            ItemFactory::getInstance()->register(new CustomSplashPotion(new ItemIdentifier(ItemIds::SPLASH_POTION, $typeId), $type->getDisplayName() . ' Splash Potion', $type), true);
        }
        ItemFactory::getInstance()->register(new EnderPearl(new ItemIdentifier(ItemIds::ENDER_PEARL, 0), 'Ender Pearl'), true);
    }

    /**
     * @return PracticeCore
     */
    public static function getInstance(): PracticeCore
    {
        return self::$plugin;
    }

    /**
     * @return void
     */
    private function registerConfigs(): void
    {
        @mkdir(PracticeCore::getInstance()->getDataFolder() . 'data/');
        self::getInstance()->BanData = new SQLite3($this->getDataFolder() . 'Ban.db');
        self::getInstance()->BanData->exec('CREATE TABLE IF NOT EXISTS banPlayers(player TEXT PRIMARY KEY, banTime INT, reason TEXT, staff TEXT);');
    }

    /**
     * @return void
     */
    private function registerCommands(): void
    {
        $Command = [
            'hub' => HubCommand::class,
            'tban' => TbanCommand::class,
            'tcheck' => TcheckCommand::class,
            'tps' => TpsCommand::class,
            'practice' => PracticeCommand::class,
            'restart' => RestartCommand::class,
            'settag' => SetTagCommand::class,
        ];
        foreach ($Command as $key => $value) {
            Server::getInstance()->getCommandMap()->register($key, new $value());
        }
    }

    /**
     * @return void
     */
    private function registerEvents(): void
    {
        new PracticeListener();
    }

    /**
     * @return void
     */
    private function registerTasks(): void
    {
        new PracticeTask();
    }

    /**
     * @return void
     */
    private function registerEntities(): void
    {
        EntityFactory::getInstance()->register(EnderPearlEntity::class, function (World $world, CompoundTag $nbt): EnderPearlEntity {
            return new EnderPearlEntity(EntityDataHelper::parseLocation($nbt, $world), null, $nbt);
        }, ['NThrownEnderpearl', 'neptune:ender_pearl'], EntityLegacyIds::ENDER_PEARL);
    }
}
