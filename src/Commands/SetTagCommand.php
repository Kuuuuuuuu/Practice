<?php

namespace Kuu\Commands;

use Kuu\PracticeCore;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\permission\DefaultPermissions;
use pocketmine\Server;

class SetTagCommand extends Command
{

    public function __construct()
    {
        parent::__construct('settag', 'settag Player', '/setTag <player> <tag>', []);
    }

    public function execute(CommandSender $sender, string $commandLabel, ?array $args): bool
    {
        if (!$sender->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
            $sender->sendMessage(PracticeCore::getPrefixCore() . "§cYou don't have permission to use this command.");
            return false;
        }
        if (!isset($args[0])) {
            $sender->sendMessage(PracticeCore::getPrefixCore() . '§cUsage: /setTag <player> <tag>');
            return false;
        }
        if (!isset($args[1])) {
            $sender->sendMessage(PracticeCore::getPrefixCore() . '§cUsage: /setTag <player> <tag>');
            return false;
        }
        $playerinfo = Server::getInstance()->getPlayerByPrefix($args[0]);
        if ($playerinfo !== null) {
            PracticeCore::getInstance()->getArenaUtils()->getData($playerinfo->getName())->setTag($args[1]);
            $sender->sendMessage(PracticeCore::getPrefixCore() . '§aTag set to §e' . $args[1]);
        } else {
            $sender->sendMessage(PracticeCore::getPrefixCore() . '§cPlayer not found.');
        }
        return true;
    }
}