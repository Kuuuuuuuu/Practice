<?php /** @noinspection PhpParamsInspection */

/** @noinspection PhpPossiblePolymorphicInvocationInspection */

declare(strict_types=1);

namespace Kohaku\Core\Commands;

use JsonException;
use Kohaku\Core\Loader;
use Kohaku\Core\Utils\KnockbackManager;
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
        } else {
            if ($sender->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
                switch ($args[0]) {
                    case "help":
                        $sender->sendMessage(Color::BOLD . Color::GREEN . Loader::getPrefixCore());
                        $sender->sendMessage(Color::GREEN . "/" . $commandLabel . Color::AQUA . " make <mode> <world>" . Color::AQUA . " - create new Arena for FFA");
                        $sender->sendMessage(Color::GREEN . "/" . $commandLabel . Color::AQUA . " remove <mode>" . Color::AQUA . " - delete Arena for FFA");
                        $sender->sendMessage(Color::GREEN . "/" . $commandLabel . Color::AQUA . " addkb - removekb - setatkspd - removeatkspd");
                        $sender->sendMessage(Color::GREEN . "Modes: " . Color::AQUA . "fist, Parkour, Boxing, Combo, Knockback, KitPVP, Resistance, OITC, SumoD");
                        break;
                    case "make":
                    case "create":
                        if (!isset($args[1])) {
                            $sender->sendMessage(Loader::getPrefixCore() . Color::RED . "use /core make <mode> <world>");
                            $sender->sendMessage(Color::GREEN . "Modes: " . Color::AQUA . "fist, Parkour, Boxing, Combo, Knockback, KitPVP, Resistance, OITC, SumoD");
                        }
                        if (!isset($args[2])) {
                            $sender->sendMessage(Loader::getPrefixCore() . Color::RED . "use /core make <mode> <world>");
                            $sender->sendMessage(Color::GREEN . "Modes: " . Color::AQUA . "fist, Parkour, Boxing, Combo, Knockback, KitPVP, Resistance, OITC, SumoD");
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
                            case "OITC":
                                if (!file_exists(Server::getInstance()->getDataPath() . "worlds/" . $args[2])) {
                                    $sender->sendMessage(Color::RED . "World " . $args[2] . " not found");
                                } else {
                                    Server::getInstance()->getWorldManager()->loadworld($args[2]);
                                    $sender->teleport(Server::getInstance()->getWorldManager()->getWorldByName($args[2])->getSafeSpawn());
                                    Loader::$arenafac->setOITCArena($sender, $args[2]);
                                }
                                break;
                            case "SumoD":
                                if (!file_exists(Server::getInstance()->getDataPath() . "worlds/" . $args[2])) {
                                    $sender->sendMessage(Color::RED . "World " . $args[2] . " not found");
                                } else {
                                    Server::getInstance()->getWorldManager()->loadworld($args[2]);
                                    $sender->teleport(Server::getInstance()->getWorldManager()->getWorldByName($args[2])->getSafeSpawn());
                                    Loader::$arenafac->setSumoD($sender, $args[2]);
                                }
                                break;
                            case "Build":
                                if (!file_exists(Server::getInstance()->getDataPath() . "worlds/" . $args[2])) {
                                    $sender->sendMessage(Color::RED . "World " . $args[2] . " not found");
                                } else {
                                    Server::getInstance()->getWorldManager()->loadworld($args[2]);
                                    $sender->teleport(Server::getInstance()->getWorldManager()->getWorldByName($args[2])->getSafeSpawn());
                                    Loader::$arenafac->setBuildArena($sender, $args[2]);
                                }
                                break;
                            default:
                                $sender->sendMessage(Loader::getPrefixCore() . Color::RED . "use /core make <mode> <world>");
                                $sender->sendMessage(Color::GREEN . "Modes: " . Color::AQUA . "fist, Parkour, Boxing, Combo, Knockback, KitPVP, Resistance, OITC, SumoD");
                                break;
                        }
                        break;
                    case "setatkspd":
                        if (!isset($args[1])) {
                            $sender->sendMessage(Loader::getPrefixCore() . Color::RED . "use /core setatkspd <world> <speed> ");
                            return false;
                        }
                        if (!isset($args[2])) {
                            $sender->sendMessage(Loader::getPrefixCore() . Color::RED . "use /core setatkspd <world> <speed>");
                            return false;
                        }
                        KnockbackManager::getInstance()->setAttackspeed($sender, $args[1], (int)$args[2]);
                        break;
                    case "removeatkspd":
                        if (!isset($args[1])) {
                            $sender->sendMessage(Loader::getPrefixCore() . Color::RED . "use /core removeatkspd <world>");
                            return false;
                        }
                        KnockbackManager::getInstance()->removeAttackspeed($sender, $args[1]);
                        break;
                    case "addkb":
                        if (!isset($args[1])) {
                            $sender->sendMessage(Loader::getPrefixCore() . Color::RED . "use /core addkb <world> <hkb> <ykb>");
                            return false;
                        }
                        if (!isset($args[2])) {
                            $sender->sendMessage(Loader::getPrefixCore() . Color::RED . "use /core addkb <world> <hkb> <ykb>");
                            return false;
                        }
                        if (!isset($args[3])) {
                            $sender->sendMessage(Loader::getPrefixCore() . Color::RED . "use /core addkb <world> <hkb> <ykb>");
                            return false;
                        }
                        KnockbackManager::getInstance()->setKnockback($sender, $args[1], (float)$args[2], (float)$args[3]);
                        break;
                    case "removekb":
                        if (!isset($args[1])) {
                            $sender->sendMessage(Loader::getPrefixCore() . Color::RED . "use /core removekb <world>");
                            return false;
                        }
                        KnockbackManager::getInstance()->removeKnockback($sender, $args[1]);
                        break;
                    case "remove":
                        if (!isset($args[1])) {
                            $sender->sendMessage(Loader::getPrefixCore() . Color::RED . "use /core remove <mode>");
                            $sender->sendMessage(Color::GREEN . "Modes: " . Color::AQUA . "fist, Parkour, Boxing, Combo, Knockback, KitPVP, Resistance, OITC, SumoD");
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
                            case "OITC":
                                Loader::$arenafac->removeOITC($sender);
                                break;
                            case "SumoD":
                                Loader::$arenafac->removeSumoD($sender);
                                break;
                            case "Build":
                                Loader::$arenafac->removeBuild($sender);
                                break;
                            default:
                                $sender->sendMessage(Loader::getPrefixCore() . Color::RED . "use /core remove <mode>");
                                $sender->sendMessage(Color::GREEN . "Modes: " . Color::AQUA . "fist, Parkour, Boxing, Combo, Knockback, KitPVP, Resistance, OITC, SumoD");
                                break;
                        }
                        break;
                    default:
                        $sender->sendMessage(Loader::getPrefixCore() . "§e/core help");
                        break;
                }
            } else {
                $sender->sendMessage(Loader::getPrefixCore() . "§cYou don't have permission to use this command.");
            }
        }
        return true;
    }
}
