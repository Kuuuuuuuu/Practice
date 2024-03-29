<?php

declare(strict_types=1);

namespace Nayuki;

use JsonException;
use Nayuki\Misc\AbstractListener;
use Nayuki\Misc\CustomChatFormatter;
use pocketmine\entity\Entity;
use pocketmine\entity\Skin;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\inventory\CraftItemEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\player\PlayerChangeSkinEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerCreationEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerKickEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\plugin\PluginDisableEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\NetworkInterfaceRegisterEvent;
use pocketmine\event\server\QueryRegenerateEvent;
use pocketmine\event\world\WorldLoadEvent;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\NetworkBroadcastUtils;
use pocketmine\network\mcpe\protocol\AddActorPacket;
use pocketmine\network\mcpe\protocol\AnimatePacket;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\types\entity\PropertySyncData;
use pocketmine\network\mcpe\protocol\types\inventory\UseItemOnEntityTransactionData;
use pocketmine\network\mcpe\protocol\types\LevelSoundEvent;
use pocketmine\network\mcpe\raklib\RakLibInterface;
use pocketmine\network\query\DedicatedQueryNetworkInterface;
use pocketmine\permission\DefaultPermissions;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use pocketmine\world\World;

final class PracticeListener extends AbstractListener
{
    /**
     * @param PlayerCreationEvent $event
     * @return void
     * @priority MONITOR
     */
    public function onPlayerCreationEvent(PlayerCreationEvent $event): void
    {
        $event->setPlayerClass(PracticePlayer::class);
    }

    /**
     * @param PlayerItemUseEvent $event
     * @return void
     * @priority LOWEST
     * @handleCancelled
     */
    public function onPlayerItemUseEvent(PlayerItemUseEvent $event): void
    {
        $player = $event->getPlayer();
        $session = PracticeCore::getSessionManager()->getSession($player);
        $item = $event->getItem();
        switch ($item->getCustomName()) {
            case '§r§dPlay':
                PracticeCore::getFormUtils()->ArenaForm($player);
                break;
            case '§r§dSettings':
                PracticeCore::getFormUtils()->SettingsForm($player);
                break;
            case '§r§dDuels':
                PracticeCore::getFormUtils()->duelForm($player);
                break;
            case '§r§cLeave Queue':
                $player->sendMessage(PracticeCore::getPrefixCore() . TextFormat::RED . 'You have left the queue!');
                $session->isQueueing = false;
                $session->DuelKit = null;
                PracticeCore::getUtils()->teleportToLobby($player);
                break;
            case '§r§dCosmetics':
                PracticeCore::getFormUtils()->cosmeticForm($player);
                break;
            case '§r§dBot':
                PracticeCore::getDuelManager()->createBotMatch($player);
                break;
            case '§r§dSpectate':
                PracticeCore::getFormUtils()->spectateForm($player);
                break;
        }
    }

    /**
     * @param QueryRegenerateEvent $ev
     * @return void
     * @priority LOWEST
     */
    public function onQueryRegenerateEvent(QueryRegenerateEvent $ev): void
    {
        $query = $ev->getQueryInfo();
        $query->setWorld('PracticeLobby');
        $query->setPlugins([PracticeCore::getInstance()]);
        $query->setMaxPlayerCount($ev->getQueryInfo()->getPlayerCount() + 1);
    }

    /**
     * @param PlayerDropItemEvent $event
     * @return void
     * @priority LOWEST
     */
    public function onPlayerDropItemEvent(PlayerDropItemEvent $event): void
    {
        $event->cancel();
    }

    /**
     * @param PlayerLoginEvent $event
     * @return void
     * @priority LOWEST
     * @throws JsonException
     * @noinspection SqlDialectInspection
     * @noinspection SqlNoDataSourceInspection
     */
    public function onPlayerLoginEvent(PlayerLoginEvent $event): void
    {
        $player = $event->getPlayer();
        $name = $player->getName();
        $cosmetic = PracticeCore::getCosmeticHandler();
        $skin = new Skin($player->getSkin()->getSkinId(), $player->getSkin()->getSkinData(), '', $player->getSkin()->getGeometryName() !== 'geometry.humanoid.customSlim' ? 'geometry.humanoid.custom' : $player->getSkin()->getGeometryName(), '');
        $cosmetic->saveSkin($skin->getSkinData(), $name);
    }

    /**
     * @param PlayerPreLoginEvent $event
     * @return void
     * @priority LOWEST
     */

    public function onPlayerPreLoginEvent(PlayerPreLoginEvent $event): void
    {
        foreach (Server::getInstance()->getOnlinePlayers() as $p) {
            if ($p->getUniqueId() !== $event->getPlayerInfo()->getUuid() && strtolower($event->getPlayerInfo()->getUsername()) === strtolower($p->getName())) {
                $event->setKickFlag(3, PracticeCore::getPrefixCore() . TextFormat::RED . 'This player is already online!');
            }
        }
    }

