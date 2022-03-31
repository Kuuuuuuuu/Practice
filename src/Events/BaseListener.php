<?php

declare(strict_types=1);

namespace Kohaku\Core\Events;

use Kohaku\Core\HorizonPlayer;
use Kohaku\Core\Loader;
use Kohaku\Core\Utils\DeleteBlocksHandler;
use pocketmine\block\BlockLegacyIds;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\event\world\WorldLoadEvent;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\network\mcpe\protocol\AnimatePacket;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;
use pocketmine\network\mcpe\protocol\ServerSettingsRequestPacket;
use pocketmine\network\mcpe\protocol\ServerSettingsResponsePacket;
use pocketmine\network\mcpe\protocol\types\inventory\UseItemOnEntityTransactionData;
use pocketmine\network\mcpe\protocol\types\LevelSoundEvent;
use pocketmine\permission\DefaultPermissions;
use pocketmine\player\GameMode;
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
        $block = $ev->getBlock();
        if ($player->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getBuildArena())) {
            if ($block->getId() === BlockLegacyIds::WOOL or $block->getId() === BlockLegacyIds::COBWEB) {
                $ev->setDropsVariadic(ItemFactory::getInstance()->get(ItemIds::AIR));
                if ($block->getId() === BlockLegacyIds::WOOL) {
                    $player->getInventory()->addItem(ItemFactory::getInstance()->get(ItemIds::WOOL, 0, 1));
                    DeleteBlocksHandler::getInstance()->setBlockBuild($block, true);
                }
            } else {
                if ($player->getGamemode() !== GameMode::CREATIVE()) {
                    $ev->cancel();
                }
            }
        } else {
            if (!$player->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
                $ev->cancel();
            }
        }
    }

    public function onPlace(BlockPlaceEvent $ev)
    {
        $player = $ev->getPlayer();
        $block = $ev->getBlock();
        if ($player->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getBuildArena())) {
            DeleteBlocksHandler::getInstance()->setBlockBuild($block);
            return;
        }
        if (!$player->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
            $ev->cancel();
        }
    }

    public function onDataPacketSend(DataPacketSendEvent $ev): void
    {
        foreach ($ev->getPackets() as $packet) {
            if ($packet instanceof LevelSoundEventPacket) {
                if ($packet->pid() === LevelSoundEventPacket::NETWORK_ID) {
                    switch ($packet->sound) {
                        case LevelSoundEvent::ATTACK:
                        case LevelSoundEvent::ATTACK_NODAMAGE:
                        case LevelSoundEvent::ATTACK_STRONG:
                            $ev->cancel();
                            break;
                    }
                }
            }
        }
    }

    public function onDataPacketReceive(DataPacketReceiveEvent $event): void
    {
        $player = $event->getOrigin()->getPlayer();
        $packet = $event->getPacket();
        if ($packet instanceof ServerSettingsRequestPacket) {
            $packet = new ServerSettingsResponsePacket();
            $packet->formData = file_get_contents(Loader::getInstance()->getDataFolder() . "Form.json");
            $packet->formId = 5928;
            $player->getNetworkSession()->sendDataPacket($packet);
        } else if ($packet instanceof ModalFormResponsePacket) {
            $formId = $packet->formId;
            if ($formId !== 5928) {
                return;
            }
        }
        if ($packet instanceof InventoryTransactionPacket or $packet instanceof LevelSoundEventPacket) {
            if ($packet::NETWORK_ID === InventoryTransactionPacket::NETWORK_ID && $packet->trData instanceof UseItemOnEntityTransactionData || $packet::NETWORK_ID === LevelSoundEventPacket::NETWORK_ID && $packet->sound === LevelSoundEvent::ATTACK_NODAMAGE) {
                Loader::$cps->addClick($player);
                if (Loader::$cps->getClicks($player) >= Loader::getInstance()->MaximumCPS) {
                    /* @var HorizonPlayer $player */
                    $player->setLastDamagePlayer("Unknown");
                    $player->kill();
                }
            }
        } else if ($event->getPacket()->pid() === AnimatePacket::NETWORK_ID) {
            Server::getInstance()->broadcastPackets($player->getViewers(), [$event->getPacket()]);
            $event->cancel();
        }
    }
}