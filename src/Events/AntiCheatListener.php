<?php

namespace Kohaku\Events;

use Kohaku\NeptunePlayer;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\player\Player;
use pocketmine\Server;

class AntiCheatListener implements Listener
{
    public function onMove(PlayerMoveEvent $event)
    {
        $player = $event->getPlayer();
        $from = $event->getFrom();
        $to = $event->getTo();
        if ($player instanceof NeptunePlayer) {
            if (!$player->isCreative() and !$player->isSpectator() and !$player->getAllowFlight()) {
                if ($from->getY() <= $to->getY()) {
                    if ($player->GetInAirTicks() > 20) {
                        $maxY = $player->getWorld()->getHighestBlockAt(floor($from->getX()), floor($to->getZ()));
                        if ($from->getY() - 2 > $maxY) {
                            $player->points[$player->getName()]["fly"] += 1.0;
                            if ($player->points[$player->getName()]["fly"] > 5.0) {
                                $event->cancel();
                                $player->kick("Fly hack detected!");
                                Server::getInstance()->broadcastMessage("§c" . $player->getName() . " §7has been kicked for flying.");
                            }
                        }
                    }
                } else {
                    $player->points[$player->getName()]["fly"] = 0.0;
                }
            }
            if ($player->isImmobile()) {
                if ($from->getX() != $to->getX() or $from->getY() != $to->getY() or $from->getZ() != $to->getZ()) {
                    $player->teleport($from->asVector3());
                }
            }
        }
    }

    public function onDamage(EntityDamageByEntityEvent $event)
    {
        $entity = $event->getEntity();
        $damager = $event->getDamager();
        $cause = $event->getCause();
        if ($cause !== EntityDamageEvent::CAUSE_PROJECTILE) {
            if ($entity instanceof Player && $damager instanceof Player) {
                if ($entity->getPosition()->distance($damager->getPosition()) > 5.5) {
                    $event->cancel();
                }
            }
        }
    }
    //TODO: Make Anti-Fly & Kill Aura
}