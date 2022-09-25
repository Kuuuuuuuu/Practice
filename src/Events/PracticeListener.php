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
use Kuu\PracticeConfig;
use Kuu\PracticeCore;
use Kuu\PracticePlayer;
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
use pocketmine\event\player\PlayerChangeSkinEvent;
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
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\Item;
use pocketmine\item\ItemIds;
use pocketmine\item\VanillaItems;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\AnimatePacket;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\types\inventory\UseItemOnEntityTransactionData;
use pocketmine\network\mcpe\protocol\types\LevelSoundEvent;
use pocketmine\network\query\DedicatedQueryNetworkInterface;
use pocketmine\permission\DefaultPermissions;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;
use pocketmine\world\World;
use Throwable;

class PracticeListener extends AbstractListener
{

    public function onCreation(PlayerCreationEvent $event): void
    {
        $event->setPlayerClass(PracticePlayer::class);
    }

    public function onUse(PlayerItemUseEvent $event): void
    {
        $player = $event->getPlayer();
        $item = $event->getItem();
        $name = $player->getName();
        if ($player instanceof PracticePlayer) {
            if ($player->getEditKit() !== null) {
                if ($item->getId() === ItemIds::ENDER_PEARL || $item->getId() === ItemIds::GOLDEN_APPLE) {
                    $event->cancel();
                }
            } else {
                switch ($item->getCustomName()) {
                    case '§r§dPlay':
                        PracticeCore::getFormUtils()->Form1($player);
                        break;
                    case '§r§dSettings':
                        PracticeCore::getFormUtils()->settingsForm($player);
                        break;
                    case '§r§dBot':
                        PracticeCore::getFormUtils()->botForm($player);
                        break;
                    case '§r§cLeave Queue':
                        $player->sendMessage(PracticeCore::getPrefixCore() . 'Left the queue');
                        $player->setCurrentKit(null);
                        $player->setInQueue(false);
                        $player->setLobbyItem();
                        break;
                    case '§r§dDuel':
                        PracticeCore::getFormUtils()->duelForm($player);
                        break;
                    case '§r§dProfile':
                        PracticeCore::getFormUtils()->ProfileForm($player, null);
                        break;
                    case '§r§eLeap§r':
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
                        break;
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
                $player->kick(str_replace(['{day}', '{hour}', '{minute}', '{second}', '{reason}', '{staff}'], [$day, $hour, $minute, $second, $reason, $staff], PracticeCore::getInstance()->MessageData['LoginBanMessage']));
                $event->cancel();
                $player->close();
            } else {
                PracticeCore::getInstance()->BanData->query("DELETE FROM banPlayers WHERE player = '$banplayer';");
            }
        } else {
            $player->teleport(Server::getInstance()->getWorldManager()->getDefaultWorld()?->getSafeSpawn());
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
        $event->setAmount(0);
    }

    public function onClickBlock(PlayerInteractEvent $event): void
    {
        $block = $event->getBlock();
        $player = $event->getPlayer();
        if (!$player->getGamemode()->equals(GameMode::CREATIVE()) && ($block->getId() === ItemIds::ANVIL || $block->getId() === ItemIds::FLOWER_POT)) {
            $event->cancel();
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

    public function onChangeSkin(PlayerChangeSkinEvent $event): void
    {
        $player = $event->getPlayer();
        if ($player instanceof PracticePlayer) {
            PracticeCore::getCosmeticHandler()->setSkin($player, $player->getStuff());
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
        if ($player->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(PracticeCore::getArenaFactory()->getBuildArena())) {
            if ($block->getId() === BlockLegacyIds::WOOL || $block->getId() === BlockLegacyIds::COBWEB) {
                $ev->setDropsVariadic(VanillaItems::AIR());
                if ($block->getId() === BlockLegacyIds::WOOL) {
                    $player->getInventory()->addItem(VanillaBlocks::WOOL()->asItem());
                    PracticeCore::getDeleteBlockHandler()->setBlockBuild($block, true);
                }
            }
        } elseif (!$player->hasPermission(DefaultPermissions::ROOT_OPERATOR) && !$player->isDueling()) {
            $ev->cancel();
        }
    }

    public function onPlace(BlockPlaceEvent $ev): void
    {
        $player = $ev->getPlayer();
        $block = $ev->getBlock();
        if ($player->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(PracticeCore::getArenaFactory()->getBuildArena())) {
            PracticeCore::getDeleteBlockHandler()->setBlockBuild($block);
        } elseif (!$player->hasPermission(DefaultPermissions::ROOT_OPERATOR) && !$player->isDueling()) {
            $ev->cancel();
        }
    }

    /**
     * @throws Exception
     */
    public function potionHitEvent(ProjectileHitEvent $event): void
    {
        $type = $event->getEntity();
        $owner = $type->getOwningEntity();
        $owner?->setHealth($owner->getHealth() + 4);
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
                }
            }
        } elseif ($packet->pid() === AnimatePacket::NETWORK_ID) {
            Server::getInstance()->broadcastPackets($player->getViewers(), [$event->getPacket()]);
            $event->cancel();
        }
    }

    public function onItemMoved(InventoryTransactionEvent $event): void
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
        if ($player instanceof PracticePlayer) {
            $event->setFormat($player->getChatFormat($message));
            if ($player->getEditKit() !== null) {
                $event->cancel();
                if (mb_strtolower($args[0]) === 'confirm') {
                    $player->saveKit();
                } else {
                    $player->sendMessage(PracticeCore::getPrefixCore() . '§aType §l§cConfirm §r§a to confirm');
                }
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
    public function onInterrupt(EntityDamageByEntityEvent $event): void
    {
        $player = $event->getEntity();
        $damager = $event->getDamager();
        if ($damager instanceof PracticePlayer && $player instanceof PracticePlayer) {
            if ($damager->getWorld() === Server::getInstance()->getWorldManager()->getDefaultWorld()) {
                if ($damager->getInventory()->getItem($damager->getInventory()->getHeldItemIndex())->getName() === '§r§dProfile') {
                    PracticeCore::getFormUtils()->ProfileForm($damager, $player);
                }
                $event->cancel();
            }
            if (($damager->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(PracticeCore::getArenaFactory()->getKnockbackArena()))) {
                $x = $player->getLocation()->getX();
                $y = $player->getLocation()->getY();
                $z = $player->getLocation()->getZ();
                $safex = $player->getWorld()->getSafeSpawn()->getX();
                $safey = $player->getWorld()->getSafeSpawn()->getY();
                $safez = $player->getWorld()->getSafeSpawn()->getZ();
                $protect = PracticeConfig::RadiusSpawnProtect;
                if (abs($safex - $x) < $protect && abs($safey - $y) < $protect && abs($safez - $z) < $protect) {
                    $event->cancel();
                    $damager->sendMessage(PracticeConfig::PREFIX . "§r§cYou can't hit the players here!");
                } else {
                    $player->knockBack(5, 0.4, 2);
                }
            } elseif ($damager->getWorld() !== Server::getInstance()->getWorldManager()->getDefaultWorld()) {
                if ($player->getOpponent() === null && $damager->getOpponent() === null) {
                    $player->setOpponent($damager->getName());
                    $damager->setOpponent($player->getName());
                    foreach ([$player, $damager] as $p) {
                        /* @var PracticePlayer $p */
                        $p->sendMessage(PracticeCore::getInstance()->MessageData['StartCombat']);
                        $p->setCombat(true);
                    }
                }
                if ($damager->getWorld() !== Server::getInstance()->getWorldManager()->getWorldByName(PracticeCore::getArenaFactory()->getOITCArena()) && $damager->getWorld() !== Server::getInstance()->getWorldManager()->getWorldByName(PracticeCore::getArenaFactory()->getKnockbackArena()) && $damager->getWorld() !== Server::getInstance()->getWorldManager()->getWorldByName(PracticeCore::getArenaFactory()->getBuildArena())) {
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
                                if ($damager->BoxingPoint < 100) {
                                    $damager->BoxingPoint++;
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
    }

    /**
     *
     * @throws Exception
     */
    public function onDamage(EntityDamageEvent $event): void
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
            if ($player->getOpponent() !== null) {
                $damager = Server::getInstance()->getPlayerByPrefix($player->getOpponent());
                if ($damager instanceof PracticePlayer) {
                    $dname = $damager->getName() ?? 'Unknown';
                    if ($damager->isAlive()) {
                        $arena = $damager->getWorld()->getDisplayName();
                        if ($arena === PracticeCore::getArenaFactory()->getOITCArena()) {
                            if ($damager->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(PracticeCore::getArenaFactory()->getOITCArena())) {
                                $damager->getInventory()->clearAll();
                                $damager->getArmorInventory()->clearAll();
                                $damager->setHealth(20);
                                $damager->getInventory()->setItem(0, VanillaItems::STONE_SWORD()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 32000))->addEnchantment(new EnchantmentInstance(VanillaEnchantments::SHARPNESS(), 1)));
                                $damager->getInventory()->setItem(1, VanillaItems::BOW()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 32000))->addEnchantment(new EnchantmentInstance(VanillaEnchantments::POWER(), 500))->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 10)));
                                $damager->getInventory()->setItem(8, VanillaItems::ARROW());
                            }
                        } elseif ($arena === PracticeCore::getArenaFactory()->getBuildArena()) {
                            if ($damager->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(PracticeCore::getArenaFactory()->getBuildArena())) {
                                $damager->getInventory()->clearAll();
                                $damager->getArmorInventory()->clearAll();
                                $damager->setHealth(20);
                                try {
                                    foreach (PracticeCore::getInstance()->KitData->get($damager->getName()) as $slot => $item) {
                                        $damager->getInventory()->setItem($slot, Item::jsonDeserialize($item));
                                    }
                                } catch (Throwable) {
                                    $damager->getInventory()->setItem(0, VanillaItems::IRON_SWORD()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 10)));
                                    $damager->getInventory()->addItem(VanillaItems::GOLDEN_APPLE()->setCount(3));
                                    $damager->getInventory()->addItem(VanillaItems::ENDER_PEARL()->setCount(2));
                                    $damager->getInventory()->addItem(VanillaBlocks::WOOL()->asItem()->setCount(128));
                                    $damager->getInventory()->addItem(VanillaBlocks::COBWEB()->asItem());
                                    $damager->getInventory()->addItem(VanillaItems::SHEARS()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 10)));
                                }
                            }
                            $damager->getArmorInventory()->setHelmet(VanillaItems::IRON_HELMET()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 32000))->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 1)));
                            $damager->getArmorInventory()->setChestplate(VanillaItems::IRON_CHESTPLATE()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 32000))->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 1)));
                            $damager->getArmorInventory()->setLeggings(VanillaItems::IRON_LEGGINGS()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 32000))->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 1)));
                            $damager->getArmorInventory()->setBoots(VanillaItems::IRON_BOOTS()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 32000))->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 1)));
                        } elseif ($arena === PracticeCore::getArenaFactory()->getBoxingArena()) {
                            if ($damager->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(PracticeCore::getArenaFactory()->getBoxingArena())) {
                                $damager->setHealth(20);
                            }
                        } elseif ($arena === PracticeCore::getArenaFactory()->getComboArena()) {
                            if ($damager->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(PracticeCore::getArenaFactory()->getComboArena())) {
                                $damager->getInventory()->clearAll();
                                $item = VanillaItems::ENCHANTED_GOLDEN_APPLE()->setCount(3);
                                $damager->getInventory()->addItem($item);
                            }
                        }
                    }
                    foreach ([$damager, $player] as $p) {
                        if ($p instanceof PracticePlayer) {
                            $p->setCombat(false);
                        }
                    }
                    $player->setLobbyItem();
                    $player->getInventory()->clearAll();
                    $player->getArmorInventory()->clearAll();
                    $player->getOffHandInventory()->clearAll();
                    $player->addDeath();
                    $damager->addKill();
                    PracticeCore::getPracticeUtils()->handleStreak($damager, $player);
                    foreach ([$player, $damager] as $p) {
                        $p->sendMessage(PracticeCore::getPrefixCore() . '§a' . $name . ' §fhas been killed by §c' . $dname);
                    }
                    $damager->setHealth(20);
                }
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
        if ($player instanceof PracticePlayer) {
            $player->setLobbyItem();
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
        if (($player instanceof PracticePlayer) && isset(PracticeConfig::BanCommand[$cmd])) {
            if ($player->isCombat() || $player->isDueling()) {
                $event->cancel();
                $player->sendMessage(PracticeCore::getInstance()->MessageData['CantUseWantCombat']);
            } elseif ($player->getEditKit() !== null) {
                $event->cancel();
                $player->sendMessage(PracticeCore::getInstance()->MessageData['CantUseeditkit']);
            }
        }
    }

    public function onNetworkRegister(NetworkInterfaceRegisterEvent $event): void
    {
        $interface = $event->getInterface();
        if ($interface instanceof DedicatedQueryNetworkInterface) {
            $event->cancel();
        }
    }
}