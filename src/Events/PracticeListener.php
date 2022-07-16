<?php
/** @noinspection PhpIllegalStringOffsetInspection */
/** @noinspection PhpInconsistentReturnPointsInspection */
/** @noinspection PhpParamsInspection */
/** @noinspection PhpPossiblePolymorphicInvocationInspection */

declare(strict_types=1);

namespace Kuu\Events;

use Exception;
use JsonException;
use Kuu\Misc\AbstractListener;
use Kuu\Misc\PracticeRaklibInterface;
use Kuu\PracticeConfig;
use Kuu\PracticeCore;
use Kuu\PracticePlayer;
use Kuu\Utils\Discord\DiscordWebhook;
use Kuu\Utils\Discord\DiscordWebhookUtils;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\VanillaBlocks;
use pocketmine\entity\projectile\Arrow;
use pocketmine\entity\projectile\SplashPotion;
use pocketmine\entity\Skin;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityShootBowEvent;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\entity\ProjectileHitBlockEvent;
use pocketmine\event\entity\ProjectileHitEvent;
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
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\server\CommandEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\event\server\NetworkInterfaceRegisterEvent;
use pocketmine\event\server\QueryRegenerateEvent;
use pocketmine\event\world\WorldLoadEvent;
use pocketmine\item\ItemIds;
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
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;
use pocketmine\world\World;

class PracticeListener extends AbstractListener
{

    public function onCreation(PlayerCreationEvent $event): void
    {
        $event->setPlayerClass(PracticePlayer::class);
    }

    public function onUse(PlayerItemUseEvent $event): void
    {
        $player = $event->getPlayer();
        $name = $player->getName();
        $item = $event->getItem();
        if ($player instanceof PracticePlayer) {
            if ($player->getEditKit() !== null) {
                if ($item->getId() === ItemIds::ENDER_PEARL || $item->getId() === ItemIds::GOLDEN_APPLE) {
                    $event->cancel();
                }
            } elseif ($item->getCustomName() === '§r§dPlay') {
                PracticeCore::getFormUtils()->Form1($player);
            } elseif ($item->getCustomName() === '§r§dSettings') {
                PracticeCore::getFormUtils()->settingsForm($player);
            } elseif ($item->getCustomName() === '§r§dBot') {
                PracticeCore::getFormUtils()->botForm($player);
            } elseif ($item->getCustomName() === '§r§cLeave Queue') {
                $player->sendMessage(PracticeCore::getPrefixCore() . 'Left the queue');
                $player->setCurrentKit(null);
                $player->setInQueue(false);
                PracticeCore::getPracticeUtils()->GiveLobbyItem($player);
            } elseif ($item->getCustomName() === '§r§dDuel') {
                PracticeCore::getFormUtils()->duelForm($player);
            } elseif ($item->getCustomName() === '§r§dProfile') {
                PracticeCore::getFormUtils()->ProfileForm($player, null);
            } elseif ($item->getCustomName() === '§r§eLeap§r') {
                if (!isset(PracticeCore::getCaches()->LeapCooldown[$name]) || PracticeCore::getCaches()->LeapCooldown[$name] <= time()) {
                    $directionvector = $player->getDirectionVector()->multiply(4 / 2);
                    $dx = $directionvector->getX();
                    $dy = $directionvector->getY();
                    $dz = $directionvector->getZ();
                    $player->setMotion(new Vector3($dx, $dy + 0.5, $dz));
                    PracticeCore::getCaches()->LeapCooldown[$name] = time() + 10;
                } else {
                    $player->sendMessage(PracticeCore::getPrefixCore() . 'You can use leap again in ' . (10 - ((time() + 10) - (PracticeCore::getCaches()->LeapCooldown[$name] ?? 0))) . ' seconds');
                }
            }
        }
    }

    public function onProjectile(ProjectileHitBlockEvent $event): void
    {
        $entity = $event->getEntity();
        if ($entity instanceof Arrow) {
            $entity->flagForDespawn();
        }
    }

