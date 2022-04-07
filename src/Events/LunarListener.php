<?php

namespace Kohaku\Events;

use Kohaku\Loader;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerMoveEvent;

class LunarListener implements Listener
{
    public function onMove(PlayerMoveEvent $event)
    {
        $player = $event->getPlayer();
        $from = $event->getFrom();
        $to = $event->getTo();
        if ($player->isImmobile()) {
            if ($from->getX() != $to->getX() || $from->getY() != $to->getY() || $from->getZ() != $to->getZ()) {
                $player->sendMessage(Loader::getPrefixCore() . "You Cannot Move when Immobile!");
                $event->cancel();
            }
        }
    }
    //TODO: Make Anti-Fly & Kill Aura
}