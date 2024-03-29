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
use Nayuki\Commands\TpsCommand;
use Nayuki\Entities\Hologram;
use Nayuki\Entities\PracticeBot;
use Nayuki\Game\Duel\DuelManager;
use Nayuki\Game\Generator\DuelGenerator;
use Nayuki\Game\Generator\SumoGenerator;
use Nayuki\Game\Generator\VoidGenerator;
use Nayuki\Players\PlayerHandler;
use Nayuki\Players\SessionManager;
use Nayuki\Task\PracticeTask;
use Nayuki\Utils\ClickHandler;
use Nayuki\Utils\CosmeticHandler;
use Nayuki\Utils\FormUtils;
use Nayuki\Utils\Scoreboard\ScoreboardManager;
use Nayuki\Utils\Scoreboard\ScoreboardUtils;
use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\EntityFactory;
use pocketmine\entity\Human;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use pocketmine\world\generator\GeneratorManager;
use pocketmine\world\World;
use ReflectionClass;
use ReflectionException;
use function is_array;

final class PracticeCore extends PluginBase
{
    public static bool $isRestarting = false;
    private static self $plugin;
    private static ClickHandler $cps;
    private static ScoreboardUtils $score;
    private static FormUtils $form;
    private static ArenaFactory $arenaFactory;
    private static ArenaManager $arena;
    private static PracticeUtils $PracticeUtils;
    private static ScoreboardManager $scoreboardManager;
    private static PlayerHandler $playerHandler;
    private static SessionManager $playerSession;
    private static DuelManager $duelManager;
    private static CosmeticHandler $cosmeticHandler;

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
        return self::$arenaFactory;
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
        return self::$scoreboardManager;
    }

    /**
     * @return PlayerHandler
     */
    public static function getPlayerHandler(): PlayerHandler
    {
        return self::$playerHandler;
    }

    /**
     * @return DuelManager
     */
    public static function getDuelManager(): DuelManager
    {
        return self::$duelManager;
    }

    /**
     * @return CosmeticHandler
     */
    public static function getCosmeticHandler(): CosmeticHandler
    {
        return self::$cosmeticHandler;
    }

    protected function onLoad(): void
    {
        self::$plugin = $this;
        self::$cps = new ClickHandler();
        self::$score = new ScoreboardUtils();
        self::$form = new FormUtils();
        self::$arenaFactory = new ArenaFactory();
        self::$arena = new ArenaManager();
        self::$PracticeUtils = new PracticeUtils();
        self::$scoreboardManager = new ScoreboardManager();
        self::$playerHandler = new PlayerHandler();
        self::$playerSession = new SessionManager();
        self::$duelManager = new DuelManager();
        self::$cosmeticHandler = new CosmeticHandler();
    }

    /**
     * @throws ReflectionException
     */
    protected function onEnable(): void
    {
        $this->registerConfigs();
        $this->registerGenerators();
        $this->unregisterCommands();
        $this->registerCommands();
        $this->registerEvents();
        $this->registerTasks();
        $this->registerEntities();
        $this->loadWorlds();
        Server::getInstance()->getNetwork()->setName(PracticeConfig::MOTD);
    }

    /**
     * @return void
     * @noinspection SqlNoDataSourceInspection
     * @noinspection SqlDialectInspection
     */
    private function registerConfigs(): void
    {
        @mkdir(self::getPlayerDataPath());
        @mkdir($this->getDataFolder() . 'data/');
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
            SumoGenerator::class => 'sumo',
            VoidGenerator::class => 'void'
        ];
        $generatorManager = GeneratorManager::getInstance();
        foreach ($generator as $key => $value) {
            $generatorManager->addGenerator($key, $value, fn() => null);
            $this->getLogger()->notice(TextFormat::AQUA . 'Register World Generator ' . TextFormat::GREEN . $value);
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
        $commandsToUnregister = array_filter(array_map([$commandMap, 'getCommand'], $commands));
        array_walk($commandsToUnregister, [$commandMap, 'unregister']);
    }

    /**
     * @return void
     * @throws ReflectionException
     */
    private function registerCommands(): void
    {
        $Command = [
            'hub' => HubCommand::class,
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
            $this->getLogger()->notice(TextFormat::AQUA . 'Register Commands ' . TextFormat::GREEN . $name);
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
        EntityFactory::getInstance()->register(Hologram::class, function (World $world, CompoundTag $nbt): Hologram {
            return new Hologram(EntityDataHelper::parseLocation($nbt, $world), $nbt);
        }, ['Hologram']);
        EntityFactory::getInstance()->register(PracticeBot::class, function (World $world, CompoundTag $nbt): PracticeBot {
            return new PracticeBot(EntityDataHelper::parseLocation($nbt, $world), Human::parseSkinNBT($nbt), $nbt);
        }, ['Bot']);
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
                if (str_starts_with(strtolower($worldName), 'duel')) {
                    $practiceUtils->deleteDir($worldPath);
                    continue;
                }
                $world = Server::getInstance()->getWorldManager()->getWorldByName($worldName);
                if ($world !== null) {
                    continue;
                }
                $this->getLogger()->notice(TextFormat::AQUA . 'Load World ' . TextFormat::GREEN . $worldName);
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
        $server = Server::getInstance();
        foreach ($server->getWorldManager()->getWorlds() as $world) {
            foreach ($world->getEntities() as $entity) {
                if ($entity instanceof Hologram || $entity instanceof Player) {
                    continue;
                }
                $entity->close();
            }
        }
    }
}
