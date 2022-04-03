<?php

namespace Kohaku\Core\Commands;

use Kohaku\Core\Loader;
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
                if ($playerinfo !== null) {
                    $sender->sendMessage(Loader::getPrefixCore() . "§7Player: §a" . $playerinfo->getName());
                    $sender->sendMessage("\n");
                    $sender->sendMessage(Loader::getPrefixCore() . "§7IP: §a" . $playerinfo->getNetworkSession()->getIp());
                    $sender->sendMessage(Loader::getPrefixCore() . "§7UUID: §a" . $playerinfo->getUniqueId());
                    $sender->sendMessage(Loader::getPrefixCore() . "§7Nametag: §a" . $playerinfo->getNameTag());
                    $sender->sendMessage(Loader::getPrefixCore() . "§7Device: §a" . Loader::getInstance()->getArenaUtils()->getPlayerDevices($playerinfo));
                    $sender->sendMessage(Loader::getPrefixCore() . "§7OS: §a" . Loader::getInstance()->getArenaUtils()->getPlayerOS($playerinfo));
                    $sender->sendMessage(Loader::getPrefixCore() . "§7Control: §a" . Loader::getInstance()->getArenaUtils()->getPlayerControls($playerinfo));
                    $sender->sendMessage(Loader::getPrefixCore() . "§7Toolbox: §a" . Loader::getInstance()->getArenaUtils()->getToolboxCheck($playerinfo));
                } else {
                    $sender->sendMessage(Loader::getPrefixCore() . "§cPlayer not found.");
                }
            }
        }
        return true;
    }
}