    /**
     * @param PlayerJoinEvent $event
     * @return void
     * @priority LOWEST
     */
    public function onPlayerJoinEvent(PlayerJoinEvent $event): void
    {
        $player = $event->getPlayer();
        $name = $player->getName();
        $player->sendMessage(PracticeCore::getPrefixCore() . TextFormat::YELLOW . 'Loading Player Data...');
        $event->setJoinMessage('§f[§a+§f] §a' . $name);
        PracticeCore::getUtils()->teleportToLobby($player);
        PracticeCore::getPlayerHandler()->loadPlayerData($player);
    }

    /**
     * @param PlayerChangeSkinEvent $event
     * @return void
     * @throws JsonException
     * @priority LOWEST
     */
    public function onPlayerChangeSkin(PlayerChangeSkinEvent $event): void
    {
        $player = $event->getPlayer();
        $name = $player->getName();
        $session = PracticeCore::getSessionManager()->getSession($player);
        $cosmetic = PracticeCore::getCosmeticHandler();
        $newSkin = $event->getNewSkin();
        $skinData = $newSkin->getSkinData();
        $geometryName = $newSkin->getGeometryName() !== 'geometry.humanoid.customSlim' ? 'geometry.humanoid.custom' : $newSkin->getGeometryName();
        $event->cancel();
        $skin = new Skin($newSkin->getSkinId(), $skinData, '', $geometryName, '');
        $cosmetic->saveSkin($skin->getSkinData(), $name);
        $player->sendMessage(PracticeCore::getPrefixCore() . TextFormat::GREEN . 'Your skin has been changed!');
        if ($session->artifact !== '') {
            $cosmetic->setSkin($player, $session->artifact);
        } elseif ($session->cape !== '') {
            $capeData = $cosmetic->createCape($session->cape);
            $player->setSkin(new Skin($newSkin->getSkinId(), $newSkin->getSkinData(), $capeData, $geometryName, ''));
        } else {
            $player->setSkin(new Skin($newSkin->getSkinId(), $newSkin->getSkinData(), '', $geometryName, ''));
        }
    }

    /**
     * @param PlayerExhaustEvent $event
     * @return void
     * @priority LOWEST
     */

    public function onPlayerExhaustEvent(PlayerExhaustEvent $event): void
    {
        $event->setAmount(0);
    }

    /**
     * @param PlayerInteractEvent $event
     * @return void
     * @priority LOWEST
     */
    public function onPlayerInteractEvent(PlayerInteractEvent $event): void
    {
        $player = $event->getPlayer();
        if ($player->getGamemode() !== GameMode::CREATIVE) {
            $event->cancel();
        }
    }

    /**
     * @param CraftItemEvent $event
     * @return void
     * @priority LOWEST
     */
    public function onCraftItemEvent(CraftItemEvent $event): void
    {
        if (!$event->isCancelled()) {
            $event->cancel();
        }
    }

    /**
     * @param WorldLoadEvent $event
     * @return void
     * @priority LOWEST
     */
    public function onWorldLoadEvent(WorldLoadEvent $event): void
    {
        $world = $event->getWorld();
        $world->setTime(World::TIME_DAY);
        $world->stopTime();
    }

    /**
     * @param BlockBreakEvent $ev
     * @return void
     * @priority LOWEST
     */
    public function onBlockBreakEvent(BlockBreakEvent $ev): void
    {
        $player = $ev->getPlayer();
        if (!$player->hasPermission(DefaultPermissions::ROOT_OPERATOR) && $player->getGamemode() !== GameMode::CREATIVE) {
            $ev->cancel();
        }
    }

    /**
     * @param BlockPlaceEvent $ev
     * @return void
     * @priority LOWEST
     */
    public function onBlockPlaceEvent(BlockPlaceEvent $ev): void
    {
        $player = $ev->getPlayer();
        if (!$player->hasPermission(DefaultPermissions::ROOT_OPERATOR) && $player->getGamemode() !== GameMode::CREATIVE) {
            $ev->cancel();
        }
    }

    /**
     * @param DataPacketReceiveEvent $event
     * @return void
     * @priority LOWEST
     */
    public function onDataPacketReceiveEvent(DataPacketReceiveEvent $event): void
    {
        $player = $event->getOrigin()->getPlayer();
        $packet = $event->getPacket();
        if ($player instanceof Player) {
            if (($packet instanceof InventoryTransactionPacket && $packet->trData instanceof UseItemOnEntityTransactionData) || ($packet instanceof LevelSoundEventPacket && $packet->sound === LevelSoundEvent::ATTACK_NODAMAGE)) {
                PracticeCore::getClickHandler()->addClick($player);
            } elseif ($packet instanceof AnimatePacket) {
                NetworkBroadcastUtils::broadcastPackets($player->getViewers(), [$packet]);
                $event->cancel();
            }
        }
    }

