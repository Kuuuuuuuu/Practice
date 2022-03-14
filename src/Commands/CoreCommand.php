<?php /** @noinspection PhpParamsInspection */

/** @noinspection PhpPossiblePolymorphicInvocationInspection */

declare(strict_types=1);

namespace Kohaku\Core\Commands;

use JsonException;
use Kohaku\Core\Loader;
use pocketmine\command\{Command, CommandSender};
use pocketmine\permission\DefaultPermissions;
use pocketmine\Server;
use pocketmine\utils\TextFormat as Color;

class CoreCommand extends Command
{

    public function __construct()
    {
        parent::__construct(
            "core",
            "HorizonCore Commands",
            "/core help",
            ["horizon"]
        );
    }

    /**
     * @throws JsonException
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): bool
    {
        if (!isset($args[0])) {
            $sender->sendMessage(Color::BOLD . Color::WHITE . ">> " . Color::RESET . Color::RED . "use /core help");
            return false;
        }
        switch ($args[0]) {
            case "help":
                if (!$sender->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
                    $sender->sendMessage(Color::BOLD . ">> " . Color::RESET . Color::RED . "You dont have enough permissions to use this command");
                    return false;
                }
                $sender->sendMessage(Color::BOLD . Color::GREEN . Loader::getInstance()->getPrefixCore());
                $sender->sendMessage(Color::GREEN . "/" . $commandLabel . Color::AQUA . " make <mode> <world>" . Color::AQUA . " - create new Arena for FFA");
                $sender->sendMessage(Color::GREEN . "/" . $commandLabel . Color::AQUA . " spawn <mode>" . Color::AQUA . " - set spawn Arena for FFA");
                $sender->sendMessage(Color::GREEN . "/" . $commandLabel . Color::AQUA . " remove <mode>" . Color::AQUA . " - delete Arena for FFA");
                $sender->sendMessage(Color::GREEN . "Modes: " . Color::AQUA . "fist, Parkour, Boxing, Combo, Knockback, KitPVP, Resistance");
                break;
            case "make":
            case "create":
                if (!$sender->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
                    $sender->sendMessage(Color::BOLD . ">> " . Color::RESET . Color::RED . "You dont have enough permissions to use this command");
                    return false;
                }
                if (!isset($args[1])) {
                    $sender->sendMessage(Loader::getInstance()->getPrefixCore() . Color::RED . "use /core make <mode> <world>");
                    $sender->sendMessage(Color::GREEN . "Modes: " . Color::AQUA . "fist, Parkour, Boxing, Combo, Knockback, KitPVP, Resistance");
                    return false;
                }
                if (!isset($args[2])) {
                    $sender->sendMessage(Loader::getInstance()->getPrefixCore() . Color::RED . "use /core make <mode> <world>");
                    $sender->sendMessage(Color::GREEN . "Modes: " . Color::AQUA . "fist, Parkour, Boxing, Combo, Knockback, KitPVP, Resistance");
                    return false;
                }
                switch ($args[1]) {
                    case "fist":
                        if (!file_exists(Server::getInstance()->getDataPath() . "worlds/" . $args[2])) {
                            $sender->sendMessage(Color::RED . "World " . $args[2] . " not found");
                        } else {
                            Server::getInstance()->getWorldManager()->loadworld($args[2]);
                            $sender->teleport(Server::getInstance()->getWorldManager()->getWorldByName($args[2])->getSafeSpawn());
                            Loader::$arenafac->setFistArena($sender, $args[2]);
                        }
                        break;
                    case "Parkour":
                        if (!file_exists(Server::getInstance()->getDataPath() . "worlds/" . $args[2])) {
                            $sender->sendMessage(Color::RED . "World " . $args[2] . " not found");
                        } else {
                            Server::getInstance()->getWorldManager()->loadworld($args[2]);
                            $sender->teleport(Server::getInstance()->getWorldManager()->getWorldByName($args[2])->getSafeSpawn());
                            Loader::$arenafac->setParkourArena($sender, $args[2]);
                        }
                        break;
                    case "Boxing":
                        if (!file_exists(Server::getInstance()->getDataPath() . "worlds/" . $args[2])) {
                            $sender->sendMessage(Color::RED . "World " . $args[2] . " not found");
                        } else {
                            Server::getInstance()->getWorldManager()->loadworld($args[2]);
                            $sender->teleport(Server::getInstance()->getWorldManager()->getWorldByName($args[2])->getSafeSpawn());
                            Loader::$arenafac->setBoxingArena($sender, $args[2]);
                        }
                        break;
                    case "Combo":
                        if (!file_exists(Server::getInstance()->getDataPath() . "worlds/" . $args[2])) {
                            $sender->sendMessage(Color::RED . "World " . $args[2] . " not found");
                        } else {
                            Server::getInstance()->getWorldManager()->loadworld($args[2]);
                            $sender->teleport(Server::getInstance()->getWorldManager()->getWorldByName($args[2])->getSafeSpawn());
                            Loader::$arenafac->setComboArena($sender, $args[2]);
                        }
                        break;
                    case "Knockback":
                        if (!file_exists(Server::getInstance()->getDataPath() . "worlds/" . $args[2])) {
                            $sender->sendMessage(Color::RED . "World " . $args[2] . " not found");
                        } else {
                            Server::getInstance()->getWorldManager()->loadworld($args[2]);
                            $sender->teleport(Server::getInstance()->getWorldManager()->getWorldByName($args[2])->getSafeSpawn());
                            Loader::$arenafac->setKnockbackArena($sender, $args[2]);
                        }
                        break;
                    case "KitPVP":
                        if (!file_exists(Server::getInstance()->getDataPath() . "worlds/" . $args[2])) {
                            $sender->sendMessage(Color::RED . "World " . $args[2] . " not found");
                        } else {
                            Server::getInstance()->getWorldManager()->loadworld($args[2]);
                            $sender->teleport(Server::getInstance()->getWorldManager()->getWorldByName($args[2])->getSafeSpawn());
                            Loader::$arenafac->setKitPVPArena($sender, $args[2]);
                        }
                        break;
                    case "Resistance":
                        if (!file_exists(Server::getInstance()->getDataPath() . "worlds/" . $args[2])) {
                            $sender->sendMessage(Color::RED . "World " . $args[2] . " not found");
                        } else {
                            Server::getInstance()->getWorldManager()->loadworld($args[2]);
                            $sender->teleport(Server::getInstance()->getWorldManager()->getWorldByName($args[2])->getSafeSpawn());
                            Loader::$arenafac->setResistanceArena($sender, $args[2]);
                        }
                        break;
                    default:
                        $sender->sendMessage(Loader::getInstance()->getPrefixCore() . Color::RED . "use /core make <mode> <world>");
                        $sender->sendMessage(Color::GREEN . "Modes: " . Color::AQUA . "fist, Parkour, Boxing, Combo, Knockback, KitPVP, Resistance");
                        break;
                }
                break;
            case "remove":
                if (!$sender->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
                    $sender->sendMessage(Color::BOLD . ">> " . Color::RESET . Color::RED . "You dont have enough permissions to use this command");
                    return false;
                }
                if (!isset($args[1])) {
                    $sender->sendMessage(Loader::getInstance()->getPrefixCore() . Color::RED . "use /core remove <mode>");
                    $sender->sendMessage(Color::GREEN . "Modes: " . Color::AQUA . "fist, Parkour, Boxing, Combo, Knockback, KitPVP, Resistance");
                    return false;
                }
                switch ($args[1]) {
                    case "fist":
                        Loader::$arenafac->removeFist($sender);
                        break;
                    case "Boxing":
                        Loader::$arenafac->removeBoxing($sender);
                        break;
                    case "Parkour":
                        Loader::$arenafac->removeParkour($sender);
                        break;
                    case "Combo":
                        Loader::$arenafac->removeCombo($sender);
                        break;
                    case "Knockback":
                        Loader::$arenafac->removeKnockback($sender);
                        break;
                    case "KitPVP":
                        Loader::$arenafac->removeKitPVP($sender);
                        break;
                    case "Resistance":
                        Loader::$arenafac->removeResistance($sender);
                        break;
                    default:
                        $sender->sendMessage(Loader::getInstance()->getPrefixCore() . Color::RED . "use /core remove <mode>");
                        $sender->sendMessage(Color::GREEN . "Modes: " . Color::AQUA . "fist, Parkour, Boxing, Combo, Knockback, KitPVP, Resistance");
                        break;
                }
                break;
            default:
                $sender->sendMessage(Loader::getInstance()->getPrefixCore() . "§e/core help");
                break;
        }
        return true;
    }
}
