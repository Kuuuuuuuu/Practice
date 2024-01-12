<?php

declare(strict_types=1);

namespace Nayuki\Commands;

use Nayuki\PracticeCore;
use Nayuki\Task\OnceRestartTask;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

final class RestartCommand extends Command
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
            $sender->sendMessage(PracticeCore::getPrefixCore() . TextFormat::RED . 'You can only use this command in-game!');
            return;
        }
        if (PracticeCore::$isRestarting) {
            $sender->sendMessage(PracticeCore::getPrefixCore() . TextFormat::RED . 'Server is already restarting!');
            return;
        }
        if (!isset($args[0]) || !is_numeric($args[0])) {
            $sender->sendMessage(PracticeCore::getPrefixCore() . TextFormat::RED . 'Usage: /restart [time]');
            return;
        }
        new OnceRestartTask((int)$args[0]);
        $sender->sendMessage(PracticeCore::getPrefixCore() . TextFormat::GREEN . 'Server restarting...');
    }
}
