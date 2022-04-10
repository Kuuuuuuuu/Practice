<?php

namespace Kohaku\Events;

use Kohaku\Loader;
use Kohaku\NeptunePlayer;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\player\Player;
use pocketmine\Server;
use Throwable;

class AntiCheatListener implements Listener
{

    public static array $data = [];

    public function onMove(PlayerMoveEvent $event)
    {
        $player = $event->getPlayer();
        $from = $event->getFrom();
        $pos = $player->getPosition()->asVector3();
        $name = $player->getName();
        $to = $event->getTo();
        if ($player instanceof NeptunePlayer) {
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
            /*try {
                if ($event->getTo()->distance($event->getFrom()) > 0) {
                    if (!isset($player->lastpos[$name][0])) {
                        $player->lastpos[$name][0]["pos"] = $pos;
                        $player->lastpos[$name][0]["time"] = microtime(true);
                    } else {
                        array_unshift($player->lastpos[$name], [
                            "pos" => $pos,
                            "time" => microtime(true)
                        ]);
                        if (count($player->lastpos[$name][0]) > 2) {
                            array_pop($player->lastpos[$name]);
                        }
                        if (!isset($this->data[$name])) {
                            self::$data[$name] = [];
                        }
                        $distance = $player->lastpos[$name][0]["pos"]->distance($player->lastpos[$name][1]["pos"]);
                        $time = $player->lastpos[$name][0]["time"] - $player->lastpos[$name][1]["time"];
                        array_unshift(self::$data[$name], [
                            "distance" => $distance,
                            "time" => $time,
                            "timestamp" => microtime(true)
                        ]);
                        if (count(self::$data[$name]) > 100) {
                            array_pop(self::$data[$name]);
                        }
                    }
                }
            } catch (Throwable $error) {
                //Loader::getArenaUtils()::getLogger($error->getMessage() . "\n" . $error->getTraceAsString());
            }
            if (self::calculateSpeed($player) > 20) {
                Server::getInstance()->getLogger()->info("§c" . $name . self::calculateSpeed($player));
                $player->sendMessage(Loader::getPrefixCore() . "Speed hack detected!");
            }*/
            if ($player->isImmobile()) {
                if ($from->getX() != $to->getX() or $from->getY() != $to->getY() or $from->getZ() != $to->getZ()) {
                    $player->teleport($from->asVector3());
                }
            }
        }
    }

    public static function calculateSpeed(Player $p, int $precision = 2): ?float
    {
        try {
            $name = $p->getName();
            if (isset(self::$data[$name])) {
                $data = array_filter(self::$data[$name], function ($entry): bool {
                    return (microtime(true) - $entry["timestamp"]) <= 1;
                });
                $speeds = [];
                foreach ($data as $entry) {
                    $time = $entry["time"];
                    $speeds[] = ($entry["distance"] / $time);
                }
                return round(array_sum($speeds) / count($speeds), $precision);
            }
        } catch (Throwable $error) {
            //Loader::getArenaUtils()::getLogger($error->getMessage() . "\n" . $error->getTraceAsString());
        }
        return null;
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