<?php

namespace Kohaku\Core\Commands;

use Kohaku\Core\Loader;
use Kohaku\Core\Utils\ArenaUtils;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\permission\DefaultPermissions;
use pocketmine\Server;

class PlayerInfoCommand extends Command
{

    public function __construct()
    {
        parent::__construct("pinfo", "Check Player info", "/pinfo <player>", ["playerinfo", "pinfo"]);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool
    {
        if (!$sender->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
            $sender->sendMessage(Loader::getPrefixCore() . "§cYou don't have permission to use this command.");
        } else {
            if ($args == null) {
                $sender->sendMessage(Loader::getPrefixCore() . "§cUsage: /playerinfo <player>");
                return false;
            } else {
                $playerinfo = Server::getInstance()->getPlayerByPrefix($args[0]);
                $sender->sendMessage(Loader::getPrefixCore() . "§7Player: §a" . $playerinfo->getName());
                $sender->sendMessage("\n");
                $sender->sendMessage(Loader::getPrefixCore() . "§7Device: §a" . ArenaUtils::getInstance()->getPlayerDevices($playerinfo));
                $sender->sendMessage(Loader::getPrefixCore() . "§7OS: §a" . ArenaUtils::getInstance()->getPlayerOS($playerinfo));
                $sender->sendMessage(Loader::getPrefixCore() . "§7Control: §a" . ArenaUtils::getInstance()->getPlayerControls($playerinfo));
                $sender->sendMessage(Loader::getPrefixCore() . "§7Toolbox: §a" . ArenaUtils::getInstance()->getToolboxCheck($playerinfo));
            }
        }
        return true;
    }
}