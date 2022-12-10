<?php

namespace Kuu\Commands;

use Kuu\PracticeCore;
use pocketmine\command\Command;
use pocketmine\player\Player;

abstract class BaseCommand extends Command
{
    public function isPlayerCanUseCommand(Player $sender): bool
    {
        $session = PracticeCore::getPlayerSession()::getSession($sender);
        if ($session->isCombat()) {
            $sender->sendMessage(PracticeCore::getPrefixCore() . '§cYou can not use this command while in combat!');
            return false;
        }
        return true;
    }
}
