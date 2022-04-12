<?php

namespace Kohaku\Events;

use Kohaku\Loader;
use Kohaku\NeptunePlayer;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\player\Player;

class AntiCheatListener implements Listener
{

    public static array $data = [];

    public function onMove(PlayerMoveEvent $event)
    {
        $player = $event->getPlayer();
        $from = $event->getFrom();
        $name = $player->getName();
        $to = $event->getTo();
        if ($player instanceof NeptunePlayer) {
            $player->lastPos = $player->getPosition()->asVector3();
            if (!$player->isCreative() and !$player->isSpectator() and !$player->getAllowFlight()) {
                if ($from->getY() <= $to->getY()) {
                    if ($player->GetInAirTicks() > 20) {
                        $maxY = $player->getWorld()->getHighestBlockAt(floor($from->getX()), floor($to->getZ()));
                        if ($from->getY() - 2 > $maxY) {
                            if (!isset($player->points[$name])) {
                                $player->points[$name]["fly"] = 0;
                            } else {
                                $player->points[$name]["fly"] += 1.0;
                                if ($player->points[$name]["fly"] > 5.0) {
                                    $event->cancel();
                                    $player->sendMessage(Loader::getPrefixCore() . "Fly hack detected!");
                                }
                            }
                        }
                    }
                } else {
                    $player->points[$name]["fly"] = 0.0;
                }
            }
            if ($player->isImmobile()) {
                if ($from->getX() != $to->getX() or $from->getY() != $to->getY() or $from->getZ() != $to->getZ()) {
                    $player->teleport($from->asVector3());
                }
            }
        }
    }

    //TODO: Make Anti-Fly & Kill Aura

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
}