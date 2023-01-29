<?php

declare(strict_types=1);

namespace Nayuki;

use Nayuki\Arena\ArenaFactory;
use Nayuki\Arena\ArenaManager;
use Nayuki\Commands\HologramCommand;
use Nayuki\Commands\HubCommand;
use Nayuki\Commands\PracticeCommand;
use Nayuki\Commands\RestartCommand;
use Nayuki\Commands\SetTagCommand;
use Nayuki\Commands\TbanCommand;
use Nayuki\Commands\TcheckCommand;
use Nayuki\Commands\TpsCommand;
use Nayuki\Entities\EnderPearlEntity;
use Nayuki\Entities\Hologram;
use Nayuki\Entities\JoinEntity;
use Nayuki\Events\PracticeListener;
use Nayuki\Game\Duel\DuelManager;
use Nayuki\Game\Generator\DuelGenerator;
use Nayuki\Game\Generator\SumoGenerator;
use Nayuki\Items\CustomSplashPotion;
use Nayuki\Items\EnderPearl;
use Nayuki\Players\PlayerHandler;
use Nayuki\Players\SessionManager;
use Nayuki\Task\BroadcastTask;
use Nayuki\Task\PracticeTask;
use Nayuki\Utils\ClickHandler;
use Nayuki\Utils\FormUtils;
use Nayuki\Utils\Scoreboard\ScoreboardManager;
use Nayuki\Utils\Scoreboard\ScoreboardUtils;
use pocketmine\data\bedrock\EntityLegacyIds;
use pocketmine\data\bedrock\PotionTypeIdMap;
use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\EntityFactory;
use pocketmine\entity\Human;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemIds;
use pocketmine\item\PotionType;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\world\generator\GeneratorManager;
use pocketmine\world\World;
use SQLite3;

use function is_array;

final class PracticeCore extends PluginBase
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
    private static SessionManager $playerSession;
    private static DuelManager $duelManager;
    public SQLite3 $BanDatabase;

    /**
     * @return string
     */
    public static function getScoreboardTitle(): string
    {
        return PracticeConfig::SBPREFIX;
    }

    /**
     * @return SessionManager
     */
    public static function getSessionManager(): SessionManager
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

    public static function getDuelManager(): DuelManager
    {
        return self::$duelManager;
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
        self::$playerSession = new SessionManager();
        self::$duelManager = new DuelManager();
    }

    public function onEnable(): void
    {
        $this->registerConfigs();
        $this->registerGenerators();
        $this->unregisterCommands();
        $this->registerItems();
        $this->registerCommands();
        $this->registerEvents();
        $this->registerTasks();
        $this->registerEntities();
        $this->loadWorlds();
        Server::getInstance()->getNetwork()->setName(PracticeConfig::MOTD);
    }

    /**
     * @return void
     */
    private function registerConfigs(): void
    {
        @mkdir(self::getPlayerDataPath());
        @mkdir($this->getDataFolder() . 'data/');
        self::getInstance()->BanDatabase = new SQLite3($this->getDataFolder() . 'Ban.db');
        self::getInstance()->BanDatabase->exec('CREATE TABLE IF NOT EXISTS banPlayers(player TEXT PRIMARY KEY, banTime INT, reason TEXT, staff TEXT);');
    }

    public static function getPlayerDataPath(): string
    {
        return self::getInstance()->getDataFolder() . 'player/';
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
    private function registerGenerators(): void
    {
        $generator = [
            DuelGenerator::class => 'duel',
            SumoGenerator::class => 'sumo'
        ];
        foreach ($generator as $key => $value) {
            GeneratorManager::getInstance()->addGenerator($key, $value, fn () => null);
        }
    }

    /**
     * @return void
     */
    private function unregisterCommands(): void
    {
        $commands = [
            'seed',
            'title',
            'mixer',
            'suicide',
            'particle',
            'me',
            'tell',
            'clear',
            'whitelist',
            'checkperm',
            'pardon-ip',
            'pardon',
            'ban',
            'ban-ip'
        ];
        foreach ($commands as $name) {
            $map = $this->getServer()->getCommandMap();
            if (($cmd = $map->getCommand($name)) !== null) {
                $map->unregister($cmd);
            }
        }
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
            'hologram' => HologramCommand::class,
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
        $this->getScheduler()->scheduleDelayedRepeatingTask(new BroadcastTask(), 200, 9000);
    }

    /**
     * @return void
     */
    private function registerEntities(): void
    {
        EntityFactory::getInstance()->register(EnderPearlEntity::class, function (World $world, CompoundTag $nbt): EnderPearlEntity {
            return new EnderPearlEntity(EntityDataHelper::parseLocation($nbt, $world), null, $nbt);
        }, ['ender_pearl'], EntityLegacyIds::ENDER_PEARL);
        EntityFactory::getInstance()->register(Hologram::class, function (World $world, CompoundTag $nbt): Hologram {
            return new Hologram(EntityDataHelper::parseLocation($nbt, $world), $nbt);
        }, ['Hologram']);
        EntityFactory::getInstance()->register(JoinEntity::class, function (World $world, CompoundTag $nbt): JoinEntity {
            return new JoinEntity(EntityDataHelper::parseLocation($nbt, $world), Human::parseSkinNBT($nbt), $nbt);
        }, ['joinentity']);
    }

    /**
     * @return void
     */
    private function loadWorlds(): void
    {
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
    }
}
