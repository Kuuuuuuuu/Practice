<?php

declare(strict_types=1);

namespace Nayuki\Commands;

use Nayuki\PracticeCore;
use pocketmine\command\CommandSender;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use pocketmine\world\World;

final class HubCommand extends BaseCommand
{
    public function __construct()
    {
        parent::__construct(
            'hub',
            'Teleport to the hub',
            '/hub',
            ['lobby', 'spawn'],
        );
        $this->setPermission('default.command');
    }

    public function execute(CommandSender $sender, string $commandLabel, ?array $args): void
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage(PracticeCore::getPrefixCore() . TextFormat::RED . 'You can only use this command in-Game!');
            return;
        }
        if ($this->isPlayerCanUseCommand($sender)) {
            $world = Server::getInstance()->getWorldManager()->getDefaultWorld();
            if ($world instanceof World) {
                $session = PracticeCore::getSessionManager()->getSession($sender);
                $sender->sendMessage(PracticeCore::getPrefixCore() . TextFormat::GREEN . 'Teleported to Hub!');
                PracticeCore::getInstance()->getScoreboardManager()->setLobbyScoreboard($sender);
                PracticeCore::getUtils()->teleportToLobby($sender);
                $session->isDueling = false;
                $session->DuelKit = null;
                $session->BoxingPoint = 0;
            }
        }
    }
}
