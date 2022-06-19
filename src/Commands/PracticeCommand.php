<?php

/** @noinspection PhpParamsInspection */
/** @noinspection PhpPossiblePolymorphicInvocationInspection */

declare(strict_types=1);

namespace Kuu\Commands;

use JsonException;
use Kuu\Entity\DeathLeaderboard;
use Kuu\Entity\KillLeaderboard;
use Kuu\PracticeCore;
use pocketmine\command\{Command, CommandSender};
use pocketmine\permission\DefaultPermissions;
use pocketmine\Server;
use pocketmine\utils\TextFormat as Color;

class PracticeCommand extends Command
{

    public function __construct()
    {
        parent::__construct(
            'practice',
            'Practice Command',
            '/core help'
        );
    }

    /**
     * @throws JsonException
     */
    public function execute(CommandSender $sender, string $commandLabel, ?array $args): bool
    {
        if (!isset($args[0])) {
            $sender->sendMessage(Color::BOLD . Color::WHITE . '>> ' . Color::RESET . Color::RED . 'use /core help');
        }
        if ($sender->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
            switch ($args[0]) {
                case 'help':
                    $sender->sendMessage(Color::BOLD . Color::GREEN . PracticeCore::getPrefixCore());
                    $sender->sendMessage(Color::GREEN . '/' . $commandLabel . Color::AQUA . ' make <mode> <world>' . Color::AQUA . ' - create new Arena for FFA');
                    $sender->sendMessage(Color::GREEN . '/' . $commandLabel . Color::AQUA . ' remove <mode>' . Color::AQUA . ' - delete Arena for FFA');
                    $sender->sendMessage(Color::GREEN . '/' . $commandLabel . Color::AQUA . ' addkb - removekb - setatkspd - removeatkspd - setkillleader - setdeathleader - removeleader');
                    $sender->sendMessage(Color::GREEN . 'Modes: ' . Color::AQUA . 'fist, Boxing, Combo, Knockback, KitPVP, Resistance, OITC, Bot');
                    break;
                case 'make':
                case 'create':
                    if (!isset($args[1])) {
                        $sender->sendMessage(PracticeCore::getPrefixCore() . Color::RED . 'use /core make <mode> <world>');
                        $sender->sendMessage(Color::GREEN . 'Modes: ' . Color::AQUA . 'fist, Boxing, Combo, Knockback, KitPVP, Resistance, OITC, Bot');
                    }
                    if (!isset($args[2])) {
                        $sender->sendMessage(PracticeCore::getPrefixCore() . Color::RED . 'use /core make <mode> <world>');
                        $sender->sendMessage(Color::GREEN . 'Modes: ' . Color::AQUA . 'fist, Boxing, Combo, Knockback, KitPVP, Resistance, OITC, Bot');
                    }
                    switch ($args[1]) {
                        case 'fist':
                            if (!file_exists(Server::getInstance()->getDataPath() . 'worlds/' . $args[2])) {
                                $sender->sendMessage(Color::RED . 'World ' . $args[2] . ' not found');
                            } else {
                                Server::getInstance()->getWorldManager()->loadworld($args[2]);
                                $sender->teleport(Server::getInstance()->getWorldManager()->getWorldByName($args[2])?->getSafeSpawn());
                                PracticeCore::getArenaFactory()->setFistArena($sender, $args[2]);
                            }
                            break;
                        case 'Boxing':
                            if (!file_exists(Server::getInstance()->getDataPath() . 'worlds/' . $args[2])) {
                                $sender->sendMessage(Color::RED . 'World ' . $args[2] . ' not found');
                            } else {
                                Server::getInstance()->getWorldManager()->loadworld($args[2]);
                                $sender->teleport(Server::getInstance()->getWorldManager()->getWorldByName($args[2])?->getSafeSpawn());
                                PracticeCore::getArenaFactory()->setBoxingArena($sender, $args[2]);
                            }
                            break;
                        case 'Combo':
                            if (!file_exists(Server::getInstance()->getDataPath() . 'worlds/' . $args[2])) {
                                $sender->sendMessage(Color::RED . 'World ' . $args[2] . ' not found');
                            } else {
                                Server::getInstance()->getWorldManager()->loadworld($args[2]);
                                $sender->teleport(Server::getInstance()->getWorldManager()->getWorldByName($args[2])?->getSafeSpawn());
                                PracticeCore::getArenaFactory()->setComboArena($sender, $args[2]);
                            }
                            break;
                        case 'Knockback':
                            if (!file_exists(Server::getInstance()->getDataPath() . 'worlds/' . $args[2])) {
                                $sender->sendMessage(Color::RED . 'World ' . $args[2] . ' not found');
                            } else {
                                Server::getInstance()->getWorldManager()->loadworld($args[2]);
                                $sender->teleport(Server::getInstance()->getWorldManager()->getWorldByName($args[2])?->getSafeSpawn());
                                PracticeCore::getArenaFactory()->setKnockbackArena($sender, $args[2]);
                            }
                            break;
                        case 'KitPVP':
                            if (!file_exists(Server::getInstance()->getDataPath() . 'worlds/' . $args[2])) {
                                $sender->sendMessage(Color::RED . 'World ' . $args[2] . ' not found');
                            } else {
                                Server::getInstance()->getWorldManager()->loadworld($args[2]);
                                $sender->teleport(Server::getInstance()->getWorldManager()->getWorldByName($args[2])?->getSafeSpawn());
                                PracticeCore::getArenaFactory()->setKitPVPArena($sender, $args[2]);
                            }
                            break;
                        case 'Resistance':
                            if (!file_exists(Server::getInstance()->getDataPath() . 'worlds/' . $args[2])) {
                                $sender->sendMessage(Color::RED . 'World ' . $args[2] . ' not found');
                            } else {
                                Server::getInstance()->getWorldManager()->loadworld($args[2]);
                                $sender->teleport(Server::getInstance()->getWorldManager()->getWorldByName($args[2])?->getSafeSpawn());
                                PracticeCore::getArenaFactory()->setResistanceArena($sender, $args[2]);
                            }
                            break;
                        case 'OITC':
                            if (!file_exists(Server::getInstance()->getDataPath() . 'worlds/' . $args[2])) {
                                $sender->sendMessage(Color::RED . 'World ' . $args[2] . ' not found');
                            } else {
                                Server::getInstance()->getWorldManager()->loadworld($args[2]);
                                $sender->teleport(Server::getInstance()->getWorldManager()->getWorldByName($args[2])?->getSafeSpawn());
                                PracticeCore::getArenaFactory()->setOITCArena($sender, $args[2]);
                            }
                            break;
                        case 'Build':
                            if (!file_exists(Server::getInstance()->getDataPath() . 'worlds/' . $args[2])) {
                                $sender->sendMessage(Color::RED . 'World ' . $args[2] . ' not found');
                            } else {
                                Server::getInstance()->getWorldManager()->loadworld($args[2]);
                                $sender->teleport(Server::getInstance()->getWorldManager()->getWorldByName($args[2])?->getSafeSpawn());
                                PracticeCore::getArenaFactory()->setBuildArena($sender, $args[2]);
                            }
                            break;
                        case 'Bot':
                            if (!file_exists(Server::getInstance()->getDataPath() . 'worlds/' . $args[2])) {
                                $sender->sendMessage(Color::RED . 'World ' . $args[2] . ' not found');
                            } else {
                                Server::getInstance()->getWorldManager()->loadworld($args[2]);
                                $sender->teleport(Server::getInstance()->getWorldManager()->getWorldByName($args[2])?->getSafeSpawn());
                                PracticeCore::getArenaFactory()->setBotArena($sender, $args[2]);
                            }
                            break;
                        default:
                            $sender->sendMessage(PracticeCore::getPrefixCore() . Color::RED . 'use /core make <mode> <world>');
                            $sender->sendMessage(Color::GREEN . 'Modes: ' . Color::AQUA . 'fist, Boxing, Combo, Knockback, KitPVP, Resistance, OITC, Bot');
                            break;
                    }
                    break;
                case 'setatkspd':
                    if (!isset($args[1])) {
                        $sender->sendMessage(PracticeCore::getPrefixCore() . Color::RED . 'use /core setatkspd <world> <speed> ');
                        return false;
                    }
                    if (!isset($args[2])) {
                        $sender->sendMessage(PracticeCore::getPrefixCore() . Color::RED . 'use /core setatkspd <world> <speed>');
                        return false;
                    }
                    PracticeCore::getKnockbackManager()->setAttackspeed($sender, mb_strtolower($args[1]), (int)$args[2]);
                    break;
                case 'removeatkspd':
                    if (!isset($args[1])) {
                        $sender->sendMessage(PracticeCore::getPrefixCore() . Color::RED . 'use /core removeatkspd <world>');
                        return false;
                    }
                    PracticeCore::getKnockbackManager()->removeAttackspeed($sender, mb_strtolower($args[1]));
                    break;
                case 'addkb':
                    if (!isset($args[1])) {
                        $sender->sendMessage(PracticeCore::getPrefixCore() . Color::RED . 'use /core addkb <world> <hkb> <ykb>');
                        return false;
                    }
                    if (!isset($args[2])) {
                        $sender->sendMessage(PracticeCore::getPrefixCore() . Color::RED . 'use /core addkb <world> <hkb> <ykb>');
                        return false;
                    }
                    if (!isset($args[3])) {
                        $sender->sendMessage(PracticeCore::getPrefixCore() . Color::RED . 'use /core addkb <world> <hkb> <ykb>');
                        return false;
                    }
                    PracticeCore::getKnockbackManager()->setKnockback($sender, mb_strtolower($args[1]), (float)$args[2], (float)$args[3]);
                    break;
                case 'removekb':
                    if (!isset($args[1])) {
                        $sender->sendMessage(PracticeCore::getPrefixCore() . Color::RED . 'use /core removekb <world>');
                        return false;
                    }
                    PracticeCore::getKnockbackManager()->removeKnockback($sender, mb_strtolower($args[1]));
                    break;
                case 'remove':
                    if (!isset($args[1])) {
                        $sender->sendMessage(PracticeCore::getPrefixCore() . Color::RED . 'use /core remove <mode>');
                        $sender->sendMessage(Color::GREEN . 'Modes: ' . Color::AQUA . 'fist, Boxing, Combo, Knockback, KitPVP, Resistance, OITC, Bot');
                        return false;
                    }
                    switch ($args[1]) {
                        case 'fist':
                            PracticeCore::getArenaFactory()->removeFist($sender);
                            break;
                        case 'Boxing':
                            PracticeCore::getArenaFactory()->removeBoxing($sender);
                            break;
                        case 'Combo':
                            PracticeCore::getArenaFactory()->removeCombo($sender);
                            break;
                        case 'Knockback':
                            PracticeCore::getArenaFactory()->removeKnockback($sender);
                            break;
                        case 'KitPVP':
                            PracticeCore::getArenaFactory()->removeKitPVP($sender);
                            break;
                        case 'Resistance':
                            PracticeCore::getArenaFactory()->removeResistance($sender);
                            break;
                        case 'OITC':
                            PracticeCore::getArenaFactory()->removeOITC($sender);
                            break;
                        case 'Build':
                            PracticeCore::getArenaFactory()->removeBuild($sender);
                            break;
                        case 'Bot':
                            PracticeCore::getArenaFactory()->removeBot($sender);
                            break;
                        default:
                            $sender->sendMessage(PracticeCore::getPrefixCore() . Color::RED . 'use /core remove <mode>');
                            $sender->sendMessage(Color::GREEN . 'Modes: ' . Color::AQUA . 'fist, Boxing, Combo, Knockback, KitPVP, Resistance, OITC, Bot');
                            break;
                    }
                    break;
                case 'setkillleader':
                    $npc = new KillLeaderboard($sender->getLocation(), $sender->getSkin());
                    $npc->spawnToAll();
                    break;
                case 'setdeathleader':
                    $npc = new DeathLeaderboard($sender->getLocation(), $sender->getSkin());
                    $npc->spawnToAll();
                    break;
                case 'removeleader':
                    foreach (Server::getInstance()->getWorldManager()->getWorlds() as $world) {
                        foreach ($world->getEntities() as $entity) {
                            if ($entity instanceof KillLeaderboard || $entity instanceof DeathLeaderboard) {
                                $entity->close();
                            }
                        }
                    }
                    $sender->sendMessage(PracticeCore::getPrefixCore() . 'KillLeaderboard removed!');
                    break;
                default:
                    $sender->sendMessage(PracticeCore::getPrefixCore() . '§e/core help');
                    break;
            }
        } else {
            $sender->sendMessage(PracticeCore::getPrefixCore() . "§cYou don't have permission to use this command.");
        }
        return true;
    }
}
