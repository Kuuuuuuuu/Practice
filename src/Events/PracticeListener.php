<?php

/** @noinspection PhpIllegalStringOffsetInspection */
/** @noinspection PhpInconsistentReturnPointsInspection */
/** @noinspection PhpParamsInspection */
/** @noinspection PhpPossiblePolymorphicInvocationInspection */

declare(strict_types=1);

namespace Kuu\Events;

use Kuu\Misc\AbstractListener;
use Kuu\PracticeConfig;
use Kuu\PracticeCore;
use Kuu\PracticePlayer;
use Kuu\Task\OncePearlTask;
use pocketmine\entity\projectile\SplashPotion;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\entity\ProjectileHitBlockEvent;
use pocketmine\event\inventory\CraftItemEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
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
use pocketmine\event\server\CommandEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\event\server\NetworkInterfaceRegisterEvent;
use pocketmine\event\server\QueryRegenerateEvent;
use pocketmine\event\world\WorldLoadEvent;
use pocketmine\item\ItemIds;
use pocketmine\item\PotionType;
use pocketmine\item\VanillaItems;
use pocketmine\math\Vector3;
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

class PracticeListener extends AbstractListener
{
    /**
     * @param PlayerCreationEvent $event
     * @return void
     */
    public function onCreation(PlayerCreationEvent $event): void
    {
        $event->setPlayerClass(PracticePlayer::class);
    }

    /**
     * @param PlayerItemUseEvent $event
     * @return void
     * @priority LOWEST
     */
    public function onUse(PlayerItemUseEvent $event): void
    {
        $player = $event->getPlayer();
        $item = $event->getItem();
        if ($item->getId() === VanillaItems::ENDER_PEARL()->getId()) {
            $session = PracticeCore::getPlayerSession()::getSession($player);
            if ($session->PearlCooldown < 1) {
                PracticeCore::getInstance()->getScheduler()->scheduleRepeatingTask(new OncePearlTask($player), 20);
            } else {
                $event->cancel();
            }
        } elseif ($item->getCustomName() === '§r§bPlay') {
            PracticeCore::getFormUtils()->Form1($player);
        } elseif ($item->getCustomName() === '§r§bSettings') {
            PracticeCore::getFormUtils()->settingsForm($player);
        }
    }

    /**
     * @param QueryRegenerateEvent $ev
     * @return void
     * @priority LOWEST
     */
    public function onQuery(QueryRegenerateEvent $ev): void
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
    public function onDropItem(PlayerDropItemEvent $event): void
    {
        $event->cancel();
    }

    /**
     * @param PlayerLoginEvent $event
     * @return void
     * @priority LOWEST
     */
    public function onLogin(PlayerLoginEvent $event): void
    {
        $player = $event->getPlayer();
        $banplayer = $player->getName();
        $banInfo = PracticeCore::getInstance()->BanData->query("SELECT * FROM banPlayers WHERE player = '$banplayer';");
        /** @phpstan-ignore-next-line */
        $array = $banInfo->fetchArray(SQLITE3_ASSOC);
        if (!empty($array)) {
            $banTime = $array['banTime'];
            $reason = $array['reason'];
            $staff = $array['staff'];
            $now = time();
            if ($banTime > $now) {
                $remainingTime = $banTime - $now;
                $day = floor($remainingTime / 86400);
                $hourSeconds = $remainingTime % 86400;
                $hour = floor($hourSeconds / 3600);
                $minuteSec = $hourSeconds % 3600;
                $minute = floor($minuteSec / 60);
                $remainingSec = $minuteSec % 60;
                $second = ceil($remainingSec);
                $player->kick(str_replace(['{day}', '{hour}', '{minute}', '{second}', '{reason}', '{staff}'], [$day, $hour, $minute, $second, $reason, $staff], "§cYou Are Banned\n§6Reason : §f{reason}\n§6Unban At §f: §e{day} D §f| §e{hour} H §f| §e{minute} M"));
                $event->cancel();
                $player->close();
            } else {
                PracticeCore::getInstance()->BanData->query("DELETE FROM banPlayers WHERE player = '$banplayer';");
            }
        }
    }

    /**
     * @param PlayerPreLoginEvent $event
     * @return void
     * @priority LOWEST
     */

