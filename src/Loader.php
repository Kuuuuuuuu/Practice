<?php

/** @noinspection PhpMethodParametersCountMismatchInspection */
/** @noinspection PhpMissingFieldTypeInspection */
/** @noinspection PhpParamsInspection */

declare(strict_types=1);

namespace Kohaku\Core;

use JetBrains\PhpStorm\Pure;
use JsonException;
use Kohaku\Core\Arena\ArenaFactory;
use Kohaku\Core\Arena\ArenaManager;
use Kohaku\Core\Utils\ArenaUtils;
use Kohaku\Core\Utils\ClickHandler;
use Kohaku\Core\Utils\FormUtils;
use Kohaku\Core\Utils\MapReset;
use Kohaku\Core\utils\Scoreboards;
use Kohaku\Core\Utils\YamlDataProvider;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use SQLite3;

class Loader extends PluginBase
{
    public static self $plugin;
    public static ?ClickHandler $cps;
    public static ?Scoreboards $score;
    public static ?FormUtils $form;
    public static ?ArenaFactory $arenafac;
    public static ?ArenaManager $arena;
    public static YamlDataProvider $YamlLoader;
    public mixed $message;
    public SQLite3 $db;
    public array $BanCommand = ["hub"];
    public array $CombatTimer = [];
    public array $opponent = [];
    public array $TimerData = [];
    public array $TimerTask = [];
    public array $JumpCount = [];
    public array $targetPlayer = [];
    public array $ChatCooldown = [];
    public array $BoxingPoint = [];
    public int $MaximumCPS = 20;
    public array $ToolboxCheck = [];
    public array $PlayerDevice = [];
    public array $PlayerOS = [];
    public array $PlayerControl = [];
    public array $SkillCooldown = [];
    public int $RestartTime = 31;
    public array $ArrowOITC = [];
    public array $PlayerSkin = [];
    public array $PlayerSprint = [];
    public array $SumoArenas = [];
    public array $SumoSetup = [];
    public array $SkywarSetup = [];
    public array $SkywarArenas = [];
    public array $SumoData = [];
    public array $SkywarData = [];
    public array $buildBlocks = [];
    public array $ParkourCheckPoint = [];
    public array $ControlList = ["Unknown", "Mouse", "Touch", "Controller"];
    public array $OSList = ["Unknown", "Android", "iOS", "macOS", "FireOS", "GearVR", "HoloLens", "Windows", "Windows", "EducalVersion", "Dedicated", "PlayStation", "Switch", "XboxOne"];
    public Config $CapeData;

    public static function getInstance(): Loader
    {
        return self::$plugin;
    }

    public static function getPrefixCore(): string
    {
        return "§b§bHorizon§f » §r";
    }

    public function onLoad(): void
    {
        self::$plugin = $this;
        self::$cps = new ClickHandler();
        self::$score = new Scoreboards();
        self::$form = new FormUtils();
        self::$arenafac = new ArenaFactory();
        self::$arena = new ArenaManager();
    }

    public function onEnable(): void
    {
        ArenaUtils::getInstance()->Start();
        $this->getLogger()->info("\n\n\n              [" . TextFormat::BOLD . TextFormat::AQUA . "Horizon" . TextFormat::WHITE . "Core" . "]\n\n");
        $this->getServer()->getNetwork()->setName("§bHorizon §fNetwork");
    }

    /**
     * @throws JsonException
     */
    #[Pure] public function onDisable(): void
    {
        $reset = new MapReset();
        $reset->loadMap("BUild");
        self::$YamlLoader->saveArenas();
        $this->getLogger()->info(TextFormat::RED . "Disable HorizonCore");
    }

    /*public function deleteDir($dirPath): void
    {
        if (!is_dir($dirPath)) {
            throw new UnexpectedValueException("dirPath must be a directory");
        }
        if (!str_ends_with($dirPath, '/')) {
            $dirPath .= '/';
        }
        $files = glob($dirPath . '*', GLOB_MARK);
        foreach ($files as $file) {
            if (is_dir($file)) {
                $this->deleteDir($file);
            } else {
                unlink($file);
            }
        }
        rmdir($dirPath);
    }*/
}
