<?php

declare(strict_types=1);

namespace Kohaku\Core\Events;

use Kohaku\Core\Loader;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityBlockChangeEvent;
use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\world\WorldLoadEvent;
use pocketmine\network\mcpe\protocol\AnimatePacket;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\types\inventory\UseItemOnEntityTransactionData;
use pocketmine\network\mcpe\protocol\types\LevelSoundEvent;
use pocketmine\permission\DefaultPermissions;
use pocketmine\player\Player;
use pocketmine\Server;

class BaseListener implements Listener
{
    public function onLevelLoadEvent(WorldLoadEvent $event)
    {
        $levelName = $event->getWorld()->getFolderName();
        $world = $event->getWorld();
        $world->setTime(0);
        $world->stopTime();
        Loader::getInstance()->ClearChunksWorlds[] = $levelName;
    }


    public function onBreak(BlockBreakEvent $ev)
    {
        $player = $ev->getPlayer();
        if (!$player->hasPermission(DefaultPermissions::ROOT_OPERATOR) && $player->getWorld() !== Server::getInstance()->getWorldManager()->getWorldByName("plot")) {
            $ev->cancel();
        }
    }

    public function onPlace(BlockPlaceEvent $ev)
    {
        $player = $ev->getPlayer();
        if (!$player->hasPermission(DefaultPermissions::ROOT_OPERATOR) && $player->getWorld() !== Server::getInstance()->getWorldManager()->getWorldByName("plot")) {
            $ev->cancel();
        }
    }

    public function PacketReceived(DataPacketReceiveEvent $event)
    {
        $player = $event->getOrigin()->getPlayer();
        $packet = $event->getPacket();
        if ($packet::NETWORK_ID === InventoryTransactionPacket::NETWORK_ID && $packet->trData instanceof UseItemOnEntityTransactionData || $packet::NETWORK_ID === LevelSoundEventPacket::NETWORK_ID && $packet->sound === LevelSoundEvent::ATTACK_NODAMAGE) {
            Loader::$cps->addClick($player);
        }
    }

    public function onDataPacketReceive(DataPacketReceiveEvent $ev): void
    {
        if ($ev->getPacket()->pid() === AnimatePacket::NETWORK_ID) {
            $ev->getOrigin()->getPlayer()->getServer()->broadcastPackets($ev->getOrigin()->getPlayer()->getViewers(), [$ev->getPacket()]);
            $ev->cancel();
        }
    }

    public function onBlockChange(EntityBlockChangeEvent $event)
    {
        $entity = $event->getEntity();
        if ($entity instanceof Player) {
            if ($entity->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getKitPVPArena())) {
                $event->cancel();
            }
        }
    }
}