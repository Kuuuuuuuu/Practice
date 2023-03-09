<?php

declare(strict_types=1);

namespace Nayuki\Commands;

use Nayuki\PracticeCore;
use Nayuki\Task\OnceRestartTask;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\permission\DefaultPermissions;
use pocketmine\player\Player;

class RestartCommand extends Command
{
    public function __construct()
    {
        parent::__construct(
            'restart',
            'Restart Server Command'
        );
        $this->setPermission('restart.command');
    }

    public function execute(CommandSender $sender, string $commandLabel, ?array $args): void
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage(PracticeCore::getPrefixCore() . '§cYou can only use this command in-Game!');
            return;
        }
        if (PracticeCore::$isRestarting) {
            $sender->sendMessage(PracticeCore::getPrefixCore() . '§cServer is already restarting!');
        } elseif (!$sender->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
            $sender->sendMessage(PracticeCore::getPrefixCore() . "§cYou don't have permission to use this command.");
            return;
        }
        if (!isset($args[0])) {
            $sender->sendMessage(PracticeCore::getPrefixCore() . '§cUsage: /restart [time]');
            return;
        }
        if (is_numeric($args[0])) {
            new OnceRestartTask((int)$args[0]);
            $sender->sendMessage(PracticeCore::getPrefixCore() . '§aServer restarting...');
        }
    }
}
