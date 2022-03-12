<?php

/** @noinspection PhpMethodParametersCountMismatchInspection */
/** @noinspection PhpMissingFieldTypeInspection */
/** @noinspection PhpParamsInspection */

declare(strict_types=1);

namespace Kohaku\Core;

use JetBrains\PhpStorm\Pure;
use Kohaku\Core\Arena\ArenaFactory;
use Kohaku\Core\Arena\ArenaManager;
use Kohaku\Core\Utils\ArenaUtils;
use Kohaku\Core\Utils\CpsCounter;
use Kohaku\Core\Utils\FormUtils;
use Kohaku\Core\utils\Scoreboards;
use Kohaku\Core\Utils\SumoYamlProvider;
use pocketmine\network\mcpe\raklib\RakLibInterface;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class Loader extends PluginBase
{
    public static self $plugin;
    public static ?CpsCounter $cps;
    public static ?Scoreboards $score;
    public static ?FormUtils $form;
    public static ?ArenaFactory $arenafac;
    public static ?ArenaManager $arena;
    private static ?SumoYamlProvider $SumoLoader;
    public $message;
    public \SQLite3 $db;
    public array $BanCommand = ["hub"];
    public string $CantUseWhenCombat = "§b§bHorizon§f » §r§cYou can't use this command while in combat!";
    public string $StartCombatMessage = "§b§bHorizon§f » §r§aYou Started combat!";
    public string $ClearCombatMessage = "§b§bHorizon§f » §r§aYou Cleared combat!";
    public string $StartSkillMessage = "§b§bHorizon§f » §r§aYou Use Skills!";
    public string $NoPlayer = "§b§bHorizon§f » §r§cPlayer not found!";
    public string $ClearSkillMessage = "§b§bHorizon§f » §r§aYou Cleared your Skills Cooldown!";
    public array $CombatTimer = [];
    public array $opponent = [];
    public array $Sprinting = [];
    public array $TimerData = [];
    public array $TimerTask = [];
    public array $JumpCount = [];
    public array $targetPlayer = [];
    public array $ChatCooldown = [];
    public int $ChatCooldownSec = 1;
    public string $ChatCooldownMessage = "§b§bHorizon§f » §r§cYou can't chat for {cooldown} seconds!";
    public array $BoxingPoint = [];
    public array $AutoClickWarn = [];
    public int $MaximumCPS = 20;
    public array $ClearChunksWorlds = [];
    public string $AntiCheatName = "§bGuardian §f» ";
    public array $SumoTimer = [];
    public array $ToolboxCheck = [];
    public array $PlayerDevice = [];
    public array $PlayerOS = [];
    public array $PlayerControl = [];
    public array $SkillCooldown = [];
    public array $LeapCooldown = [];
    public array $SumoArena = [];
    public array $SumoSetup = [];
    public array $SumoData = [];
    public array $inSumo = [];
    public array $SkinCooldown = [];
    public array $PlayerSleep = [];
    public int $RestartTime = 31;
    public array $ControlList = ["Unknown", "Mouse", "Touch", "Controller"];
    public array $OSList = ["Unknown", "Android", "iOS", "macOS", "FireOS", "GearVR", "HoloLens", "Windows", "Windows", "EducalVersion", "Dedicated", "PlayStation", "Switch", "XboxOne"];

    public static function getInstance(): Loader
    {
        return self::$plugin;
    }

    public function getPrefixCore(): string
    {
        return "§b§bHorizon§f » §r";
    }

    public function onLoad(): void
    {
        self::$plugin = $this;
        self::$cps = new CpsCounter();
        self::$score = new Scoreboards();
        self::$form = new FormUtils();
        self::$arenafac = new ArenaFactory();
        self::$arena = new ArenaManager();
        self::$SumoLoader = new SumoYamlProvider();
    }

    public function onEnable(): void
    {
        foreach (Server::getInstance()->getNetwork()->getInterfaces() as $interface) {
            if ($interface instanceof RakLibInterface) {
                $interface->setPacketLimit(9999999999);
            }
        }
        ArenaUtils::getInstance()->Start();
        self::$SumoLoader->loadArenas();
        $this->saveResource("config.yml");
        $this->getLogger()->info("\n\n\n              [" . TextFormat::BOLD . TextFormat::AQUA . "Horizon" . TextFormat::WHITE . "Core" . "]\n\n");
        $this->getServer()->getNetwork()->setName("§bHorizon §fNetwork");
    }

    /**
     * @throws \JsonException
     */
    #[Pure] public function onDisable(): void
    {
        self::$SumoLoader->saveArenas();
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
