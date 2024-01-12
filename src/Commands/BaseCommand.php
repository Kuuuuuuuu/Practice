<?php

namespace Nayuki\Commands;

use Nayuki\PracticeCore;
use pocketmine\command\Command;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

abstract class BaseCommand extends Command
{
    /**
     * @param Player $sender
     * @return bool
     */
    public function isPlayerCanUseCommand(Player $sender): bool
    {
        $session = PracticeCore::getSessionManager()->getSession($sender);
        if ($session->isCombat || $session->isDueling) {
            $sender->sendMessage(PracticeCore::getPrefixCore() . TextFormat::RED . 'You can not use this command while in combat!');
            return false;
        }
        if ($session->spectating) {
            $sender->sendMessage(PracticeCore::getPrefixCore() . TextFormat::RED . 'You can not use this command while spectating!');
            return false;
        }
        return true;
    }
}
