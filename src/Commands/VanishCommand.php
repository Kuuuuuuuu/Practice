<?php

namespace Kohaku\Core\Commands;

use Kohaku\Core\HorizonPlayer;
use Kohaku\Core\Loader;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\permission\DefaultPermissions;
use pocketmine\player\GameMode;

class VanishCommand extends Command
{
    public function __construct()
    {
        parent::__construct("vanish", "Vanish mode", null, ["v"]);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if ($sender->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
            if ($sender instanceof HorizonPlayer) {
                if (!$sender->vanish) {
                    $sender->vanish = true;
                    $sender->sendMessage(Loader::getPrefixCore() . "§aYou are now vanish!");
                    $sender->setGamemode(GameMode::CREATIVE());
                    $sender->getXpManager()->setCanAttractXpOrbs(false);
                } else {
                    $sender->vanish = false;
                    $sender->sendMessage(Loader::getPrefixCore() . "§aYou are now Unvanish!");
                    $sender->setGamemode(GameMode::SURVIVAL());
                    $sender->getXpManager()->setCanAttractXpOrbs(true);
                }
            }
        } else {
            $sender->sendMessage(Loader::getPrefixCore() . "§cYou don't have permission to use this command!");
        }
    }
}