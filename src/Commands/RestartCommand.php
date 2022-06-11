<?php

declare(strict_types=1);

namespace Kuu\Commands;

use Kuu\Loader;
use Kuu\Task\OnceRestartTask;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\permission\DefaultPermissions;
use pocketmine\player\Player;

class RestartCommand extends Command
{

    public function __construct()
    {
        parent::__construct('Restart', 'Restart Server Command', null, ['restart']);
    }

    public function execute(CommandSender $sender, string $commandLabel, ?array $args)
    {
        if ($sender instanceof Player) {
            if (Loader::getInstance()->Restarted) {
                $sender->sendMessage(Loader::getPrefixCore() . '§cServer is already restarting!');
                return;
            }
            if ($sender->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
                if (count($args) > 0) {
                    if (is_numeric($args[0])) {
                        Loader::getInstance()->getScheduler()->scheduleRepeatingTask(new OnceRestartTask((int)$args[0]), 20);
                        $sender->sendMessage(Loader::getPrefixCore() . '§aServer restarting...');
                    }
                } else {
                    $sender->sendMessage(Loader::getPrefixCore() . '§cUsage: /restart [time]');
                }
            } else {
                $sender->sendMessage(Loader::getPrefixCore() . "§cYou don't have permission to use this command.");
            }
        } else {
            $sender->sendMessage(Loader::getPrefixCore() . '§cYou can only use this command in-game!');
        }
    }
}