    /**
     * @param InventoryTransactionEvent $event
     * @return void
     * @priority LOWEST
     */
    public function onInventoryTransactionEvent(InventoryTransactionEvent $event): void
    {
        $transaction = $event->getTransaction();
        $player = $transaction->getSource();
        if ($player->getGamemode() !== GameMode::CREATIVE && $player->getWorld() === Server::getInstance()->getWorldManager()->getDefaultWorld()) {
            $event->cancel();
        }
    }

    /**
     * @param PlayerChatEvent $event
     * @return void
     * @priority LOWEST
     */
    public function onPlayerChatEvent(PlayerChatEvent $event): void
    {
        $formatter = new CustomChatFormatter();
        $event->setFormatter($formatter);
    }

    /**
     * @param PlayerQuitEvent $event
     * @return void
     * @priority LOWEST
     */
    public function onPlayerQuitEvent(PlayerQuitEvent $event): void
    {
        $player = $event->getPlayer();
        $name = $player->getName();
        $event->setQuitMessage('§f[§c-§f] §c' . $name);
        PracticeCore::getClickHandler()->removePlayerClickData($player);
        PracticeCore::getPlayerHandler()->savePlayerData($player);
    }

    /**
     * @param PlayerKickEvent $event
     * @return void
     * @priority LOWEST
     */
    public function onPlayerKickEvent(PlayerKickEvent $event): void
    {
        PracticeCore::getPlayerHandler()->savePlayerData($event->getPlayer());
    }

    /**
     * @param PluginDisableEvent $event
     * @return void
     * @priority MONITOR
     */
    public function onPluginDisableEvent(PluginDisableEvent $event): void
    {
        $plugin = $event->getPlugin();
        if ($plugin instanceof PracticeCore) {
            $sessions = PracticeCore::getSessionManager()->getSessions();
            if (!empty($sessions)) {
                foreach ($sessions as $session) {
                    $player = $session->getPlayer();
                    PracticeCore::getPlayerHandler()->savePlayerData($player);
                }
            }
            $arenas = PracticeCore::getDuelManager()->getArenas();
            if (!empty($arenas)) {
                foreach ($arenas as $duel) {
                    PracticeCore::getDuelManager()->stopMatch($duel->name);
                }
            }
        }
    }

    /**
     * @param EntityDamageByEntityEvent $event
     * @return void
     * @priority LOWEST
     */
    public function onEntityDamageByEntityEvent(EntityDamageByEntityEvent $event): void
    {
        $player = $event->getEntity();
        $damager = $event->getDamager();
        $sessionManager = PracticeCore::getSessionManager();
        if ($damager instanceof Player && !$event->isCancelled()) {
            if ($player instanceof Player) {
                if ($damager->getWorld() === Server::getInstance()->getWorldManager()->getDefaultWorld()) {
                    $event->cancel();
                    return;
                }
                $DSession = $sessionManager->getSession($damager);
                $PSession = $sessionManager->getSession($player);

                if ($DSession->isDueling || $PSession->isDueling) {
                    return;
                }

                if ($PSession->getOpponent() === null && $DSession->getOpponent() === null) {
                    $PSession->setOpponent($damager->getName());
                    $DSession->setOpponent($player->getName());
                    $player->sendMessage(PracticeCore::getPrefixCore() . TextFormat::GRAY . 'You are now in combat with §c' . $damager->getName());
                    $damager->sendMessage(PracticeCore::getPrefixCore() . TextFormat::GRAY . 'You are now in combat with §c' . $player->getName());
                    $PSession->isCombat = $DSession->isCombat = true;
                    $PSession->CombatTime = $DSession->CombatTime = 10;
                } elseif ($PSession->getOpponent() !== null && $DSession->getOpponent() !== null) {
                    if ($PSession->getOpponent() === $damager->getName() && $DSession->getOpponent() === $player->getName()) {
                        foreach ([$player, $damager] as $p) {
                            $session = PracticeCore::getSessionManager()->getSession($p);
                            $session->isCombat = true;
                            $session->CombatTime = 10;
                        }
                        return;
                    }
                    $event->cancel();
                    $damager->sendMessage(PracticeCore::getPrefixCore() . "§cDon't Interrupt!");
                } else {
                    $event->cancel();
                    $damager->sendMessage(PracticeCore::getPrefixCore() . "§cDon't Interrupt!");
                }
            }
        }
    }

