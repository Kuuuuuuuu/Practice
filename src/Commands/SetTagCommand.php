<?php

declare(strict_types=1);

namespace Nayuki\Commands;

use Nayuki\PracticeCore;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\permission\DefaultPermissions;
use pocketmine\player\Player;

class SetTagCommand extends Command
{
    public function __construct()
    {
        parent::__construct(
            'settag',
            'settag Player',
            '/setTag <player> <tag>',
            []
        );
        $this->setPermission('settag.command');
    }

    public function execute(CommandSender $sender, string $commandLabel, ?array $args): void
    {
        if (!$sender->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
            $sender->sendMessage(PracticeCore::getPrefixCore() . "§cYou don't have permission to use this command.");
        } elseif (!isset($args[0])) {
            $sender->sendMessage(PracticeCore::getPrefixCore() . '§cUsage: /setTag <player> <tag>');
        } elseif (isset($args[1])) {
            $player = PracticeCore::getPracticeUtils()->getPlayerInSessionByPrefix($args[0]);
            if ($player instanceof Player) {
                $session = PracticeCore::getPlayerSession()::getSession($player);
                $session->setCustomTag($args[1]);
                $sender->sendMessage(PracticeCore::getPrefixCore() . '§aTag set to §e' . $args[1]);
            } else {
                $sender->sendMessage(PracticeCore::getPrefixCore() . '§cPlayer not found.');
            }
        } else {
            $sender->sendMessage(PracticeCore::getPrefixCore() . '§cUsage: /setTag <player> <tag>');
        }
    }
}
