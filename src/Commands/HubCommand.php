<?php

declare(strict_types=1);

namespace Kuu\Commands;

use Kuu\PracticeCore;
use Kuu\PracticePlayer;
use pocketmine\Command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\GameMode;
use pocketmine\Server;

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
            if ($sender->getEditKit() !== null) {
                $sender->sendMessage(PracticeCore::getPrefixCore() . "§cYou can't use this command while editing a kit!");
            } else {
                $sender->teleport(Server::getInstance()->getWorldManager()->getDefaultWorld()?->getSafeSpawn());
                $sender->sendMessage(PracticeCore::getPrefixCore() . '§aTeleported to Hub!');
                $sender->setGamemode(GameMode::ADVENTURE());
                $sender->getInventory()->clearAll();
                $sender->getArmorInventory()->clearAll();
                $sender->getEffects()->clear();
                PracticeCore::getInstance()->getScoreboardManager()->sb($sender);
                PracticeCore::getInstance()->getPracticeUtils()->GiveLobbyItem($sender);
                $sender->setLastDamagePlayer('Unknown');
                if ($sender->isImmobile()) {
                    $sender->setImmobile(false);
                }
            }
        } else {
            $sender->sendMessage(PracticeCore::getPrefixCore() . '§cYou can only use this command in-game!');
        }
    }
}