    public function onPlayerPreLogin(PlayerPreLoginEvent $event): void
    {
        foreach (Server::getInstance()->getOnlinePlayers() as $p) {
            if ($p->getUniqueId() !== $event->getPlayerInfo()->getUuid() && strtolower($event->getPlayerInfo()->getUsername()) === strtolower($p->getName())) {
                $event->setKickReason(3, PracticeConfig::PREFIX . '§cThis player is already online!');
            }
        }
    }

    /**
     * @param PlayerJoinEvent $event
     * @return void
     * @priority LOWEST
     */
    public function onJoin(PlayerJoinEvent $event): void
    {
        $player = $event->getPlayer();
        $name = $player->getName();
        $event->setJoinMessage('§f[§a+§f] §a' . $name);
        $player->sendMessage(PracticeCore::getPrefixCore() . '§eLoading Data...');
        PracticeCore::getPracticeUtils()->setLobbyItem($player);
        PracticeCore::getPlayerHandler()->loadPlayerData($player);
    }

    /**
     * @param PlayerExhaustEvent $event
     * @return void
     * @priority LOWEST
     */

    public function onExhaust(PlayerExhaustEvent $event): void
    {
        $event->setAmount(0);
    }

    /**
     * @param PlayerInteractEvent $event
     * @return void
     * @priority LOWEST
     */
    public function onClickBlock(PlayerInteractEvent $event): void
    {
        $block = $event->getBlock();
        $player = $event->getPlayer();
        if ($player->getGamemode() !== GameMode::CREATIVE() && ($block->getIdInfo()->getBlockId() === ItemIds::ANVIL || $block->getIdInfo()->getBlockId() == ItemIds::FLOWER_POT)) {
            $event->cancel();
        }
    }

    /**
     * @param CraftItemEvent $event
     * @return void
     * @priority LOWEST
     */
    public function onCraft(CraftItemEvent $event): void
    {
        $event->cancel();
    }

    /**
     * @param WorldLoadEvent $event
     * @return void
     * @priority LOWEST
     */
    public function onLevelLoadEvent(WorldLoadEvent $event): void
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
    public function onBreak(BlockBreakEvent $ev): void
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
    public function onPlace(BlockPlaceEvent $ev): void
    {
        $player = $ev->getPlayer();
        if (!$player->hasPermission(DefaultPermissions::ROOT_OPERATOR) && $player->getGamemode() !== GameMode::CREATIVE()) {
            $ev->cancel();
        }
    }

    /**
     * @param ProjectileHitBlockEvent $event
     * @return void
     * @priority LOWEST
     */
    public function onProjectileHitBlock(ProjectileHitBlockEvent $event): void
    {
        $projectile = $event->getEntity();
        if ($projectile instanceof SplashPotion && $projectile->getPotionType() === PotionType::STRONG_HEALING()) {
            $player = $projectile->getOwningEntity();
            if ($player instanceof Player && $player->isAlive() && $projectile->getPosition()->distance($player->getPosition()) <= 3) {
                $player->setHealth($player->getHealth() + 3.5);
            }
        }
    }

    /**
     * @param DataPacketSendEvent $ev
     * @return void
     * @priority LOWEST
     */
    public function onDataPacketSend(DataPacketSendEvent $ev): void
    {
        foreach ($ev->getPackets() as $packet) {
            if (($packet instanceof LevelSoundEventPacket) && $packet->pid() === LevelSoundEventPacket::NETWORK_ID) {
                $sound = $packet->sound;
                if ($sound === LevelSoundEvent::ATTACK || $sound === LevelSoundEvent::ATTACK_NODAMAGE || $sound === LevelSoundEvent::ATTACK_STRONG) {
                    $ev->cancel();
                }
            }
        }
    }

    /**
     * @param DataPacketReceiveEvent $event
     * @return void
     * @priority LOWEST
     */
    public function onDataPacketReceive(DataPacketReceiveEvent $event): void
    {
        $player = $event->getOrigin()->getPlayer();
        $packet = $event->getPacket();
        if ($player instanceof Player) {
            if (($packet instanceof InventoryTransactionPacket && $packet->trData instanceof UseItemOnEntityTransactionData) || ($packet instanceof LevelSoundEventPacket && $packet->sound === LevelSoundEvent::ATTACK_NODAMAGE)) {
                PracticeCore::getClickHandler()->addClick($player);
            } elseif ($packet instanceof AnimatePacket) {
                Server::getInstance()->broadcastPackets($player->getViewers(), [$packet]);
                $event->cancel();
            }
        }
    }

