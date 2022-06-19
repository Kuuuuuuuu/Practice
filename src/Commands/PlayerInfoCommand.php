<?php

namespace Kuu\Commands;

use Kuu\PracticeCore;
use Kuu\PracticePlayer;
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

    public function execute(CommandSender $sender, string $commandLabel, ?array $args): bool
    {
        if (!$sender->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
            $sender->sendMessage(PracticeCore::getPrefixCore() . "§cYou don't have permission to use this command.");
            return false;
        }
        if ($args === null) {
            $sender->sendMessage(PracticeCore::getPrefixCore() . '§cUsage: /playerinfo <player>');
            return false;
        }
        $playerinfo = Server::getInstance()->getPlayerByPrefix($args[0]);
        if ($playerinfo !== null) {
            /* @var $playerinfo PracticePlayer */
            $sender->sendMessage(PracticeCore::getPrefixCore() . '§7Player: §a' . $playerinfo->getName());
            $sender->sendMessage("\n");
            $sender->sendMessage(PracticeCore::getPrefixCore() . '§7IP: §a' . $playerinfo->getNetworkSession()->getIp());
            $sender->sendMessage(PracticeCore::getPrefixCore() . '§7UUID: §a' . $playerinfo->getUniqueId());
            $sender->sendMessage(PracticeCore::getPrefixCore() . '§7Nametag: §a' . $playerinfo->getNameTag());
            $sender->sendMessage(PracticeCore::getPrefixCore() . '§7Device: §a' . $playerinfo->PlayerDevice);
            $sender->sendMessage(PracticeCore::getPrefixCore() . '§7OS: §a' . $playerinfo->PlayerOS);
            $sender->sendMessage(PracticeCore::getPrefixCore() . '§7Control: §a' . $playerinfo->PlayerControl);
            $sender->sendMessage(PracticeCore::getPrefixCore() . '§7Toolbox: §a' . $playerinfo->ToolboxStatus);
        } else {
            $sender->sendMessage(PracticeCore::getPrefixCore() . '§cPlayer not found.');
        }
        return true;
    }
}