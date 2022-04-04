<?php

/** @noinspection PhpParamsInspection */
/** @noinspection PhpPossiblePolymorphicInvocationInspection */

declare(strict_types=1);

namespace Kohaku\Commands;

use JsonException;
use Kohaku\Entity\DeathLeaderboard;
use Kohaku\Entity\KillLeaderboard;
use Kohaku\Loader;
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
                        $sender->sendMessage(Color::GREEN . "/" . $commandLabel . Color::AQUA . " addkb - removekb - setatkspd - removeatkspd - setleader - removeleader");
                        $sender->sendMessage(Color::GREEN . "Modes: " . Color::AQUA . "fist, Parkour, Boxing, Combo, Knockback, KitPVP, Resistance, OITC, SumoD, Bot, Skywars");
                        break;
                    case "make":
                    case "create":
                        if (!isset($args[1])) {
                            $sender->sendMessage(Loader::getPrefixCore() . Color::RED . "use /core make <mode> <world>");
                            $sender->sendMessage(Color::GREEN . "Modes: " . Color::AQUA . "fist, Parkour, Boxing, Combo, Knockback, KitPVP, Resistance, OITC, SumoD, Bot, Skywars");
                        }
                        if (!isset($args[2])) {
                            $sender->sendMessage(Loader::getPrefixCore() . Color::RED . "use /core make <mode> <world>");
                            $sender->sendMessage(Color::GREEN . "Modes: " . Color::AQUA . "fist, Parkour, Boxing, Combo, Knockback, KitPVP, Resistance, OITC, SumoD, Bot, Skywars");
                        }
                        switch ($args[1]) {
                            case "fist":
                                if (!file_exists(Server::getInstance()->getDataPath() . "worlds/" . $args[2])) {
                                    $sender->sendMessage(Color::RED . "World " . $args[2] . " not found");
                                } else {
                                    Server::getInstance()->getWorldManager()->loadworld($args[2]);
                                    $sender->teleport(Server::getInstance()->getWorldManager()->getWorldByName($args[2])->getSafeSpawn());
                                    Loader::getArenaFactory()->setFistArena($sender, $args[2]);
                                }
                                break;
                            case "Parkour":
                                if (!file_exists(Server::getInstance()->getDataPath() . "worlds/" . $args[2])) {
                                    $sender->sendMessage(Color::RED . "World " . $args[2] . " not found");
                                } else {
                                    Server::getInstance()->getWorldManager()->loadworld($args[2]);
                                    $sender->teleport(Server::getInstance()->getWorldManager()->getWorldByName($args[2])->getSafeSpawn());
                                    Loader::getArenaFactory()->setParkourArena($sender, $args[2]);
                                }
                                break;
                            case "Boxing":
                                if (!file_exists(Server::getInstance()->getDataPath() . "worlds/" . $args[2])) {
                                    $sender->sendMessage(Color::RED . "World " . $args[2] . " not found");
                                } else {
                                    Server::getInstance()->getWorldManager()->loadworld($args[2]);
                                    $sender->teleport(Server::getInstance()->getWorldManager()->getWorldByName($args[2])->getSafeSpawn());
                                    Loader::getArenaFactory()->setBoxingArena($sender, $args[2]);
                                }
                                break;
                            case "Combo":
                                if (!file_exists(Server::getInstance()->getDataPath() . "worlds/" . $args[2])) {
                                    $sender->sendMessage(Color::RED . "World " . $args[2] . " not found");
                                } else {
                                    Server::getInstance()->getWorldManager()->loadworld($args[2]);
                                    $sender->teleport(Server::getInstance()->getWorldManager()->getWorldByName($args[2])->getSafeSpawn());
                                    Loader::getArenaFactory()->setComboArena($sender, $args[2]);
                                }
                                break;
                            case "Knockback":
                                if (!file_exists(Server::getInstance()->getDataPath() . "worlds/" . $args[2])) {
                                    $sender->sendMessage(Color::RED . "World " . $args[2] . " not found");
                                } else {
                                    Server::getInstance()->getWorldManager()->loadworld($args[2]);
                                    $sender->teleport(Server::getInstance()->getWorldManager()->getWorldByName($args[2])->getSafeSpawn());
                                    Loader::getArenaFactory()->setKnockbackArena($sender, $args[2]);
                                }
                                break;
                            case "KitPVP":
                                if (!file_exists(Server::getInstance()->getDataPath() . "worlds/" . $args[2])) {
                                    $sender->sendMessage(Color::RED . "World " . $args[2] . " not found");
                                } else {
                                    Server::getInstance()->getWorldManager()->loadworld($args[2]);
                                    $sender->teleport(Server::getInstance()->getWorldManager()->getWorldByName($args[2])->getSafeSpawn());
                                    Loader::getArenaFactory()->setKitPVPArena($sender, $args[2]);
                                }
                                break;
                            case "Resistance":
                                if (!file_exists(Server::getInstance()->getDataPath() . "worlds/" . $args[2])) {
                                    $sender->sendMessage(Color::RED . "World " . $args[2] . " not found");
                                } else {
                                    Server::getInstance()->getWorldManager()->loadworld($args[2]);
                                    $sender->teleport(Server::getInstance()->getWorldManager()->getWorldByName($args[2])->getSafeSpawn());
                                    Loader::getArenaFactory()->setResistanceArena($sender, $args[2]);
                                }
                                break;
                            case "OITC":
                                if (!file_exists(Server::getInstance()->getDataPath() . "worlds/" . $args[2])) {
                                    $sender->sendMessage(Color::RED . "World " . $args[2] . " not found");
                                } else {
                                    Server::getInstance()->getWorldManager()->loadworld($args[2]);
                                    $sender->teleport(Server::getInstance()->getWorldManager()->getWorldByName($args[2])->getSafeSpawn());
                                    Loader::getArenaFactory()->setOITCArena($sender, $args[2]);
                                }
                                break;
                            case "SumoD":
                                if (!file_exists(Server::getInstance()->getDataPath() . "worlds/" . $args[2])) {
                                    $sender->sendMessage(Color::RED . "World " . $args[2] . " not found");
                                } else {
                                    Server::getInstance()->getWorldManager()->loadworld($args[2]);
                                    $sender->teleport(Server::getInstance()->getWorldManager()->getWorldByName($args[2])->getSafeSpawn());
                                    Loader::getArenaFactory()->setSumoD($sender, $args[2]);
                                }
                                break;
                            case "Build":
                                if (!file_exists(Server::getInstance()->getDataPath() . "worlds/" . $args[2])) {
                                    $sender->sendMessage(Color::RED . "World " . $args[2] . " not found");
                                } else {
                                    Server::getInstance()->getWorldManager()->loadworld($args[2]);
                                    $sender->teleport(Server::getInstance()->getWorldManager()->getWorldByName($args[2])->getSafeSpawn());
                                    Loader::getArenaFactory()->setBuildArena($sender, $args[2]);
                                }
                                break;
                            case "Bot":
                                if (!file_exists(Server::getInstance()->getDataPath() . "worlds/" . $args[2])) {
                                    $sender->sendMessage(Color::RED . "World " . $args[2] . " not found");
                                } else {
                                    Server::getInstance()->getWorldManager()->loadworld($args[2]);
                                    $sender->teleport(Server::getInstance()->getWorldManager()->getWorldByName($args[2])->getSafeSpawn());
                                    Loader::getArenaFactory()->setBotArena($sender, $args[2]);
                                }
                                break;
                            case "Skywars":
                                if (!file_exists(Server::getInstance()->getDataPath() . "worlds/" . $args[2])) {
                                    $sender->sendMessage(Color::RED . "World " . $args[2] . " not found");
                                } else {
                                    Server::getInstance()->getWorldManager()->loadworld($args[2]);
                                    $sender->teleport(Server::getInstance()->getWorldManager()->getWorldByName($args[2])->getSafeSpawn());
                                    Loader::getArenaFactory()->setSkywarsArena($sender, $args[2]);
                                }
                                break;
                            default:
                                $sender->sendMessage(Loader::getPrefixCore() . Color::RED . "use /core make <mode> <world>");
                                $sender->sendMessage(Color::GREEN . "Modes: " . Color::AQUA . "fist, Parkour, Boxing, Combo, Knockback, KitPVP, Resistance, OITC, SumoD, Bot, Skywars");
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
                        Loader::getKnockbackManager()->setAttackspeed($sender, $args[1], (int)$args[2]);
                        break;
                    case "removeatkspd":
                        if (!isset($args[1])) {
                            $sender->sendMessage(Loader::getPrefixCore() . Color::RED . "use /core removeatkspd <world>");
                            return false;
                        }
                        Loader::getKnockbackManager()->removeAttackspeed($sender, $args[1]);
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
                        Loader::getKnockbackManager()->setKnockback($sender, $args[1], (float)$args[2], (float)$args[3]);
                        break;
                    case "removekb":
                        if (!isset($args[1])) {
                            $sender->sendMessage(Loader::getPrefixCore() . Color::RED . "use /core removekb <world>");
                            return false;
                        }
                        Loader::getKnockbackManager()->removeKnockback($sender, $args[1]);
                        break;
                    case "remove":
                        if (!isset($args[1])) {
                            $sender->sendMessage(Loader::getPrefixCore() . Color::RED . "use /core remove <mode>");
                            $sender->sendMessage(Color::GREEN . "Modes: " . Color::AQUA . "fist, Parkour, Boxing, Combo, Knockback, KitPVP, Resistance, OITC, SumoD, Bot, Skywars");
                            return false;
                        }
                        switch ($args[1]) {
                            case "fist":
                                Loader::getArenaFactory()->removeFist($sender);
                                break;
                            case "Boxing":
                                Loader::getArenaFactory()->removeBoxing($sender);
                                break;
                            case "Parkour":
                                Loader::getArenaFactory()->removeParkour($sender);
                                break;
                            case "Combo":
                                Loader::getArenaFactory()->removeCombo($sender);
                                break;
                            case "Knockback":
                                Loader::getArenaFactory()->removeKnockback($sender);
                                break;
                            case "KitPVP":
                                Loader::getArenaFactory()->removeKitPVP($sender);
                                break;
                            case "Resistance":
                                Loader::getArenaFactory()->removeResistance($sender);
                                break;
                            case "OITC":
                                Loader::getArenaFactory()->removeOITC($sender);
                                break;
                            case "SumoD":
                                Loader::getArenaFactory()->removeSumoD($sender);
                                break;
                            case "Build":
                                Loader::getArenaFactory()->removeBuild($sender);
                                break;
                            case "Bot":
                                Loader::getArenaFactory()->removeBot($sender);
                                break;
                            case "Skywars":
                                Loader::getArenaFactory()->removeSkywars($sender);
                                break;
                            default:
                                $sender->sendMessage(Loader::getPrefixCore() . Color::RED . "use /core remove <mode>");
                                $sender->sendMessage(Color::GREEN . "Modes: " . Color::AQUA . "fist, Parkour, Boxing, Combo, Knockback, KitPVP, Resistance, OITC, SumoD, Bot, Skywars");
                                break;
                        }
                        break;
                    case "setkillleader":
                        $npc = new KillLeaderboard($sender->getLocation(), $sender->getSkin());
                        $npc->spawnToAll();
                        break;
                    case "setdeathleader":
                        $npc = new DeathLeaderboard($sender->getLocation(), $sender->getSkin());
                        $npc->spawnToAll();
                        break;
                    case "removeleader":
                        foreach (Server::getInstance()->getWorldManager()->getWorlds() as $world) {
                            foreach ($world->getEntities() as $entity) {
                                if ($entity instanceof KillLeaderboard or $entity instanceof DeathLeaderboard) {
                                    $entity->close();
                                }
                            }
                        }
                        $sender->sendMessage(Loader::getPrefixCore() . "KillLeaderboard removed!");
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
