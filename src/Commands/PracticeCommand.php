<?php

declare(strict_types=1);

namespace Kuu\Commands;

use JsonException;
use Kuu\PracticeCore;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\permission\DefaultPermissions;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat as Color;

class PracticeCommand extends Command
{
    public function __construct()
    {
        parent::__construct(
            'practice',
            'Practice Command',
            '/practice help'
        );
        $this->setPermission('practice.command');
    }

    /**
     * @throws JsonException
     */
    public function execute(CommandSender $sender, string $commandLabel, ?array $args): void
    {
        if ($sender instanceof Player) {
            if (!isset($args[0])) {
                $sender->sendMessage(Color::BOLD . Color::WHITE . '>> ' . Color::RESET . Color::RED . 'use /practice help');
            } elseif ($sender->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
                switch ($args[0]) {
                    case 'help':
                        $sender->sendMessage(Color::BOLD . Color::GREEN . PracticeCore::getPrefixCore());
                        $sender->sendMessage(Color::GREEN . '/' . $commandLabel . Color::AQUA . ' make <mode> <world>' . Color::AQUA . ' - create new Arena for FFA');
                        $sender->sendMessage(Color::GREEN . '/' . $commandLabel . Color::AQUA . ' remove <mode>' . Color::AQUA . ' - delete Arena for FFA');
                        $sender->sendMessage(Color::GREEN . 'Modes: ' . Color::AQUA . 'Boxing, Nodebuff');
                        break;
                    case 'make':
                    case 'create':
                        if (!isset($args[1])) {
                            $sender->sendMessage(PracticeCore::getPrefixCore() . Color::RED . 'use /core make <mode> <world>');
                            $sender->sendMessage(Color::GREEN . 'Modes: ' . Color::AQUA . 'Boxing, Nodebuff');
                        }
                        if (!isset($args[2])) {
                            $sender->sendMessage(PracticeCore::getPrefixCore() . Color::RED . 'use /core make <mode> <world>');
                            $sender->sendMessage(Color::GREEN . 'Modes: ' . Color::AQUA . 'Boxing, Nodebuff');
                        }
                        switch ($args[1]) {
                            case 'Boxing':
                                if (file_exists(Server::getInstance()->getDataPath() . 'worlds/' . $args[2])) {
                                    Server::getInstance()->getWorldManager()->loadWorld($args[2]);
                                    /** @phpstan-ignore-next-line */
                                    $sender->teleport(Server::getInstance()->getWorldManager()->getWorldByName($args[2])?->getSafeSpawn());
                                    PracticeCore::getArenaFactory()->setBoxingArena($sender, $args[2]);
                                } else {
                                    $sender->sendMessage(Color::RED . 'World ' . $args[2] . ' not found');
                                }
                                break;
                            case 'Nodebuff':
                                if (file_exists(Server::getInstance()->getDataPath() . 'worlds/' . $args[2])) {
                                    Server::getInstance()->getWorldManager()->loadWorld($args[2]);
                                    /** @phpstan-ignore-next-line */
                                    $sender->teleport(Server::getInstance()->getWorldManager()->getWorldByName($args[2])?->getSafeSpawn());
                                    PracticeCore::getArenaFactory()->setNodebuffArena($sender, $args[2]);
                                } else {
                                    $sender->sendMessage(Color::RED . 'World ' . $args[2] . ' not found');
                                }
                                break;
                            default:
                                $sender->sendMessage(PracticeCore::getPrefixCore() . Color::RED . 'use /core make <mode> <world>');
                                $sender->sendMessage(Color::GREEN . 'Modes: ' . Color::AQUA . 'Boxing, Nodebuff');
                                break;
                        }
                        break;
                    case 'remove':
                        if (isset($args[1])) {
                            switch ($args[1]) {
                                case 'Boxing':
                                    PracticeCore::getArenaFactory()->removeBoxing($sender);
                                    break;
                                case 'Nodebuff':
                                    PracticeCore::getArenaFactory()->removeNodebuff($sender);
                                    break;
                                default:
                                    $sender->sendMessage(PracticeCore::getPrefixCore() . Color::RED . 'use /core remove <mode>');
                                    $sender->sendMessage(Color::GREEN . 'Modes: ' . Color::AQUA . 'Boxing, Nodebuff');
                                    break;
                            }
                        } else {
                            $sender->sendMessage(PracticeCore::getPrefixCore() . Color::RED . 'use /core remove <mode>');
                            $sender->sendMessage(Color::GREEN . 'Modes: ' . Color::AQUA . 'Boxing, Nodebuff');
                        }
                        break;
                    default:
                        $sender->sendMessage(PracticeCore::getPrefixCore() . '§e/practice help');
                        break;
                }
            } else {
                $sender->sendMessage(PracticeCore::getPrefixCore() . "§cYou don't have permission to use this command.");
            }
        }
    }
}
