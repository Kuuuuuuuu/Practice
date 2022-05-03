<?php

namespace Kuu\Events;

use Kuu\Loader;
use Kuu\NeptunePlayer;
use pocketmine\block\BlockLegacyIds;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\math\Vector3;
use pocketmine\player\Player;

class AntiCheatListener implements Listener
{

    public function onMove(PlayerMoveEvent $event): void
    {
        $player = $event->getPlayer();
        $from = $event->getFrom();
        $name = $player->getName();
        $to = $event->getTo();
        if ($player instanceof NeptunePlayer) {
            if (!$player->isCreative() && !$player->isOnGround() && !$player->isSpectator() && !$player->getAllowFlight()) {
                if ($player->getInAirTicks() > 20 && $player->getWorld()->getBlock(new Vector3($player->getPosition()->getX(), $player->getPosition()->getY() - 1, $player->getPosition()->getZ()))->getId() === BlockLegacyIds::AIR) {
                    $maxY = $player->getWorld()->getHighestBlockAt(floor($to->getX()), floor($to->getZ()));
                    if ($to->getY() - 5 > $maxY) {
                        if (!isset($player->points[$name])) {
                            $player->points[$name]['fly'] = 1.0;
                        } else {
                            $player->points[$name]['fly'] += 1.0;
                            if ($player->points[$name]['fly'] > 3.0) {
                                $player->sendMessage(Loader::getPrefixCore() . 'Fly hack detected!');
                            }
                        }
                    }
                } else {
                    $player->points[$name]['fly'] = 0.0;
                }
            }
            if ($player->isImmobile()) {
                if ($from->getX() !== $to->getX() || $from->getY() !== $to->getY() || $from->getZ() !== $to->getZ()) {
                    $player->teleport($from->asVector3());
                }
            }
        }
    }

    //TODO: Make Anti-Fly & Kill Aura

    public function onDamage(EntityDamageByEntityEvent $event): void
    {
        $entity = $event->getEntity();
        $damager = $event->getDamager();
        $cause = $event->getCause();
        if (($cause !== EntityDamageEvent::CAUSE_PROJECTILE) && $entity instanceof Player && $damager instanceof Player && $entity->getPosition()->distance($damager->getPosition()) > $this->CalculateReach($damager)) {
            $event->cancel();
        }
    }

    private function CalculateReach(Player $damager): float
    {
        $projected = $damager->isOnGround() ? 4.2 : 6.2;
        return ($damager->getNetworkSession()->getPing() * 0.002) + $projected;
    }
}