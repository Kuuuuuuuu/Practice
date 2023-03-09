<?php

/** @noinspection SqlDialectInspection */
/** @noinspection SqlNoDataSourceInspection */
/** @noinspection MkdirRaceConditionInspection */

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
use Nayuki\Duel\DuelManager;
use Nayuki\Entities\EnderPearlEntity;
use Nayuki\Entities\Hologram;
use Nayuki\Entities\JoinEntity;
use Nayuki\Game\Generator\DuelGenerator;
use Nayuki\Game\Generator\SumoGenerator;
use Nayuki\Items\CustomSplashPotion;
use Nayuki\Players\PlayerHandler;
use Nayuki\Players\SessionManager;
use Nayuki\Task\PracticeTask;
use Nayuki\Utils\ClickHandler;
use Nayuki\Utils\FormUtils;
use Nayuki\Utils\Scoreboard\ScoreboardManager;
use Nayuki\Utils\Scoreboard\ScoreboardUtils;
use pocketmine\data\bedrock\EntityLegacyIds;
use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\EntityFactory;
use pocketmine\entity\Human;
use pocketmine\item\ItemFactory;
use pocketmine\item\PotionType;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\world\generator\GeneratorManager;
use pocketmine\world\World;
use ReflectionClass;
use ReflectionException;
use SQLite3;

use function is_array;

final class PracticeCore extends PluginBase
{
    public static bool $isRestarting = false;
    private static self $plugin;
    private static ClickHandler $cps;
    private static ScoreboardUtils $score;
    private static FormUtils $form;
    private static ArenaFactory $arenafac;
    private static ArenaManager $arena;
    private static PracticeUtils $PracticeUtils;
    private static ScoreboardManager $scoremanager;
    private static PlayerHandler $playerHandler;
    private static SessionManager $playerSession;
    private static DuelManager $duelManager;
    public array $targetPlayer = [];
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

    public static function getDuelManager(): DuelManager
    {
        return self::$duelManager;
    }

    protected function onLoad(): void
    {
        self::$plugin = $this;
        self::$cps = new ClickHandler();
        self::$score = new ScoreboardUtils();
        self::$form = new FormUtils();
        self::$arenafac = new ArenaFactory();
        self::$arena = new ArenaManager();
        self::$PracticeUtils = new PracticeUtils();
        self::$scoremanager = new ScoreboardManager();
        self::$playerHandler = new PlayerHandler();
        self::$playerSession = new SessionManager();
        self::$duelManager = new DuelManager();
    }

    /**
     * @throws ReflectionException
     */
    protected function onEnable(): void
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
            GeneratorManager::getInstance()->addGenerator($key, $value, fn() => null);
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
        $commandMap = $this->getServer()->getCommandMap();
        $commandsToUnregister = array_map(static fn($name) => $commandMap->getCommand($name), $commands);
        $commandsToUnregister = array_filter($commandsToUnregister);
        foreach ($commandsToUnregister as $command) {
            $commandMap->unregister($command);
        }
    }

    /**
     * @return void
     */
    private function registerItems(): void
    {
        foreach (PotionType::getAll() as $type) {
            ItemFactory::getInstance()->register(new CustomSplashPotion($type), true);
        }
    }

    /**
     * @return void
     * @throws ReflectionException
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
        $commandMap = Server::getInstance()->getCommandMap();
        foreach ($Command as $name => $class) {
            $reflectionClass = new ReflectionClass($class);
            if ($reflectionClass->hasMethod('__construct')) {
                $command = $reflectionClass->newInstance($name);
            } else {
                $command = $reflectionClass->newInstance();
            }
            $commandMap->register($name, $command);
            $this->getLogger()->notice('§bRegister Commands §a' . $name);
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
            return new EnderPearlEntity(EntityDataHelper::parseLocation($nbt, $world), null);
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
        $worldsDir = Server::getInstance()->getDataPath() . 'worlds/';
        $worlds = glob($worldsDir . '*', GLOB_ONLYDIR);
        if (is_array($worlds)) {
            $practiceUtils = self::getUtils();
            foreach ($worlds as $worldPath) {
                $worldName = str_replace($worldsDir, '', $worldPath);
                if (str_starts_with($worldName, 'duel')) {
                    $practiceUtils->deleteDir($worldPath);
                    continue;
                }
                $world = Server::getInstance()->getWorldManager()->getWorldByName($worldName);
                if ($world !== null) {
                    continue;
                }
                $this->getLogger()->notice('§bLoad World §a' . $worldName);
                Server::getInstance()->getWorldManager()->loadWorld($worldName, true);
            }
        }
    }

    /**
     * @return PracticeUtils
     */
    public static function getUtils(): PracticeUtils
    {
        return self::$PracticeUtils;
    }

    protected function onDisable(): void
    {
        foreach (Server::getInstance()->getWorldManager()->getWorlds() as $world) {
            foreach ($world->getEntities() as $entity) {
                if ($entity instanceof Hologram || $entity instanceof JoinEntity) {
                    continue;
                }
                $entity->close();
            }
        }
    }
}
