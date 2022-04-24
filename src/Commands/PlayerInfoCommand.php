<?php

namespace Kuu\Commands;

use Kuu\Loader;
use Kuu\NeptunePlayer;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\permission\DefaultPermissions;
use pocketmine\Server;

class PlayerInfoCommand extends Command
{

    public function __construct()
    {
        parent::__construct('pinfo', 'Check Player info', '/pinfo <player>', ['playerinfo', 'pinfo']);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool
    {
        if (!$sender->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
            $sender->sendMessage(Loader::getPrefixCore() . "§cYou don't have permission to use this command.");
            return false;
        }
        if ($args == null) {
            $sender->sendMessage(Loader::getPrefixCore() . '§cUsage: /playerinfo <player>');
            return false;
        } else {
            $playerinfo = Server::getInstance()->getPlayerByPrefix($args[0]);
            if ($playerinfo !== null) {
                /* @var $playerinfo NeptunePlayer */
                $sender->sendMessage(Loader::getPrefixCore() . '§7Player: §a' . $playerinfo->getName());
                $sender->sendMessage("\n");
                $sender->sendMessage(Loader::getPrefixCore() . '§7IP: §a' . $playerinfo->getNetworkSession()->getIp());
                $sender->sendMessage(Loader::getPrefixCore() . '§7UUID: §a' . $playerinfo->getUniqueId());
                $sender->sendMessage(Loader::getPrefixCore() . '§7Nametag: §a' . $playerinfo->getNameTag());
                $sender->sendMessage(Loader::getPrefixCore() . '§7Device: §a' . $playerinfo->PlayerDevice);
                $sender->sendMessage(Loader::getPrefixCore() . '§7OS: §a' . $playerinfo->PlayerOS);
                $sender->sendMessage(Loader::getPrefixCore() . '§7Control: §a' . $playerinfo->PlayerControl);
                $sender->sendMessage(Loader::getPrefixCore() . '§7Toolbox: §a' . $playerinfo->ToolboxStatus);
            } else {
                $sender->sendMessage(Loader::getPrefixCore() . '§cPlayer not found.');
            }
        }
        return true;
    }
}