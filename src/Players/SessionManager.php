<?php

declare(strict_types=1);

namespace Nayuki\Players;

use Nayuki\PracticeCore;
use pocketmine\player\Player;

final class SessionManager
{
    /**
     * @param Player $player
     * @return PlayerSession
     */
    public static function getSession(Player $player): PlayerSession
    {
        return PracticeCore::getCaches()->PlayerSession[$player->getName()] ??= new PlayerSession($player);
    }

    /**
     * @param Player $player
     * @return void
     */
    public static function removeSession(Player $player): void
    {
        unset(PracticeCore::getCaches()->PlayerSession[$player->getName()]);
    }
}
