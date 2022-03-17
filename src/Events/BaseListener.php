<?php

declare(strict_types=1);

namespace Kohaku\Core\Events;

use Kohaku\Core\Loader;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\event\world\WorldLoadEvent;
use pocketmine\network\mcpe\protocol\AnimatePacket;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\types\inventory\UseItemOnEntityTransactionData;
use pocketmine\network\mcpe\protocol\types\LevelSoundEvent;
use pocketmine\permission\DefaultPermissions;
use pocketmine\Server;

class BaseListener implements Listener
{
    public function onLevelLoadEvent(WorldLoadEvent $event)
    {
        $world = $event->getWorld();
        $world->setTime(0);
        $world->stopTime();
    }


    public function onBreak(BlockBreakEvent $ev)
    {
        $player = $ev->getPlayer();
        if (!$player->hasPermission(DefaultPermissions::ROOT_OPERATOR) and $player->getWorld() !== Server::getInstance()->getWorldManager()->getWorldByName("aqua")) {
            $ev->cancel();
        }
    }

    public function onPlace(BlockPlaceEvent $ev)
    {
        $player = $ev->getPlayer();
        if (!$player->hasPermission(DefaultPermissions::ROOT_OPERATOR) and $player->getWorld() !== Server::getInstance()->getWorldManager()->getWorldByName("aqua")) {
            $ev->cancel();
        }
    }

    public function onDataPacketSend(DataPacketSendEvent $ev): void
    {
        foreach ($ev->getPackets() as $packet) {
            if ($packet instanceof LevelSoundEventPacket) {
                if ($packet->pid() === LevelSoundEventPacket::NETWORK_ID) {
                    if ($packet->sound === LevelSoundEvent::ATTACK) {
                        $ev->cancel();
                    } else if ($packet->sound === LevelSoundEvent::ATTACK_NODAMAGE) {
                        $ev->cancel();
                    } else if ($packet->sound === LevelSoundEvent::ATTACK_STRONG) {
                        $ev->cancel();
                    }
                }
            }
        }
    }

    public function onDataPacketReceive(DataPacketReceiveEvent $event): void
    {
        $player = $event->getOrigin()->getPlayer();
        $packet = $event->getPacket();
        if ($packet instanceof InventoryTransactionPacket or $packet instanceof LevelSoundEventPacket) {
            if ($packet::NETWORK_ID === InventoryTransactionPacket::NETWORK_ID && $packet->trData instanceof UseItemOnEntityTransactionData || $packet::NETWORK_ID === LevelSoundEventPacket::NETWORK_ID && $packet->sound === LevelSoundEvent::ATTACK_NODAMAGE) {
                Loader::$cps->addClick($player);
            }
        } else if ($event->getPacket()->pid() === AnimatePacket::NETWORK_ID) {
            Server::getInstance()->broadcastPackets($player->getViewers(), [$event->getPacket()]);
            $event->cancel();
        }
    }
}