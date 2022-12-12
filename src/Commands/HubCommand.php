<?php

declare(strict_types=1);

namespace Nayuki\Commands;

use Nayuki\PracticeCore;
use pocketmine\command\CommandSender;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\world\World;

class HubCommand extends BaseCommand
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
        if ($sender instanceof Player) {
            if ($this->isPlayerCanUseCommand($sender)) {
                $world = Server::getInstance()->getWorldManager()->getDefaultWorld();
                if ($world instanceof World) {
                    $session = PracticeCore::getPlayerSession()::getSession($sender);
                    $sender->teleport($world->getSafeSpawn());
                    $sender->sendMessage(PracticeCore::getPrefixCore() . '§aTeleported to Hub!');
                    $sender->setGamemode(GameMode::ADVENTURE());
                    $sender->getInventory()->clearAll();
                    $sender->getArmorInventory()->clearAll();
                    $sender->getEffects()->clear();
                    PracticeCore::getInstance()->getScoreboardManager()->setLobbyScoreboard($sender);
                    PracticeCore::getPracticeUtils()->setLobbyItem($sender);
                    $session->isDueling = false;
                    $session->DuelKit = null;
                    $session->BoxingPoint = 0;
                }
            }
        } else {
            $sender->sendMessage(PracticeCore::getPrefixCore() . '§cYou can only use this command in-Game!');
        }
    }
}
