<?php

namespace Nayuki\Commands;

use Nayuki\PracticeCore;
use pocketmine\command\Command;
use pocketmine\player\Player;

abstract class BaseCommand extends Command
{
    /**
     * @param Player $sender
     * @return bool
     */
    public function isPlayerCanUseCommand(Player $sender): bool
    {
        $session = PracticeCore::getSessionManager()->getSession($sender);
        if ($session->isCombat()) {
            $sender->sendMessage(PracticeCore::getPrefixCore() . '§cYou can not use this command while in combat!');
            return false;
        }
        return true;
    }
}
