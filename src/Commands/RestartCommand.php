<?php

declare(strict_types=1);

namespace Kohaku\Core\Commands;

use Kohaku\Core\Loader;
use Kohaku\Core\Task\RestartTask;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\permission\DefaultPermissions;
use pocketmine\player\Player;

class RestartCommand extends Command
{

    public function __construct()
    {
        parent::__construct("Restart", "Restart Server Command", null, ["restart"]);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if ($sender instanceof Player) {
            if ($sender->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
                Loader::getInstance()->getScheduler()->scheduleRepeatingTask(new RestartTask(), 20);
                $sender->sendMessage(Loader::getInstance()->getPrefixCore() . "§aServer restarting...");
            } else {
                $sender->sendMessage(Loader::getInstance()->getPrefixCore() . "§cYou don't have permission to use this command.");
            }
        } else {
            $sender->sendMessage(Loader::getInstance()->getPrefixCore() . "§cYou can only use this command in-game!");
        }
    }
}