    public function onBow(EntityShootBowEvent $event): void
    {
        $entity = $event->getEntity();
        if (($entity instanceof PracticePlayer) && $entity->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(PracticeCore::getArenaFactory()->getOITCArena())) {
            PracticeCore::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($entity): void {
                if ($entity->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(PracticeCore::getArenaFactory()->getOITCArena()) && $entity->isAlive()) {
                    $entity->getInventory()->setItem(8, VanillaItems::ARROW());
                }
            }), PracticeConfig::OITCBowDelay);
        }
    }

    public function onQuery(QueryRegenerateEvent $ev): void
    {
        $query = $ev->getQueryInfo();
        $query->setWorld('PracticeLobby');
        $query->setPlugins([PracticeCore::getInstance()]);
        $query->setMaxPlayerCount($ev->getQueryInfo()->getPlayerCount() + 1);
    }

    public function onDropItem(PlayerDropItemEvent $event): void
    {
        $event->cancel();
    }

    /**
     * @throws JsonException
     */
    public function onLogin(PlayerLoginEvent $event): void
    {
        $player = $event->getPlayer();
        $name = $player->getName();
        $banplayer = $player->getName();
        $banInfo = PracticeCore::getInstance()->BanData->query("SELECT * FROM banPlayers WHERE player = '$banplayer';");
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
        } else {
            $player->getAllArtifact();
            $player->teleport(Server::getInstance()->getWorldManager()->getDefaultWorld()?->getSafeSpawn());
            PracticeCore::getInstance()->getPracticeUtils()->DeviceCheck($player);
            PracticeCore::getClickHandler()->initPlayerClickData($player);
            if ($player instanceof PracticePlayer) {
                $cosmetic = PracticeCore::getCosmeticHandler();
                $skin = new Skin($player->getSkin()->getSkinId(), $player->getSkin()->getSkinData(), '', $player->getSkin()->getGeometryName() !== 'geometry.humanoid.customSlim' ? 'geometry.humanoid.custom' : $player->getSkin()->getGeometryName(), '');
                $cosmetic->saveSkin($skin->getSkinData(), $name);
            }
        }
    }

    public function onPlayerPreLogin(PlayerPreLoginEvent $event): void
    {
        foreach (Server::getInstance()->getOnlinePlayers() as $p) {
            if ($p->getUniqueId() !== $event->getPlayerInfo()->getUuid() && strtolower($event->getPlayerInfo()->getUsername()) === strtolower($p->getName())) {
                $event->setKickReason(3, PracticeConfig::PREFIX . '§cThis player is already online!');
            }
        }
    }

    /**
     * @throws JsonException
     */
    public function onJoin(PlayerJoinEvent $event): void
    {
        $player = $event->getPlayer();
        $name = $player->getName();
        $event->setJoinMessage('§f[§a+§f] §a' . $name);
        if ($player instanceof PracticePlayer) {
            $player->onJoin();
        }
    }

    public function onExhaust(PlayerExhaustEvent $event): void
    {
        //$player = $event->getPlayer();
        $event->setAmount(0);
        //$player->getHungerManager()->setFood(20);
    }

    public function onClickBlock(PlayerInteractEvent $event): void
    {
        $block = $event->getBlock();
        $player = $event->getPlayer();
        if ($player->getGamemode() !== GameMode::CREATIVE()) {
            if ($block->getId() === ItemIds::ANVIL || $block->getId() === ItemIds::FLOWER_POT) {
                $event->cancel();
            }
        }
    }

    public function onEntityTeleport(EntityTeleportEvent $event): void
    {
        $entity = $event->getEntity();
        $from = $event->getFrom();
        $to = $event->getTo();
        if ($entity instanceof PracticePlayer && $from->getWorld() !== $to->getWorld()) {
            $entity->setCombat(false);
        }
    }

    public function onCraft(CraftItemEvent $event): void
    {
        $event->cancel();
    }

    public function onLevelLoadEvent(WorldLoadEvent $event): void
    {
        $world = $event->getWorld();
        $world->setTime(World::TIME_DAY);
        $world->stopTime();
    }

    public function onBreak(BlockBreakEvent $ev): void
    {
        $player = $ev->getPlayer();
        $block = $ev->getBlock();
        if ($player instanceof PracticePlayer) {
            if ($player->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(PracticeCore::getArenaFactory()->getBuildArena())) {
                if ($block->getId() === BlockLegacyIds::WOOL || $block->getId() === BlockLegacyIds::COBWEB) {
                    $ev->setDropsVariadic(VanillaItems::AIR());
                    if ($block->getId() === BlockLegacyIds::WOOL) {
                        $player->getInventory()->addItem(VanillaBlocks::WOOL()->asItem());
                        PracticeCore::getDeleteBlockHandler()->setBlockBuild($block, true);
                    }
                }
            } elseif ($player->isDueling() && $block->getId() === ItemIds::GRASS) {
                $ev->cancel();
            } elseif (!$player->hasPermission(DefaultPermissions::ROOT_OPERATOR) && !$player->isDueling()) {
                $ev->cancel();
            }
        }
    }

    public function onPlace(BlockPlaceEvent $ev): void
    {
        $player = $ev->getPlayer();
        $block = $ev->getBlock();
        if ($player instanceof PracticePlayer) {
            if ($player->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(PracticeCore::getArenaFactory()->getBuildArena())) {
                PracticeCore::getDeleteBlockHandler()->setBlockBuild($block);
            } elseif (!$player->hasPermission(DefaultPermissions::ROOT_OPERATOR) && !$player->isDueling()) {
                $ev->cancel();
            }
        }
    }

    /**
     * @throws Exception
     */
    public function onProjectileHit(ProjectileHitEvent $event): void
    {
        $type = $event->getEntity();
        $owner = $type->getOwningEntity();
        $owner?->setHealth($owner?->getHealth() + 4);
        if (($owner instanceof Player) && $type instanceof SplashPotion) {
            foreach ($type->getWorld()->getNearbyEntities($type->getBoundingBox()->expand(2, 5, 2)) as $entity) {
                if ($entity instanceof Player) {
                    if ($entity->getName() === $owner->getName()) {
                        continue;
                    }
                    $entity->setHealth($owner->getHealth() + random_int(2, 4));
                }
            }
        }
    }

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

    public function onDataPacketReceive(DataPacketReceiveEvent $event): void
    {
        $player = $event->getOrigin()->getPlayer();
        $packet = $event->getPacket();
        if ($packet instanceof InventoryTransactionPacket || $packet instanceof LevelSoundEventPacket) {
            if (($packet::NETWORK_ID === InventoryTransactionPacket::NETWORK_ID && $packet->trData instanceof UseItemOnEntityTransactionData) || ($packet::NETWORK_ID === LevelSoundEventPacket::NETWORK_ID && $packet->sound === LevelSoundEvent::ATTACK_NODAMAGE)) {
                if ($player instanceof PracticePlayer) {
                    PracticeCore::getClickHandler()->addClick($player);
                    if (PracticeCore::getClickHandler()->getClicks($player) >= PracticeConfig::MaximumCPS) {
                        $player->sendTitle('§eWarning!', '§cYou CPS is too high', 20, 40, 20);
                    }
                }
            }
        } elseif ($packet->pid() === AnimatePacket::NETWORK_ID) {
            Server::getInstance()->broadcastPackets($player->getViewers(), [$event->getPacket()]);
            $event->cancel();
        }
    }

    public function onInventoryTransaction(InventoryTransactionEvent $event): void
    {
        $transaction = $event->getTransaction();
        $player = $transaction->getSource();
        if (($player instanceof PracticePlayer) && $player->getEditKit() === null && !$player->isDueling() && $player->getGamemode() !== GameMode::CREATIVE()) {
            $event->cancel();
        }
    }

    /**
     * @throws JsonException
     */
    public function onChat(PlayerChatEvent $event): void
    {
        $player = $event->getPlayer();
        $message = $event->getMessage();
        $args = explode(' ', $event->getMessage());
        $event->setFormat(PracticeCore::getPracticeUtils()->getChatFormat($player, $message));
        if ($player instanceof PracticePlayer) {
            if ($player->getEditKit() !== null) {
                $event->cancel();
                if (mb_strtolower($args[0]) === 'confirm') {
                    $player->saveKit();
                } else {
                    $player->sendMessage(PracticeCore::getPrefixCore() . '§aType §l§cConfirm §r§a to confirm');
                    $player->sendMessage(PracticeCore::getPrefixCore() . '§aพิมพ์ §l§cConfirm §r§a เพื่อยืนยัน');
                }
            } else {
                $web = new DiscordWebhook(PracticeCore::getInstance()->getConfig()->get('Webhook'));
                $msg = new DiscordWebhookUtils();
                $msg2 = str_replace(['@here', '@everyone'], '', $message);
                $msg->setContent('>>> ' . $player->getNetworkSession()->getPing() . 'ms | ' . $player->PlayerOS . ' ' . $player->getDisplayName() . ' > ' . $msg2);
                $web->send($msg);
            }
        }
    }

    public function onQuit(PlayerQuitEvent $event): void
    {
        $player = $event->getPlayer();
        $name = $player->getName();
        $event->setQuitMessage('§f[§c-§f] §c' . $name);
        if ($player instanceof PracticePlayer) {
            $player->onQuit();
        }
    }

    /**
     * @throws Exception
     */
    public function onEntityDamageByEntity(EntityDamageByEntityEvent $event): void
    {
        $player = $event->getEntity();
        $damager = $event->getDamager();
        if ($damager instanceof PracticePlayer && $player instanceof PracticePlayer) {
            if ($damager->getWorld() === Server::getInstance()->getWorldManager()->getDefaultWorld()) {
                if ($damager->getInventory()->getItem($damager->getInventory()->getHeldItemIndex())->getName() === '§r§dProfile') {
                    PracticeCore::getFormUtils()->ProfileForm($damager, $player);
                }
                $event->cancel();
            } else {
                $damager->setLastDamagePlayer($player->getName());
                $player->setLastDamagePlayer($damager->getName());
            }
            if ($damager->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(PracticeCore::getArenaFactory()->getKnockbackArena()) && $damager->getInventory()->getItemInHand()->getId() === ItemIds::STICK) {
                $x = $damager->getDirectionVector()->x;
                $z = $damager->getDirectionVector()->z;
                $player->knockBack($x, $z, 0.8);
            } elseif ($damager->getWorld() !== Server::getInstance()->getWorldManager()->getDefaultWorld() && $damager->getWorld() !== Server::getInstance()->getWorldManager()->getWorldByName(PracticeCore::getArenaFactory()->getOITCArena())) {
                if ($player->getOpponent() === null && $damager->getOpponent() === null) {
                    $player->setOpponent($damager->getName());
                    $damager->setOpponent($player->getName());
                    foreach ([$player, $damager] as $p) {
                        /* @var PracticePlayer $p */
                        $p->sendMessage(PracticeCore::getPrefixCore() . '§aYou Started combat!');
                        $p->setCombat(true);
                    }
                }
                if ($player->getOpponent() !== null && $damager->getOpponent() !== null) {
                    if ($player->getOpponent() !== $damager->getName() && $damager->getOpponent() !== $player->getName()) {
                        $event->cancel();
                        $damager->sendMessage(PracticeCore::getPrefixCore() . "§cDon't Interrupt!");
                    } elseif ($player->getOpponent() === $damager->getName() && $damager->getOpponent() === $player->getName()) {
                        foreach ([$player, $damager] as $p) {
                            /* @var PracticePlayer $p */
                            $p->setCombat(true);
                        }
                        if ($damager->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(PracticeCore::getArenaFactory()->getBoxingArena())) {
                            if (!isset(PracticeCore::getCaches()->BoxingPoint[$damager->getName()])) {
                                PracticeCore::getCaches()->BoxingPoint[$damager->getName()] = 1;
                            } elseif (PracticeCore::getCaches()->BoxingPoint[$damager->getName()] <= 100) {
                                PracticeCore::getCaches()->BoxingPoint[$damager->getName()]++;
                                foreach ([$player, $damager] as $p) {
                                    /* @var PracticePlayer $p */
                                    PracticeCore::getScoreboardManager()->Boxing($p);
                                }
                            } else {
                                $player->kill();
                            }
                        }
                    }
                } elseif ($player->getOpponent() !== null && $damager->getOpponent() === null) {
                    $event->cancel();
                    $damager->sendMessage(PracticeCore::getPrefixCore() . "§cDon't Interrupt!");
                } elseif ($player->getOpponent() === null && $damager->getOpponent() !== null) {
                    $event->cancel();
                    $damager->sendMessage(PracticeCore::getPrefixCore() . "§cDon't Interrupt!");
                }
            }
        }
    }

    /**
     *
     * @throws Exception
     */
    public function onEntityDamage(EntityDamageEvent $event): void
    {
        $entity = $event->getEntity();
        if ($entity instanceof PracticePlayer) {
            switch ($event->getCause()) {
                case EntityDamageEvent::CAUSE_VOID:
                    if ($entity->getWorld() === Server::getInstance()->getWorldManager()->getDefaultWorld()) {
                        $event->cancel();
                        $entity->teleport(Server::getInstance()->getWorldManager()->getDefaultWorld()?->getSafeSpawn());
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
     * @throws Exception
     */
    public function onDeath(PlayerDeathEvent $event): void
    {
        $event->setDeathMessage('');
        $event->setDrops([]);
        $player = $event->getPlayer();
        $name = $player->getName();
        $cause = $player->getLastDamageCause();
        if (($cause instanceof EntityDamageByEntityEvent) && $player instanceof PracticePlayer) {
            $damager = Server::getInstance()->getPlayerByPrefix($player->getLastDamagePlayer());
            if ($damager instanceof PracticePlayer) {
                $dname = $damager->getName() ?? 'Unknown';
                PracticeCore::getInstance()->getPracticeUtils()->DeathReset($player, $damager, $damager->getWorld()->getFolderName());
                foreach ([$player, $damager] as $p) {
                    $p->setLastDamagePlayer('Unknown');
                    $p->sendMessage(PracticeCore::getPrefixCore() . '§a' . $name . ' §fhas been killed by §c' . $dname);
                }
                $damager->setHealth(20);
            }
        }
    }

    public function onRespawn(PlayerRespawnEvent $event): void
    {
        $player = $event->getPlayer();
        $player->getEffects()->clear();
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        $player->getOffHandInventory()->clearAll();
        PracticeCore::getInstance()->getPracticeUtils()->GiveLobbyItem($player);
        if ($player instanceof PracticePlayer) {
            if ($player->getEditKit() !== null) {
                $player->setEditKit(null);
                $player->setImmobile(false);
            }
            $player->setDueling(false);
        }
    }

    public function onCommandEvent(CommandEvent $event): void
    {
        $player = Server::getInstance()->getPlayerByPrefix($event->getSender()->getName());
        $cmd = $event->getCommand();
        foreach (PracticeConfig::BanCommand as $ban) {
            if (mb_strtolower($ban) === mb_strtolower($cmd) && $player instanceof PracticePlayer) {
                if ($player->isCombat() || $player->isDueling()) {
                    $event->cancel();
                    $player->sendMessage(PracticeCore::getPrefixCore() . "§cYou can't use this command in combat!");
                } elseif ($player->getEditKit() !== null) {
                    $event->cancel();
                    $player->sendMessage(PracticeCore::getPrefixCore() . "§cYou can't use this command in Editkit mode!");
                }
            }
        }
    }

    public function onNetworkRegister(NetworkInterfaceRegisterEvent $event): void
    {
        $interface = $event->getInterface();
        if ($interface instanceof RakLibInterface && !$interface instanceof PracticeRaklibInterface) {
            $event->cancel();
            $server = Server::getInstance();
            $server->getNetwork()->registerInterface(new PracticeRaklibInterface($server, $server->getIp(), $server->getPort(), PracticeConfig::IPV6));
        } elseif ($interface instanceof DedicatedQueryNetworkInterface) {
            $event->cancel();
        }
    }
}