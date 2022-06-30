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
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\entity\projectile\Arrow;
use pocketmine\entity\Skin;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityShootBowEvent;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\entity\ProjectileHitBlockEvent;
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
use pocketmine\event\player\PlayerMoveEvent;
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
            }
            if (!$player->isSkillCooldown()) {
                if ($item->getCustomName() === '§r§6Reaper') {
                    $player->sendMessage(PracticeCore::getInstance()->MessageData['StartSkillMessage']);
                    foreach (Server::getInstance()->getWorldManager()->getWorldByName(PracticeCore::getArenaFactory()->getKitPVPArena())?->getPlayers() as $p) {
                        if (($p->getName() !== $name) && $player->getPosition()->distance($p->getPosition()) <= 10) {
                            $player->getEffects()->add(new EffectInstance(VanillaEffects::INVISIBILITY(), 120, 1, false));
                            $p->getEffects()->add(new EffectInstance(VanillaEffects::WEAKNESS(), 120, 1, false));
                            $p->getEffects()->add(new EffectInstance(VanillaEffects::BLINDNESS(), 120, 1, false));
                            $player->getArmorInventory()->setHelmet(VanillaItems::WITHER_SKELETON_SKULL()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 4)));
                            $player->setSkillCooldown(true);
                        }
                    }
                } elseif ($item->getCustomName() === '§r§6Ultimate Tank') {
                    $player->sendMessage(PracticeCore::getInstance()->MessageData['StartSkillMessage']);
                    $player->getEffects()->add(new EffectInstance(VanillaEffects::REGENERATION(), 120, 1, false));
                    $player->getEffects()->add(new EffectInstance(VanillaEffects::RESISTANCE(), 120, 1, false));
                    $player->getEffects()->add(new EffectInstance(VanillaEffects::HEALTH_BOOST(), 120, 1, false));
                    $player->setSkillCooldown(true);
                } elseif ($item->getCustomName() === '§r§6Ultimate Boxing') {
                    $player->getEffects()->add(new EffectInstance(VanillaEffects::STRENGTH(), 120, 1, false));
                    $player->getEffects()->add(new EffectInstance(VanillaEffects::RESISTANCE(), 120, 1, false));
                    $player->sendMessage(PracticeCore::getInstance()->MessageData['StartSkillMessage']);
                    $player->setSkillCooldown(true);
                } elseif ($item->getCustomName() === '§r§6Ultimate Bower') {
                    $player->getEffects()->add(new EffectInstance(VanillaEffects::STRENGTH(), 120, 1, false));
                    $player->getEffects()->add(new EffectInstance(VanillaEffects::RESISTANCE(), 120, 1, false));
                    $player->getEffects()->add(new EffectInstance(VanillaEffects::SPEED(), 120, 3, false));
                    $player->getEffects()->add(new EffectInstance(VanillaEffects::JUMP_BOOST(), 120, 3, false));
                    $player->sendMessage(PracticeCore::getInstance()->MessageData['StartSkillMessage']);
                    $player->setSkillCooldown(true);
                } elseif ($item->getCustomName() === '§r§6Teleport') {
                    $player->sendMessage(PracticeCore::getInstance()->MessageData['StartSkillMessage']);
                    foreach (Server::getInstance()->getWorldManager()->getWorldByName(PracticeCore::getArenaFactory()->getKitPVPArena())?->getPlayers() as $p) {
                        if ($p->getName() !== $name) {
                            $player->teleport($p->getPosition()->asVector3());
                            $player->setSkillCooldown(true);
                        }
                    }
                } elseif ($item->getCustomName() === '§r§eLeap§r') {
                    $directionvector = $player->getDirectionVector()->multiply(4 / 2);
                    $dx = $directionvector->getX();
                    $dy = $directionvector->getY();
                    $dz = $directionvector->getZ();
                    $player->setMotion(new Vector3($dx, $dy + 0.5, $dz));
                    $player->setSkillCooldown(true);
                }
                PracticeCore::getInstance()->getPracticeUtils()->SkillCooldown($player);
            }
            if ($item->getCustomName() === '§r§dPlay') {
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
                /*} elseif ($player->getInventory()->getItem($player->getInventory()->getHeldItemIndex())->getId() === ItemIds::ENDER_PEARL) {
                    if ($player->getWorld() !== Server::getInstance()->getWorldManager()->getWorldByName(PracticeCore::getArenaFactory()->getBuildArena()) && !$player->isEnderPearlCooldown()) {
                        $player->setEnderPearlCooldown(true);
                    }*/
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
        $ev->getQueryInfo()->setWorld('PracticeLobby');
        $ev->getQueryInfo()->setPlugins([PracticeCore::getInstance()]);
        $ev->getQueryInfo()->setMaxPlayerCount($ev->getQueryInfo()->getPlayerCount() + 1);
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
                $event->setKickReason(3, PracticeCore::getInstance()->MessageData['AntiCheatName'] . '§cThis player is already online!');
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
        $player = $event->getPlayer();
        if ($player->getHungerManager()->getFood() < 20) {
            $player->getHungerManager()->setFood(20);
        }
    }

    public function onClickBlock(PlayerInteractEvent $event): void
    {
        $block = $event->getBlock();
        $player = $event->getPlayer();
        if ($player->getGamemode() !== Gamemode::CREATIVE()) {
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

    /**
     * @throws JsonException
     */
    public function onChangeSkin(PlayerChangeSkinEvent $event): void
    {
        $case = 0;
        $player = $event->getPlayer();
        $name = $player->getName();
        $cosmetic = PracticeCore::getCosmeticHandler();
        if ($player instanceof PracticePlayer) {
            if (strlen($event->getNewSkin()->getSkinData()) >= 131072 || strlen($event->getNewSkin()->getSkinData()) <= 8192 || $cosmetic->getSkinTransparencyPercentage($event->getNewSkin()->getSkinData()) > 6) {
                copy($cosmetic->stevePng, $cosmetic->saveSkin . "$name.png");
                $cosmetic->resetSkin($player);
                $case = 1;
            } else {
                $skin = new Skin($event->getNewSkin()->getSkinId(), $event->getNewSkin()->getSkinData(), '', $event->getNewSkin()->getGeometryName() !== 'geometry.humanoid.customSlim' ? 'geometry.humanoid.custom' : $event->getNewSkin()->getGeometryName(), '');
                $cosmetic->saveSkin($skin->getSkinData(), $name);
            }
            if ($player->getStuff() !== '') {
                $cosmetic->setSkin($player, $player->getStuff());
            } elseif ($player->getCape() !== '') {
                $capedata = $cosmetic->createCape($player->getCape());
                if ($case === 1) {
                    $player->setSkin(new Skin($player->getSkin()->getSkinId(), $player->getSkin()->getSkinData(), $capedata, $player->getSkin()->getGeometryName() !== 'geometry.humanoid.customSlim' ? 'geometry.humanoid.custom' : $player->getSkin()->getGeometryName(), ''));
                } else {
                    $player->setSkin(new Skin($event->getNewSkin()->getSkinId(), $event->getNewSkin()->getSkinData(), $capedata, $event->getNewSkin()->getGeometryName() !== 'geometry.humanoid.customSlim' ? 'geometry.humanoid.custom' : $event->getNewSkin()->getGeometryName(), ''));
                }
            } elseif ($case === 1) {
                $player->setSkin(new Skin($player->getSkin()->getSkinId(), $player->getSkin()->getSkinData(), '', $player->getSkin()->getGeometryName() !== 'geometry.humanoid.customSlim' ? 'geometry.humanoid.custom' : $player->getSkin()->getGeometryName(), ''));
            } else {
                $player->setSkin(new Skin($event->getNewSkin()->getSkinId(), $event->getNewSkin()->getSkinData(), '', $event->getNewSkin()->getGeometryName() !== 'geometry.humanoid.customSlim' ? 'geometry.humanoid.custom' : $event->getNewSkin()->getGeometryName(), ''));
            }
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

    public function onBreak(BlockBreakEvent $ev)
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
                return false;
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
            return;
        }
        /* @var $player PracticePlayer */
        if (!$player->hasPermission(DefaultPermissions::ROOT_OPERATOR) && !$player->isDueling()) {
            $ev->cancel();
        }
    }

    public function onDataPacketSend(DataPacketSendEvent $ev): void
    {
        foreach ($ev->getPackets() as $packet) {
            if (($packet instanceof LevelSoundEventPacket) && $packet->pid() === LevelSoundEventPacket::NETWORK_ID) {
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

    public function onDataPacketReceive(DataPacketReceiveEvent $event): void
    {
        $player = $event->getOrigin()->getPlayer();
        $packet = $event->getPacket();
        if ($packet instanceof InventoryTransactionPacket || $packet instanceof LevelSoundEventPacket) {
            if (($packet::NETWORK_ID === InventoryTransactionPacket::NETWORK_ID && $packet->trData instanceof UseItemOnEntityTransactionData) || ($packet::NETWORK_ID === LevelSoundEventPacket::NETWORK_ID && $packet->sound === LevelSoundEvent::ATTACK_NODAMAGE)) {
                if ($player instanceof PracticePlayer) {
                    PracticeCore::getClickHandler()->addClick($player);
                    if (PracticeCore::getClickHandler()->getClicks($player) >= PracticeConfig::MaximumCPS) {
                        $player->setLastDamagePlayer('Unknown');
                        $player->kill();
                    }
                }
            }
        } elseif ($event->getPacket()->pid() === AnimatePacket::NETWORK_ID) {
            Server::getInstance()->broadcastPackets($player->getViewers(), [$event->getPacket()]);
            $event->cancel();
        }
    }

    public function onItemMoved(InventoryTransactionEvent $event): void
    {
        $transaction = $event->getTransaction();
        $player = $transaction->getSource();
        if ($player instanceof PracticePlayer) {
            if ($player->getEditKit() !== null || $player->isDueling() || $player->getGamemode() === GameMode::CREATIVE()) {
                return;
            }
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
                $web = new DiscordWebhook(PracticeCore::getInstance()->getConfig()->get('api'));
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
    public function onInterrupt(EntityDamageByEntityEvent $event): void
    {
        $player = $event->getEntity();
        $damager = $event->getDamager();
        if ($player instanceof PracticePlayer && $damager instanceof PracticePlayer) {
            if ($damager->getWorld() !== Server::getInstance()->getWorldManager()->getDefaultWorld()) {
                $damager->setLastDamagePlayer($player->getName());
                $player->setLastDamagePlayer($damager->getName());
            } else {
                if ($damager->getInventory()->getItem($damager->getInventory()->getHeldItemIndex())->getName() === '§r§dProfile') {
                    PracticeCore::getFormUtils()->ProfileForm($damager, $player);
                }
                $event->cancel();
            }
            if ($damager->getWorld() !== Server::getInstance()->getWorldManager()->getDefaultWorld() && $damager->getWorld() !== Server::getInstance()->getWorldManager()->getWorldByName(PracticeCore::getArenaFactory()->getKnockbackArena()) && $damager->getWorld() !== Server::getInstance()->getWorldManager()->getWorldByName(PracticeCore::getArenaFactory()->getOITCArena()) && $damager->getWorld() !== Server::getInstance()->getWorldManager()->getWorldByName(PracticeCore::getArenaFactory()->getKitPVPArena())) {
                if (($damager->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(PracticeCore::getArenaFactory()->getKnockbackArena())) && $damager->getInventory()->getItemInHand()->getId() === ItemIds::STICK) {
                    $player->knockBack(0.4, 0.4, 2);
                }
                if ($player->getOpponent() === null && $damager->getOpponent() === null) {
                    $player->setOpponent($damager->getName());
                    $damager->setOpponent($player->getName());
                    foreach ([$player, $damager] as $p) {
                        /* @var PracticePlayer $p */
                        $p->sendMessage(PracticeCore::getInstance()->MessageData['StartCombat']);
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
                            if ($damager->BoxingPoint < 100) {
                                $damager->BoxingPoint++;
                                PracticeCore::getScoreboardManager()->Boxing($p);
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
    public function onDamage(EntityDamageEvent $event): void
    {
        $entity = $event->getEntity();
        if ($entity instanceof PracticePlayer) {
            switch ($event->getCause()) {
                case EntityDamageEvent::CAUSE_VOID:
                    if ($entity->getWorld() === Server::getInstance()->getWorldManager()->getDefaultWorld()) {
                        $event->cancel();
                        $entity->teleport(Server::getInstance()->getWorldManager()->getDefaultWorld()?->getSafeSpawn());
                    }
                    break;
                case EntityDamageEvent::CAUSE_FALL:
                    $event->cancel();
                    break;
                case EntityDamageEvent::CAUSE_SUFFOCATION:
                    $event->cancel();
                    $entity->teleport(new Vector3($entity->getPosition()->getX(), $entity->getPosition()->getY() + 3, $entity->getPosition()->getZ()));
                    break;
                case EntityDamageEvent::CAUSE_PROJECTILE:
                    try {
                        if ($event->getChild() instanceof Arrow) {
                            $owner = $event->getChild()?->getOwningEntity();
                            if ($owner instanceof Player) {
                                $name = $owner->getName();
                                if ($name === $entity->getName()) {
                                    $event->cancel();
                                    $entity->sendMessage(PracticeCore::getPrefixCore() . "§cYou can't attack yourself!");
                                }
                            }
                        }
                    } catch (Throwable) {
                        $event->cancel();
                    }
                    break;
            }
        }
    }

    public function onMove(PlayerMoveEvent $event): void
    {
        $player = $event->getPlayer();
        //$block = $player->getWorld()->getBlock(new Vector3($player->getPosition()->getX(), $player->getPosition()->asPosition()->getY() - 0.5, $player->getPosition()->asPosition()->getZ()));
        if ($player->getPosition()->getY() <= 1) {
            $player->kill();
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
        if ($interface instanceof RakLibInterface && !$interface instanceof PracticeRaklibInterface) {
            $event->cancel();
            $server = Server::getInstance();
            $server->getNetwork()->registerInterface(new PracticeRaklibInterface($server, $server->getIp(), $server->getPort(), PracticeConfig::IPV6));
        } elseif ($interface instanceof DedicatedQueryNetworkInterface) {
            $event->cancel();
        }
    }
}