    /**
     * @param EntityDamageEvent $event
     * @return void
     * @priority LOWEST
     */
    public function onEntityDamageEvent(EntityDamageEvent $event): void
    {
        $entity = $event->getEntity();
        if ($entity instanceof Player) {
            switch ($event->getCause()) {
                case EntityDamageEvent::CAUSE_VOID:
                    if ($entity->getWorld() === Server::getInstance()->getWorldManager()->getDefaultWorld()) {
                        $event->cancel();
                        $entity->teleport($entity->getWorld()->getSafeSpawn());
                    } else {
                        $entity->kill();
                    }
                    break;
                case EntityDamageEvent::CAUSE_FALL:
                    $event->cancel();
                    break;
                case EntityDamageEvent::CAUSE_SUFFOCATION:
                    $event->cancel();
                    $entity->teleport(new Vector3($entity->getPosition()->getX(), $entity->getPosition()->getY() + 3, $entity->getPosition()->getZ()));
                    break;
            }
        }
    }

    /**
     * @param PlayerDeathEvent $event
     * @return void
     * @priority LOWEST
     */
    public function onPlayerDeathEvent(PlayerDeathEvent $event): void
    {
        $event->setDeathMessage('');
        $event->setDrops([]);
        $event->setXpDropAmount(0);
        $player = $event->getPlayer();
        $cause = $player->getLastDamageCause();
        $session = PracticeCore::getSessionManager()->getSession($player);
        if (($cause instanceof EntityDamageByEntityEvent) && $session->getOpponent() !== null) {
            $damager = PracticeCore::getSessionManager()->getPlayerInSessionByPrefix($session->getOpponent());
            if ($damager instanceof Player) {
                $damagerSession = PracticeCore::getSessionManager()->getSession($damager);
                foreach ([$damager, $player] as $p) {
                    $session = PracticeCore::getSessionManager()->getSession($p);
                    $session->CombatTime = 0;
                    $session->isCombat = false;
                    $session->setOpponent(null);
                }
                Server::getInstance()->broadcastMessage('§a' . $damager->getName() . ' §ehas killed §c' . $player->getName());
                PracticeCore::getUtils()->handleStreak($damager, $player);
                $damagerSession->killStreak++;
                $damagerSession->kills++;
                $damagerSession->coins += PracticeCore::getUtils()->randomCoinsPerKill();
                $session->deaths++;
                $session->killStreak = 0;
                $damager->setHealth(20);
                if ($session->isLightningKill) {
                    $lightning = new AddActorPacket();
                    $lightning->actorUniqueId = Entity::nextRuntimeId();
                    $lightning->actorRuntimeId = 1;
                    $lightning->position = $player->getPosition()->asVector3();
                    $lightning->type = 'minecraft:lightning_bolt';
                    $lightning->yaw = $player->getLocation()->getYaw();
                    $lightning->syncedProperties = new PropertySyncData([], []);
                    PracticeCore::getUtils()->playSound('ambient.weather.thunder', $damager);
                    NetworkBroadcastUtils::broadcastPackets([$damager], [$lightning]);
                }
            }
        }
    }

    /**
     * @param PlayerRespawnEvent $event
     * @return void
     * @priority LOWEST
     */
    public function onPlayerRespawnEvent(PlayerRespawnEvent $event): void
    {
        $player = $event->getPlayer();
        $session = PracticeCore::getSessionManager()->getSession($player);
        $session->isCombat = false;
        $session->CombatTime = 0;
        $session->setOpponent(null);
        $session->isDueling = false;
        $session->isQueueing = false;
        $session->DuelKit = null;
        PracticeCore::getScoreboardManager()->setLobbyScoreboard($player);
        PracticeCore::getUtils()->teleportToLobby($player);
    }

    /**
     * @param EntityTeleportEvent $event
     * @return void
     * @priority LOWEST
     */
    public function onEntityTeleportEvent(EntityTeleportEvent $event): void
    {
        $player = $event->getEntity();
        if ($player instanceof Player && $event->getFrom()->getWorld() !== $event->getTo()->getWorld()) {
            $session = PracticeCore::getSessionManager()->getSession($player);
            if (!$session->isDueling && !$session->isQueueing) {
                $session->isCombat = false;
                $session->CombatTime = 0;
                $session->setOpponent(null);
            }
        }
    }

    /**
     * @param NetworkInterfaceRegisterEvent $event
     * @return void
     * @priority LOWEST
     */
    public function onNetworkInterfaceRegisterEvent(NetworkInterfaceRegisterEvent $event): void
    {
        $interface = $event->getInterface();
        if ($interface instanceof RakLibInterface) {
            $interface->setPacketLimit(PHP_INT_MAX);
        } elseif ($interface instanceof DedicatedQueryNetworkInterface) {
            $event->cancel();
        }
    }
}
