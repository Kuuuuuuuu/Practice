<?php

namespace Kohaku\Core\Commands;

use Kohaku\Core\Loader;
use Kohaku\Core\Utils\ArenaUtils;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\permission\DefaultPermissions;
use pocketmine\Server;

class SetTagCommand extends Command
{

    public function __construct()
    {
        parent::__construct("settag", "settag Player", "/setTag <player> <tag>", []);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool
    {
        if (!$sender->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
            $sender->sendMessage(Loader::getPrefixCore() . "§cYou don't have permission to use this command.");
        } else {
            if ($args == null) {
                $sender->sendMessage(Loader::getPrefixCore() . "§cUsage: /setTag <player> <tag>");
                return false;
            } else {
                if (!isset($args[1])) {
                    $sender->sendMessage(Loader::getPrefixCore() . "§cUsage: /setTag <player> <tag>");
                    return false;
                }
                $playerinfo = Server::getInstance()->getPlayerByPrefix($args[0]);
                if ($playerinfo !== null) {
                    ArenaUtils::getInstance()->getData($playerinfo->getName())->setTag($args[1]);
                    $sender->sendMessage(Loader::getPrefixCore() . "§aTag set to §e" . $args[1]);
                } else {
                    $sender->sendMessage(Loader::getPrefixCore() . "§cPlayer not found.");
                }
            }
        }
        return true;
    }
}