    /**
     * @param InventoryTransactionEvent $event
     * @return void
     * @priority LOWEST
     */
    public function onItemMoved(InventoryTransactionEvent $event): void
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
    public function onChat(PlayerChatEvent $event): void
    {
        $player = $event->getPlayer();
        $message = $event->getMessage();
        $event->setFormat(PracticeCore::getPracticeUtils()->getChatFormat($player, $message));
    }

    /**
     * @param PlayerQuitEvent $event
     * @return void
     */
    public function onQuit(PlayerQuitEvent $event): void
    {
        $player = $event->getPlayer();
        $session = PracticeCore::getPlayerSession()::getSession($player);
        $name = $player->getName();
        $event->setQuitMessage('§f[§c-§f] §c' . $name);
        PracticeCore::getClickHandler()->removePlayerClickData($player);
        PracticeCore::getPlayerHandler()->savePlayerData($player);
        $player->setGamemode(GameMode::SURVIVAL());
        if ($session->isCombat()) {
            $player->kill();
        }
    }

    /**
     * @param PlayerKickEvent $event
     * @return void
     * @priority LOWEST
     */
    public function onKick(PlayerKickEvent $event): void
    {
        PracticeCore::getPlayerHandler()->savePlayerData($event->getPlayer());
    }

    /**
     * @param PluginDisableEvent $event
     * @return void
     * @priority LOWEST
     */
    public function onPluginDisabled(PluginDisableEvent $event): void
    {
        $plugin = $event->getPlugin();
        if ($plugin instanceof PracticeCore) {
            PracticeCore::getPracticeUtils()->dispose();
            foreach (PracticeCore::getPracticeUtils()->getPlayerInSession() as $player) {
                PracticeCore::getPlayerHandler()->savePlayerData($player);
            }
        }
    }

    /**
     * @param EntityDamageByEntityEvent $event
     * @return void
     * @priority LOWEST
     */
    public function onInterrupt(EntityDamageByEntityEvent $event): void
    {
        $player = $event->getEntity();
        $damager = $event->getDamager();
        if (!$event->isCancelled()) {
            if ($damager instanceof Player && $player instanceof Player && $damager->getWorld() !== Server::getInstance()->getWorldManager()->getDefaultWorld()) {
                $DamagerSession = PracticeCore::getPlayerSession()::getSession($damager);
                $PlayerSession = PracticeCore::getPlayerSession()::getSession($player);
                if ($PlayerSession->getOpponent() === null && $DamagerSession->getOpponent() === null) {
                    $PlayerSession->setOpponent($damager->getName());
                    $DamagerSession->setOpponent($player->getName());
                    foreach ([$player, $damager] as $p) {
                        $session = PracticeCore::getPlayerSession()::getSession($player);
                        $p->sendMessage(PracticeCore::getPrefixCore() . '§7You are now in combat with §c' . $session->getOpponent());
                        $session->setCombat(true);
                    }
                } elseif ($PlayerSession->getOpponent() !== null && $DamagerSession->getOpponent() !== null) {
                    if ($PlayerSession->getOpponent() === $damager->getName() && $DamagerSession->getOpponent() === $player->getName()) {
                        if ($damager->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(PracticeCore::getArenaFactory()->getBoxingArena())) {
                            $DamagerSession->BoxingPoint++;
                            if ($DamagerSession->BoxingPoint > 99) {
                                $player->kill();
                            }
                            foreach ([$player, $damager] as $p) {
                                PracticeCore::getScoreboardManager()->setBoxingScoreboard($p);
                            }
                        }
                        foreach ([$player, $damager] as $p) {
                            $session = PracticeCore::getPlayerSession()::getSession($p);
                            $session->setCombat(true);
                        }
                    } else {
                        $event->cancel();
                        $damager->sendMessage(PracticeCore::getPrefixCore() . "§cDon't Interrupt!");
                    }
                } elseif ($PlayerSession->getOpponent() !== null && $DamagerSession->getOpponent() === null) {
                    $event->cancel();
                    $damager->sendMessage(PracticeCore::getPrefixCore() . "§cDon't Interrupt!");
                } elseif ($PlayerSession->getOpponent() === null && $DamagerSession->getOpponent() !== null) {
                    $event->cancel();
                    $damager->sendMessage(PracticeCore::getPrefixCore() . "§cDon't Interrupt!");
                }
            } else {
                $event->cancel();
            }
        }
    }

