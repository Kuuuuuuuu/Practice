<?php

declare(strict_types=1);

namespace Nayuki\Commands;

use Nayuki\PracticeCore;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

final class SetTagCommand extends Command
{
    public function __construct()
    {
        parent::__construct(
            'settag',
            'settag Player',
            '/setTag <player> <tag>',
        );
        $this->setPermission('settag.command');
    }

    public function execute(CommandSender $sender, string $commandLabel, ?array $args): void
    {
        if (!isset($args[0])) {
            $message = TextFormat::RED . 'Usage: /setTag <player> <tag>';
        } elseif (!isset($args[1])) {
            $message = TextFormat::RED . 'Usage: /setTag <player> <tag>';
        } else {
            $player = PracticeCore::getSessionManager()->getPlayerInSessionByPrefix($args[0]);
            if ($player instanceof Player) {
                $session = PracticeCore::getSessionManager()->getSession($player);
                $session->setCustomTag($args[1]);
                $message = TextFormat::GREEN . 'Tag set to ' . TextFormat::YELLOW . $args[1];
            } else {
                $message = TextFormat::RED . 'Player not found.';
            }
        }
        $sender->sendMessage(PracticeCore::getPrefixCore() . $message);
    }
}
