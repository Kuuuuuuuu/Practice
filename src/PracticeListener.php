<?php

declare(strict_types=1);

namespace Nayuki;

use JsonException;
use Nayuki\Misc\AbstractListener;
use Nayuki\Misc\CustomChatFormatter;
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
use pocketmine\network\mcpe\protocol\AnimatePacket;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\types\inventory\UseItemOnEntityTransactionData;
use pocketmine\network\mcpe\protocol\types\LevelSoundEvent;
use pocketmine\network\mcpe\raklib\RakLibInterface;
use pocketmine\network\query\DedicatedQueryNetworkInterface;
use pocketmine\permission\DefaultPermissions;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\world\World;
use SQLite3Stmt;

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
        if ($item->getCustomName() === '§r§bPlay') {
            PracticeCore::getFormUtils()->ArenaForm($player);
        } elseif ($item->getCustomName() === '§r§bSettings') {
            PracticeCore::getFormUtils()->SettingsForm($player);
        } elseif ($item->getCustomName() === '§r§bDuels') {
            PracticeCore::getFormUtils()->duelForm($player);
        } elseif ($item->getCustomName() === '§r§cLeave Queue') {
            $player->sendMessage(PracticeCore::getPrefixCore() . '§cYou have left the queue!');
            $session->isQueueing = false;
            $session->DuelKit = null;
            PracticeCore::getUtils()->setLobbyItem($player);
        } elseif ($item->getCustomName() === '§r§bCosmetics') {
            PracticeCore::getFormUtils()->cosmeticForm($player);
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
     */
    public function onPlayerLoginEvent(PlayerLoginEvent $event): void
    {
        $player = $event->getPlayer();
        $banplayer = $player->getName();
        $stmt = PracticeCore::getInstance()->BanDatabase->prepare('SELECT * FROM banPlayers WHERE player = ?');
        if (!$stmt instanceof SQLite3Stmt) {
            return;
        }
        $stmt->bindValue(1, $banplayer);
        $result = $stmt->execute();
        if ($result === false) {
            return;
        }
        if ($array = $result->fetchArray(SQLITE3_ASSOC)) {
            $banTime = $array['banTime'];
            $reason = $array['reason'];
            $now = time();
            if ($banTime > $now) {
                $remainingTime = $banTime - $now;
                $day = gmdate('d', $remainingTime);
                $hour = gmdate('H', $remainingTime);
                $minute = gmdate('i', $remainingTime);
                $kickMessage = sprintf("§cYou Are Banned\n§6Reason : §f%s\n§6Unban At §f: §e%s D §f| §e%s H §f| §e%s M", $reason, $day, $hour, $minute);
                $player->kick($kickMessage);
                PracticeCore::getInstance()->getServer()->broadcastMessage(sprintf('%s has been banned: %s', $player->getName(), $reason));
                $event->cancel();
            } else {
                $stmt = PracticeCore::getInstance()->BanDatabase->prepare('DELETE FROM banPlayers WHERE player = ?');
                if (!$stmt instanceof SQLite3Stmt) {
                    return;
                }
                $stmt->bindValue(1, $banplayer);
                $stmt->execute();
            }
        }
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
                $event->setKickFlag(3, PracticeConfig::PREFIX . '§cThis player is already online!');
            }
        }
    }

    /**
     * @param PlayerJoinEvent $event
     * @return void
     * @priority LOWEST
     * @throws JsonException
     */
    public function onPlayerJoinEvent(PlayerJoinEvent $event): void
    {
        $player = $event->getPlayer();
        $name = $player->getName();
        $event->setJoinMessage('§f[§a+§f] §a' . $name);
        $player->sendMessage(PracticeCore::getPrefixCore() . '§eLoading Player Data...');
        PracticeCore::getUtils()->setLobbyItem($player);
        PracticeCore::getPlayerHandler()->loadPlayerData($player);
        $skin = new Skin($player->getSkin()->getSkinId(), $player->getSkin()->getSkinData(), '', $player->getSkin()->getGeometryName() !== 'geometry.humanoid.customSlim' ? 'geometry.humanoid.custom' : $player->getSkin()->getGeometryName(), '');
        PracticeCore::getCosmeticHandler()->saveSkin($skin->getSkinData(), $name);
    }

    /**
     * @param PlayerChangeSkinEvent $event
     * @return void
     * @throws JsonException
     * @priority LOWEST
     */
    public function onChangeSkin(PlayerChangeSkinEvent $event): void
    {
        $player = $event->getPlayer();
        $name = $player->getName();
        if ($player instanceof PracticePlayer) {
            $cosmetic = PracticeCore::getCosmeticHandler();
            if (strlen($event->getNewSkin()->getSkinData()) >= 131072 || strlen($event->getNewSkin()->getSkinData()) <= 8192 || $cosmetic->getSkinTransparencyPercentage($event->getNewSkin()->getSkinData()) > 6) {
                copy($cosmetic->stevePng, $cosmetic->saveSkin . "$name.png");
                $cosmetic->resetSkin($player);
            } else {
                $skin = new Skin($event->getNewSkin()->getSkinId(), $event->getNewSkin()->getSkinData(), '', $event->getNewSkin()->getGeometryName() !== 'geometry.humanoid.customSlim' ? 'geometry.humanoid.custom' : $event->getNewSkin()->getGeometryName(), '');
                $cosmetic->saveSkin($skin->getSkinData(), $name);
            }
            $skin = new Skin($player->getSkin()->getSkinId(), $player->getSkin()->getSkinData(), '', $player->getSkin()->getGeometryName() !== 'geometry.humanoid.customSlim' ? 'geometry.humanoid.custom' : $player->getSkin()->getGeometryName(), '');
            $cosmetic->saveSkin($skin->getSkinData(), $name);
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
        if ($player->getGamemode() !== GameMode::CREATIVE()) {
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
        if (!$player->hasPermission(DefaultPermissions::ROOT_OPERATOR) && $player->getGamemode() !== GameMode::CREATIVE()) {
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
        if (!$player->hasPermission(DefaultPermissions::ROOT_OPERATOR) && $player->getGamemode() !== GameMode::CREATIVE()) {
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
        if ($player->getGamemode() !== GameMode::CREATIVE() && $player->getWorld() === Server::getInstance()->getWorldManager()->getDefaultWorld()) {
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
        $session = PracticeCore::getSessionManager()->getSession($player);
        $name = $player->getName();
        $event->setQuitMessage('§f[§c-§f] §c' . $name);
        PracticeCore::getClickHandler()->removePlayerClickData($player);
        PracticeCore::getPlayerHandler()->savePlayerData($player);
        if ($session->isDueling || $session->isCombat) {
            $player->kill();
        }
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
        if ($damager instanceof Player && !$event->isCancelled()) {
            if ($player instanceof Player) {
                if ($damager->getWorld() === Server::getInstance()->getWorldManager()->getDefaultWorld()) {
                    $event->cancel();
                    return;
                }
                $DSession = PracticeCore::getSessionManager()->getSession($damager);
                $PSession = PracticeCore::getSessionManager()->getSession($player);
                if ($DSession->isDueling || $PSession->isDueling) {
                    return;
                }
                if ($PSession->getOpponent() === null && $DSession->getOpponent() === null) {
                    $opponentName = $damager->getName();
                    $PSession->setOpponent($opponentName);
                    $DSession->setOpponent($player->getName());
                    $msg = PracticeCore::getPrefixCore() . '§7You are now in combat with §c' . $opponentName;
                    $player->sendMessage($msg);
                    $damager->sendMessage($msg);
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
        PracticeCore::getUtils()->setLobbyItem($player);
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
