<?php

declare(strict_types=1);

namespace Kohaku\Core\Commands;

use Kohaku\Core\Loader;
use Kohaku\Core\Utils\ArenaUtils;
use pocketmine\Command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\Server;

class HubCommand extends Command
{

    public function __construct()
    {
        parent::__construct(
            "hub",
            "Teleport to the hub",
            "/hub"
        );
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if ($sender instanceof Player) {
            $sender->teleport(Server::getInstance()->getWorldManager()->getDefaultWorld()->getSpawnLocation());
            $sender->sendMessage(Loader::getInstance()->getPrefixCore() . "§aTeleported to Hub!");
            $sender->getInventory()->clearAll();
            $sender->getArmorInventory()->clearAll();
            $sender->getEffects()->clear();
            ArenaUtils::getInstance()->GiveItem($sender);
        } else {
            $sender->sendMessage(Loader::getInstance()->getPrefixCore() . "§cYou can only use this command in-game!");
        }
    }
}