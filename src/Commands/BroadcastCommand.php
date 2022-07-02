<?php

declare(strict_types=1);

namespace Kuu\Commands;

use Kuu\PracticeCore;
use pocketmine\Command\Command;
use pocketmine\command\CommandSender;
use pocketmine\permission\DefaultPermissions;
use pocketmine\player\Player;
use pocketmine\Server;

class BroadcastCommand extends Command
{

    public function __construct()
    {
        parent::__construct(
            'broadcast',
            'Broadcast a message to all players',
            '/broadcast <message>',
            ['bc']
        );
    }

    public function execute(CommandSender $sender, string $commandLabel, ?array $args): void
    {
        if ($sender instanceof Player) {
            if ($args === null) {
                $sender->sendMessage(PracticeCore::getPrefixCore() . '§cPlease enter a message');
            } elseif ($sender->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
                foreach (Server::getInstance()->getOnlinePlayers() as $player) {
                    $message = implode(' ', $args);
                    $player->sendMessage(PracticeCore::getPrefixCore() . $message);
                }
            } else {
                $sender->sendMessage(PracticeCore::getPrefixCore() . "§cYou don't have permission to use this command.");
            }
        } else {
            $sender->sendMessage(PracticeCore::getPrefixCore() . '§cYou can only use this command in-game!');
        }
    }
}