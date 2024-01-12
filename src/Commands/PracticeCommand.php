<?php

declare(strict_types=1);

namespace Nayuki\Commands;

use JsonException;
use Nayuki\PracticeCore;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use pocketmine\utils\TextFormat as Color;

final class PracticeCommand extends Command
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
        if (!$sender instanceof Player) {
            $sender->sendMessage(PracticeCore::getPrefixCore() . TextFormat::RED . 'You can only use this command in-Game!');
            return;
        }
        if (!isset($args[0])) {
            $sender->sendMessage(Color::BOLD . Color::WHITE . '>> ' . Color::RESET . Color::RED . 'use /practice help');
            return;
        }
        switch ($args[0]) {
            case 'help':
                $sender->sendMessage(Color::BOLD . Color::GREEN . PracticeCore::getPrefixCore());
                $sender->sendMessage(Color::GREEN . '/' . $commandLabel . Color::AQUA . ' make <mode> <world>' . Color::AQUA . ' - create new Arena for FFA');
                $sender->sendMessage(Color::GREEN . '/' . $commandLabel . Color::AQUA . ' remove <mode>' . Color::AQUA . ' - delete Arena for FFA');
                $sender->sendMessage(Color::GREEN . 'Modes: ' . Color::AQUA . 'Fist, Resistance');
                break;
            case 'make':
            case 'create':
                if (!isset($args[1], $args[2])) {
                    $sender->sendMessage(PracticeCore::getPrefixCore() . Color::RED . 'use /core make <mode> <world>');
                    $sender->sendMessage(Color::GREEN . 'Modes: ' . Color::AQUA . 'Fist, Resistance');
                    return;
                }
                $arenaTypes = ['Fist', 'Resistance'];
                $worldPath = Server::getInstance()->getDataPath() . 'worlds/' . $args[2];
                if (in_array($args[1], $arenaTypes) && file_exists($worldPath)) {
                    Server::getInstance()->getWorldManager()->loadWorld($args[2]);
                    /** @phpstan-ignore-next-line */
                    $sender->teleport(Server::getInstance()->getWorldManager()->getWorldByName($args[2])?->getSafeSpawn());
                    PracticeCore::getArenaFactory()->setArenas($sender, $args[1], $args[2]);
                } else {
                    $sender->sendMessage(Color::RED . 'World ' . $args[2] . ' not found');
                }
                break;
            case 'remove':
                if (isset($args[1])) {
                    switch ($args[1]) {
                        case 'Resistance':
                            PracticeCore::getArenaFactory()->removeArenas($sender, $args[1]);
                            break;
                        default:
                            $sender->sendMessage(PracticeCore::getPrefixCore() . Color::RED . 'use /core remove <mode>');
                            $sender->sendMessage(Color::GREEN . 'Modes: ' . Color::AQUA . 'Fist, Resistance');
                            break;
                    }
                } else {
                    $sender->sendMessage(PracticeCore::getPrefixCore() . Color::RED . 'use /core remove <mode>');
                    $sender->sendMessage(Color::GREEN . 'Modes: ' . Color::AQUA . 'Fist, Resistance');
                }
                break;
            default:
                $sender->sendMessage(PracticeCore::getPrefixCore() . TextFormat::YELLOW . '/practice help');
                break;
        }
    }
}