    /**
     * @param EntityDamageEvent $event
     * @return void
     * @priority LOWEST
     */
    public function onDamage(EntityDamageEvent $event): void
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
    public function onDeath(PlayerDeathEvent $event): void
    {
        $event->setDeathMessage('');
        $event->setDrops([]);
        $player = $event->getPlayer();
        $name = $player->getName();
        $cause = $player->getLastDamageCause();
        $session = PracticeCore::getPlayerSession()::getSession($player);
        if (($cause instanceof EntityDamageByEntityEvent) && $session->getOpponent() !== null) {
            $damager = PracticeCore::getPracticeUtils()->getPlayerInSessionByPrefix($session->getOpponent());
            if ($damager instanceof Player) {
                $damagerSession = PracticeCore::getPlayerSession()::getSession($damager);
                $dname = $damager->getName();
                if ($damager->isAlive() && $damager->isConnected()) {
                    $arena = $damager->getWorld()->getFolderName();
                    if ($arena === PracticeCore::getArenaFactory()->getBoxingArena()) {
                        if ($damager->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(PracticeCore::getArenaFactory()->getBoxingArena())) {
                            $damager->setHealth(20);
                        }
                    } elseif ($arena === PracticeCore::getArenaFactory()->getNodebuffArena()) {
                        if ($damager->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(PracticeCore::getArenaFactory()->getNodebuffArena())) {
                            $damager->getInventory()->clearAll();
                            PracticeCore::getArenaManager()->getKitNodebuff($damager);
                        }
                    }
                }
                foreach ([$damager, $player] as $p) {
                    $session = PracticeCore::getPlayerSession()::getSession($p);
                    $session->setCombat(false);
                    $session->setOpponent(null);
                    $p->sendMessage(PracticeCore::getPrefixCore() . '§a' . $name . ' §fhas been killed by §c' . $dname);
                }
                PracticeCore::getPracticeUtils()->handleStreak($damager, $player);
                $damagerSession->killStreak++;
                $damagerSession->kills++;
                $session->deaths++;
                $session->killStreak = 0;
                $damager->setHealth(20);
                $player->getInventory()->clearAll();
                $player->getArmorInventory()->clearAll();
                $player->getOffHandInventory()->clearAll();
                PracticeCore::getPracticeUtils()->setLobbyItem($player);
            }
        }
    }

    /**
     * @param PlayerRespawnEvent $event
     * @return void
     * @priority LOWEST
     */
    public function onRespawn(PlayerRespawnEvent $event): void
    {
        $player = $event->getPlayer();
        $session = PracticeCore::getPlayerSession()::getSession($player);
        $session->setCombat(false);
        $session->setOpponent(null);
        PracticeCore::getScoreboardManager()->setLobbyScoreboard($player);
        PracticeCore::getPracticeUtils()->setLobbyItem($player);
    }

    /**
     * @param EntityTeleportEvent $event
     * @return void
     * @priority LOWEST
     */
    public function onChangeWorld(EntityTeleportEvent $event): void
    {
        $player = $event->getEntity();
        if ($player instanceof Player && $event->getFrom()->getWorld() !== $event->getTo()->getWorld()) {
            $session = PracticeCore::getPlayerSession()::getSession($player);
            $session->setCombat(false);
            $session->setOpponent(null);
        }
    }

    /**
     * @param NetworkInterfaceRegisterEvent $event
     * @return void
     * @priority LOWEST
     */
    public function onNetworkRegister(NetworkInterfaceRegisterEvent $event): void
    {
        $interface = $event->getInterface();
        if ($interface instanceof RakLibInterface) {
            $interface->setPacketLimit(PHP_INT_MAX);
        } elseif ($interface instanceof DedicatedQueryNetworkInterface) {
            $event->cancel();
        }
    }
}
