<?php

declare(strict_types=1);

namespace Kohaku\Commands;

use Kohaku\Loader;
use Kohaku\NeptunePlayer;
use pocketmine\Command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\GameMode;
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
        if ($sender instanceof NeptunePlayer) {
            if ($sender->EditKit !== null) {
                $sender->sendMessage(Loader::getPrefixCore() . "§cYou can't use this command while editing a kit!");
                return;
            }
            $sender->teleport(Server::getInstance()->getWorldManager()->getDefaultWorld()->getSafeSpawn());
            $sender->sendMessage(Loader::getPrefixCore() . "§aTeleported to Hub!");
            $sender->setGamemode(GameMode::ADVENTURE());
            $sender->getInventory()->clearAll();
            $sender->getArmorInventory()->clearAll();
            $sender->getEffects()->clear();
            Loader::getInstance()->getScoreboardManager()->sb($sender);
            Loader::getInstance()->getArenaUtils()->GiveItem($sender);
            if ($sender->isImmobile()) {
                $sender->setImmobile(false);
            }
        } else {
            $sender->sendMessage(Loader::getPrefixCore() . "§cYou can only use this command in-game!");
        }
    }
}