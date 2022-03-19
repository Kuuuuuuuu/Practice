<?php

declare(strict_types=1);

namespace Kohaku\Core\Commands;

use Kohaku\Core\Loader;
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
            "broadcast",
            "Broadcast a message to all players",
            "/broadcast <message>",
            ["bc"]
        );
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if ($sender instanceof Player) {
            if ($args[0] === null) {
                $sender->sendMessage(Loader::getPrefixCore() . "§cPlease enter a message");
                return;
            }
            if ($sender->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
                foreach (Server::getInstance()->getOnlinePlayers() as $player) {
                    $message = $args[0];
                    $player->sendMessage(Loader::getPrefixCore() . $message);
                }
            } else {
                $sender->sendMessage(Loader::getPrefixCore() . "§cYou don't have permission to use this command.");
            }
        } else {
            $sender->sendMessage(Loader::getPrefixCore() . "§cYou can only use this command in-game!");
        }
    }
}