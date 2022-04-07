<?php

namespace Kohaku\Events;

use Kohaku\Loader;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\player\Player;

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
                $player->teleport($from->asVector3());
            }
        }
    }

    public function onDamage(EntityDamageByEntityEvent $event)
    {
        $entity = $event->getEntity();
        $damager = $event->getDamager();
        if ($entity instanceof Player && $damager instanceof Player) {
            if ($entity->getPosition()->distance($damager->getPosition()) > 5.5) {
                $damager->kill();
                $event->cancel();
            }
        }
    }
    //TODO: Make Anti-Fly & Kill Aura
}