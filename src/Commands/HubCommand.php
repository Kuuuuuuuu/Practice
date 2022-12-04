<?php

declare(strict_types=1);

namespace Kuu\Commands;

use Kuu\PracticeCore;
use Kuu\PracticePlayer;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\GameMode;
use pocketmine\Server;
use pocketmine\world\World;

class HubCommand extends Command
{
    public function __construct()
    {
        parent::__construct(
            'hub',
            'Teleport to the hub',
            '/hub'
        );
    }

    public function execute(CommandSender $sender, string $commandLabel, ?array $args): void
    {
        if ($sender instanceof PracticePlayer) {
            $world = Server::getInstance()->getWorldManager()->getDefaultWorld();
            if ($world instanceof World) {
                $sender->teleport($world->getSafeSpawn());
                $sender->sendMessage(PracticeCore::getPrefixCore() . '§aTeleported to Hub!');
                $sender->setGamemode(GameMode::ADVENTURE());
                $sender->getInventory()->clearAll();
                $sender->getArmorInventory()->clearAll();
                $sender->getEffects()->clear();
                PracticeCore::getInstance()->getScoreboardManager()->setLobbyScoreboard($sender);
                PracticeCore::getPracticeUtils()->setLobbyItem($sender);
            }
        } else {
            $sender->sendMessage(PracticeCore::getPrefixCore() . '§cYou can only use this command in-game!');
        }
    }
}
