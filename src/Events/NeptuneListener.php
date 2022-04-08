<?php
/** @noinspection PhpIllegalStringOffsetInspection */
/** @noinspection PhpInconsistentReturnPointsInspection */
/** @noinspection PhpParamsInspection */
/** @noinspection PhpPossiblePolymorphicInvocationInspection */

declare(strict_types=1);

namespace Kohaku\Events;

use Exception;
use JsonException;
use Kohaku\Entity\FistBot;
use Kohaku\Loader;
use Kohaku\NeptunePlayer;
use Kohaku\Task\ParkourFinishTask;
use Kohaku\Utils\DiscordUtils\DiscordWebhook;
use Kohaku\Utils\DiscordUtils\DiscordWebhookUtils;
use pocketmine\block\BlockLegacyIds;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\entity\projectile\Arrow;
use pocketmine\entity\Skin;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\event\entity\EntityShootBowEvent;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\entity\ProjectileHitBlockEvent;
use pocketmine\event\inventory\CraftItemEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChangeSkinEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
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
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\event\world\WorldLoadEvent;
use pocketmine\inventory\transaction\CraftingTransaction;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\AnimatePacket;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\types\inventory\UseItemOnEntityTransactionData;
use pocketmine\network\mcpe\protocol\types\LevelSoundEvent;
use pocketmine\permission\DefaultPermissions;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;

class NeptuneListener implements Listener
{

    public function onCreation(PlayerCreationEvent $event)
    {
        $event->setPlayerClass(NeptunePlayer::class);
    }

    public function onUse(PlayerItemUseEvent $event)
    {
        $player = $event->getPlayer();
        $item = $event->getItem();
        $name = $player->getName();
        if ($player instanceof NeptunePlayer) {
            if ($player->EditKit !== null) {
                if ($item->getId() === ItemIds::ENDER_PEARL or $item->getId() === ItemIds::GOLDEN_APPLE) {
                    $event->cancel();
                }
            }
            if ($player->SkillCooldown === false) {
                if ($item->getCustomName() === "§r§6Reaper") {
                    $player->sendMessage(Loader::getInstance()->MessageData["StartSkillMessage"]);
                    foreach (Server::getInstance()->getWorldManager()->getWorldByName(Loader::getArenaFactory()->getKitPVPArena())->getPlayers() as $p) {
                        if ($p->getName() !== $name) {
                            if ($player->getPosition()->distance($p->getPosition()) <= 10) {
                                $player->getEffects()->add(new EffectInstance(VanillaEffects::INVISIBILITY(), 120, 1, false));
                                $p->getEffects()->add(new EffectInstance(VanillaEffects::WEAKNESS(), 120, 1, false));
                                $p->getEffects()->add(new EffectInstance(VanillaEffects::BLINDNESS(), 120, 1, false));
                                $player->getArmorInventory()->setHelmet(ItemFactory::getInstance()->get(ItemIds::SKULL, 1, 1)->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 4)));
                                $player->SkillCooldown = true;
                            }
                        }
                    }
                } elseif ($item->getCustomName() === "§r§6Ultimate Tank") {
                    $player->sendMessage(Loader::getInstance()->MessageData["StartSkillMessage"]);
                    $player->getEffects()->add(new EffectInstance(VanillaEffects::REGENERATION(), 120, 1, false));
                    $player->getEffects()->add(new EffectInstance(VanillaEffects::RESISTANCE(), 120, 1, false));
                    $player->getEffects()->add(new EffectInstance(VanillaEffects::HEALTH_BOOST(), 120, 1, false));
                    $player->SkillCooldown = true;
                } elseif ($item->getCustomName() === "§r§6Ultimate Boxing") {
                    $player->getEffects()->add(new EffectInstance(VanillaEffects::STRENGTH(), 120, 1, false));
                    $player->getEffects()->add(new EffectInstance(VanillaEffects::RESISTANCE(), 120, 1, false));
                    $player->sendMessage(Loader::getInstance()->MessageData["StartSkillMessage"]);
                    $player->SkillCooldown = true;
                } elseif ($item->getCustomName() === "§r§6Ultimate Bower") {
                    $player->getEffects()->add(new EffectInstance(VanillaEffects::STRENGTH(), 120, 1, false));
                    $player->getEffects()->add(new EffectInstance(VanillaEffects::RESISTANCE(), 120, 1, false));
                    $player->getEffects()->add(new EffectInstance(VanillaEffects::SPEED(), 120, 3, false));
                    $player->getEffects()->add(new EffectInstance(VanillaEffects::JUMP_BOOST(), 120, 3, false));
                    $player->sendMessage(Loader::getInstance()->MessageData["StartSkillMessage"]);
                    $player->SkillCooldown = true;
                } elseif ($item->getCustomName() === "§r§6Teleport") {
                    $player->sendMessage(Loader::getInstance()->MessageData["StartSkillMessage"]);
                    foreach (Server::getInstance()->getWorldManager()->getWorldByName(Loader::getArenaFactory()->getKitPVPArena())->getPlayers() as $p) {
                        if ($p->getName() !== $name) {
                            $player->teleport($p->getPosition()->asVector3());
                            $player->SkillCooldown = true;
                        }
                    }
                } elseif ($item->getCustomName() === "§r§eLeap§r") {
                    $directionvector = $player->getDirectionVector()->multiply(4 / 2);
                    $dx = $directionvector->getX();
                    $dy = $directionvector->getY();
                    $dz = $directionvector->getZ();
                    $player->setMotion(new Vector3($dx, $dy + 0.5, $dz));
                    $player->SkillCooldown = true;
                }
                Loader::getInstance()->getArenaUtils()->SkillCooldown($player);
            }
            if ($item->getCustomName() === "§r§dPlay") {
                Loader::getFormUtils()->Form1($player);
            } elseif ($item->getCustomName() === "§r§dSettings") {
                Loader::getFormUtils()->settingsForm($player);
            } elseif ($item->getCustomName() === "§r§dBot") {
                Loader::getFormUtils()->botForm($player);
            } elseif ($item->getCustomName() === "§r§aStop Timer §f| §dClick to use") {
                $player->TimerData = 0;
                $player->TimerTask = false;
                $player->teleport(new Vector3(275, 66, 212));
                $player->sendMessage(Loader::getPrefixCore() . "§aYou Has been reset!");
                $player->ParkourCheckPoint = new Vector3(275, 77, 212);
            } elseif ($item->getCustomName() === "§r§aBack to Checkpoint §f| §dClick to use") {
                if ($player->ParkourCheckPoint !== null) {
                    $player->teleport($player->ParkourCheckPoint);
                } else {
                    $player->teleport(new Vector3(275, 77, 212));
                }
                $player->sendMessage(Loader::getPrefixCore() . "§aTeleport to Checkpoint");
            } elseif ($item->getCustomName() === "§r§cLeave Queue") {
                $player->sendMessage(Loader::getPrefixCore() . "Left the queue");
                $player->setCurrentKit(null);
                $player->setInQueue(false);
                Loader::getArenaUtils()->GiveItem($player);
            } elseif ($item->getCustomName() === "§r§dDuel") {
                Loader::getFormUtils()->duelForm($player);
            }
        }
    }

    public function onProjectile(ProjectileHitBlockEvent $event)
    {
        $entity = $event->getEntity();
        if ($entity instanceof Arrow) {
            $entity->flagForDespawn();
            $entity->close();
        }
    }

    public function onBow(EntityShootBowEvent $event)
    {
        $entity = $event->getEntity();
        if ($entity instanceof NeptunePlayer) {
            if ($entity->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::getArenaFactory()->getOITCArena())) {
                Loader::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($entity): void {
                    if ($entity->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::getArenaFactory()->getOITCArena())) {
                        $entity->getOffHandInventory()->setItem(0, ItemFactory::getInstance()->get(ItemIds::ARROW, 0, 1));
                    }
                }), 100);
            }
        }
    }

    public function onDropItem(PlayerDropItemEvent $event): void
    {
        $event->cancel();
    }

    /**
     * @throws JsonException
     */
    public function onLogin(PlayerLoginEvent $event)
    {
        $player = $event->getPlayer();
        $name = $player->getName();
        $banplayer = $player->getName();
        $banInfo = Loader::getInstance()->BanData->query("SELECT * FROM banPlayers WHERE player = '$banplayer';");
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
                $player->kick(str_replace(["{day}", "{hour}", "{minute}", "{second}", "{reason}", "{staff}"], [$day, $hour, $minute, $second, $reason, $staff], Loader::getInstance()->MessageData["LoginBanMessage"]));
                $event->cancel();
                $player->close();
            } else {
                Loader::getInstance()->BanData->query("DELETE FROM banPlayers WHERE player = '$banplayer';");
            }
        } else {
            $player->getAllArtifact();
            $player->teleport(Server::getInstance()->getWorldManager()->getDefaultWorld()->getSafeSpawn());
            Loader::getInstance()->getArenaUtils()->DeviceCheck($player);
            Loader::getClickHandler()->initPlayerClickData($player);
            if ($player instanceof NeptunePlayer) {
                $cosmetic = Loader::getCosmeticHandler();
                $skin = new Skin($player->getSkin()->getSkinId(), $player->getSkin()->getSkinData(), '', $player->getSkin()->getGeometryName() !== 'geometry.humanoid.customSlim' ? 'geometry.humanoid.custom' : $player->getSkin()->getGeometryName(), '');
                $cosmetic->saveSkin($skin->getSkinData(), $name);
            }
        }
    }

    public function onPlayerPreLogin(PlayerPreLoginEvent $event)
    {
        foreach (Server::getInstance()->getOnlinePlayers() as $p) {
            if ($p->getUniqueId() !== $event->getPlayerInfo()->getUuid() and strtolower($event->getPlayerInfo()->getUsername()) === strtolower($p->getName())) {
                $event->setKickReason(3, Loader::getInstance()->MessageData["AntiCheatName"] . "§cThis player is already online!");
            }
        }
    }

    /**
     * @throws JsonException
     */
    public function onJoin(PlayerJoinEvent $event)
    {
        $player = $event->getPlayer();
        $name = $player->getName();
        $event->setJoinMessage("§f[§a+§f] §a" . $name);
        if ($player instanceof NeptunePlayer) {
            $player->onJoin();
        }
    }

    public function onExhaust(PlayerExhaustEvent $event)
    {
        $player = $event->getPlayer();
        if ($player->getHungerManager()->getFood() < 20) {
            $player->getHungerManager()->setFood(20);
        }
    }

    public function onClickBlock(PlayerInteractEvent $event)
    {
        $block = $event->getBlock();
        if ($block->getId() === ItemIds::ANVIL) {
            $event->cancel();
        }
    }

    /**
     * @throws JsonException
     */
    public function onChangeSkin(PlayerChangeSkinEvent $event)
    {
        $case = 0;
        $player = $event->getPlayer();
        $name = $player->getName();
        $cosmetic = Loader::getCosmeticHandler();
        if ($player instanceof NeptunePlayer) {
            if (strlen($event->getNewSkin()->getSkinData()) >= 131072 || strlen($event->getNewSkin()->getSkinData()) <= 8192 || $cosmetic->getSkinTransparencyPercentage($event->getNewSkin()->getSkinData()) > 6) {
                copy($cosmetic->stevePng, $cosmetic->saveSkin . "$name.png");
                $cosmetic->resetSkin($player);
                $case = 1;
            } else {
                $skin = new Skin($event->getNewSkin()->getSkinId(), $event->getNewSkin()->getSkinData(), '', $event->getNewSkin()->getGeometryName() !== 'geometry.humanoid.customSlim' ? 'geometry.humanoid.custom' : $event->getNewSkin()->getGeometryName(), '');
                $cosmetic->saveSkin($skin->getSkinData(), $name);
            }
            if ($player->getStuff() !== "") {
                $cosmetic->setSkin($player, $player->getStuff());
            } elseif ($player->getCape() !== "") {
                $capedata = $cosmetic->createCape($player->getCape());
                if ($case === 1) {
                    $player->setSkin(new Skin($player->getSkin()->getSkinId(), $player->getSkin()->getSkinData(), $capedata, $player->getSkin()->getGeometryName() !== 'geometry.humanoid.customSlim' ? 'geometry.humanoid.custom' : $player->getSkin()->getGeometryName(), ''));
                } else {
                    $player->setSkin(new Skin($event->getNewSkin()->getSkinId(), $event->getNewSkin()->getSkinData(), $capedata, $event->getNewSkin()->getGeometryName() !== 'geometry.humanoid.customSlim' ? 'geometry.humanoid.custom' : $event->getNewSkin()->getGeometryName(), ''));
                }
            } else {
                if ($case === 1) {
                    $player->setSkin(new Skin($player->getSkin()->getSkinId(), $player->getSkin()->getSkinData(), '', $player->getSkin()->getGeometryName() !== 'geometry.humanoid.customSlim' ? 'geometry.humanoid.custom' : $player->getSkin()->getGeometryName(), ''));
                } else {
                    $player->setSkin(new Skin($event->getNewSkin()->getSkinId(), $event->getNewSkin()->getSkinData(), '', $event->getNewSkin()->getGeometryName() !== 'geometry.humanoid.customSlim' ? 'geometry.humanoid.custom' : $event->getNewSkin()->getGeometryName(), ''));
                }
            }
        }
    }

    public function onCraft(CraftItemEvent $event)
    {
        $event->cancel();
    }

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
        if ($player->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::getArenaFactory()->getBuildArena())) {
            if ($block->getId() === BlockLegacyIds::WOOL or $block->getId() === BlockLegacyIds::COBWEB) {
                $ev->setDropsVariadic(ItemFactory::getInstance()->get(ItemIds::AIR));
                if ($block->getId() === BlockLegacyIds::WOOL) {
                    $player->getInventory()->addItem(ItemFactory::getInstance()->get(ItemIds::WOOL, 0, 1));
                    Loader::getDeleteBlockHandler()->setBlockBuild($block, true);
                }
            } else {
                if ($player->getGamemode() !== GameMode::CREATIVE()) {
                    $ev->cancel();
                }
            }
        } else {
            /* @var $player NeptunePlayer */
            if (!$player->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
                if (!$player->isDueling()) {
                    $ev->cancel();
                }
            }
        }
    }

    public function onPlace(BlockPlaceEvent $ev)
    {
        $player = $ev->getPlayer();
        $block = $ev->getBlock();
        if ($player->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::getArenaFactory()->getBuildArena())) {
            Loader::getDeleteBlockHandler()->setBlockBuild($block);
            return;
        }
        /* @var $player NeptunePlayer */
        if (!$player->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
            if (!$player->isDueling()) {
                $ev->cancel();
            }
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
        if ($packet instanceof InventoryTransactionPacket or $packet instanceof LevelSoundEventPacket) {
            if ($packet::NETWORK_ID === InventoryTransactionPacket::NETWORK_ID && $packet->trData instanceof UseItemOnEntityTransactionData || $packet::NETWORK_ID === LevelSoundEventPacket::NETWORK_ID && $packet->sound === LevelSoundEvent::ATTACK_NODAMAGE) {
                Loader::getClickHandler()->addClick($player);
                if (Loader::getClickHandler()->getClicks($player) >= Loader::getInstance()->MaximumCPS) {
                    if ($player instanceof NeptunePlayer) {
                        $player->setLastDamagePlayer("Unknown");
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
        $actions = $transaction->getActions();
        $player = $transaction->getSource();
        if ($player instanceof NeptunePlayer) {
            if ($player->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::getArenaFactory()->getSkywarsArena()) or $player->isDueling()) {
                return;
            }
            if ($transaction instanceof CraftingTransaction) {
                $event->cancel();
            }
            foreach ($actions as $action) {
                if ($player->getGamemode() === GameMode::CREATIVE() or $player->EditKit !== null) {
                    return;
                } else {
                    $event->cancel();
                }
            }
        }
    }

    /**
     * @throws JsonException
     */
    public function onChat(PlayerChatEvent $event)
    {
        $player = $event->getPlayer();
        $message = $event->getMessage();
        $name = $player->getName();
        $args = explode(" ", $event->getMessage());
        $event->setFormat(Loader::getArenaUtils()->getChatFormat($player, $message));
        if ($player instanceof NeptunePlayer) {
            if ($player->EditKit !== null) {
                $event->cancel();
                if (mb_strtolower($args[0]) === "confirm") {
                    $player->saveKit();
                } else {
                    $player->sendMessage(Loader::getPrefixCore() . "§aType §l§cConfirm §r§a to confirm");
                    $player->sendMessage(Loader::getPrefixCore() . "§aพิมพ์ §l§cConfirm §r§a เพื่อยืนยัน");
                }
            } elseif (isset(Loader::getInstance()->SumoSetup[$name])) {
                $event->cancel();
                $arena = Loader::getInstance()->SumoSetup[$name];
                if (Loader::getArenaFactory()->getSumoDArena() !== null) {
                    $arena->data["level"] = Loader::getArenaFactory()->getSumoDArena();
                }
                switch ($args[0]) {
                    case "help":
                        $player->sendMessage(Loader::getPrefixCore() . "§aSumo setup\n" .
                            "§7help : Displays list of available setup commands\n" .
                            "§7setspawn : Set arena spawns\n" .
                            "§7enable : Enable the arena");
                        break;
                    case "setspawn":
                        if (!isset($args[1])) {
                            $player->sendMessage(Loader::getPrefixCore() . "§cUsage: §7setspawn <int: spawn>");
                            break;
                        } elseif (!is_numeric($args[1])) {
                            $player->sendMessage(Loader::getPrefixCore() . "§cType number!");
                            break;
                        } elseif ((int)$args[1] > $arena->data["slots"]) {
                            $player->sendMessage(Loader::getPrefixCore() . "§cThere are only {$arena->data["slots"]} slots!");
                            break;
                        }
                        $arena->data["spawns"]["spawn-$args[1]"]["x"] = $player->getPosition()->getX();
                        $arena->data["spawns"]["spawn-$args[1]"]["y"] = $player->getPosition()->getY();
                        $arena->data["spawns"]["spawn-$args[1]"]["z"] = $player->getPosition()->getZ();
                        $player->sendMessage(Loader::getPrefixCore() . "Spawn $args[1] set to X: " . round($player->getPosition()->getX()) . " Y: " . round($player->getPosition()->getY()) . " Z: " . round($player->getPosition()->getZ()));
                        break;
                    case "enable":
                        if (!$arena->setup) {
                            $player->sendMessage(Loader::getPrefixCore() . "§6Arena is already enabled!");
                            break;
                        } elseif (!$arena->enable()) {
                            $player->sendMessage(Loader::getPrefixCore() . "§cCould not load arena, there are missing information!");
                            break;
                        }
                        $player->sendMessage(Loader::getPrefixCore() . "§aArena enabled!");
                        break;
                    case "done":
                        $player->sendMessage(Loader::getPrefixCore() . "§aYou are successfully leaved setup mode!");
                        unset(Loader::getInstance()->SumoSetup[$name]);
                        if (isset(Loader::getInstance()->SumoData[$name])) {
                            unset(Loader::getInstance()->SumoData[$name]);
                        }
                        break;
                    default:
                        $player->sendMessage(Loader::getPrefixCore() . "§6You are in setup mode.\n" .
                            "§7- use §lhelp §r§7to display available commands\n" .
                            "§7- or §ldone §r§7to leave setup mode");
                        break;
                }
            } else {
                $web = new DiscordWebhook(Loader::getInstance()->getConfig()->get("api"));
                $msg = new DiscordWebhookUtils();
                $msg2 = str_replace(["@here", "@everyone"], "", $message);
                $msg->setContent(">>> " . $player->getNetworkSession()->getPing() . "ms | " . $player->PlayerOS . " " . $player->getDisplayName() . " > " . $msg2);
                $web->send($msg);
            }
        }
    }

    public function onQuit(PlayerQuitEvent $event)
    {
        $player = $event->getPlayer();
        $name = $player->getName();
        $event->setQuitMessage("§f[§c-§f] §c" . $name);
        if ($player instanceof NeptunePlayer) {
            $player->onQuit();
        }
    }

    /**
     * @throws Exception
     */
    public function onInterrupt(EntityDamageByEntityEvent $event)
    {
        $player = $event->getEntity();
        $damager = $event->getDamager();
        if ($player instanceof NeptunePlayer and $damager instanceof NeptunePlayer) {
            if ($damager->getWorld() !== Server::getInstance()->getWorldManager()->getDefaultWorld()) {
                $damager->setLastDamagePlayer($player->getName());
                $player->setLastDamagePlayer($damager->getName());
            }
            if ($player->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::getArenaFactory()->getBotArena()) or $player->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::getArenaFactory()->getParkourArena()) or $player->getWorld() === Server::getInstance()->getWorldManager()->getDefaultWorld()) {
                $event->cancel();
            } elseif ($player->Opponent === null and $damager->Opponent === null) {
                if ($damager->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::getArenaFactory()->getBuildArena()) or $damager->getWorld() === Server::getInstance()->getWorldManager()->getDefaultWorld() or $damager->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::getArenaFactory()->getSumoDArena()) or $damager->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::getArenaFactory()->getKnockbackArena()) or $damager->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::getArenaFactory()->getOITCArena()) or $damager->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::getArenaFactory()->getKitPVPArena())) return;
                $player->Opponent = $damager->getName();
                $damager->Opponent = $player->getName();
                foreach ([$player, $damager] as $p) {
                    $p->sendMessage(Loader::getInstance()->MessageData["StartCombat"]);
                    $p->Combat = true;
                    $p->CombatTime = 10;
                }
            } elseif ($player->Opponent !== null and $damager->Opponent !== null) {
                if ($player->Opponent !== $damager->getName() and $damager->Opponent !== $player->getName()) {
                    $event->cancel();
                    $damager->sendMessage(Loader::getPrefixCore() . "§cDon't Interrupt!");
                } elseif ($player->Opponent === $damager->getName() and $damager->Opponent === $player->getName()) {
                    if ($damager->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::getArenaFactory()->getBuildArena()) or $damager->getWorld() === Server::getInstance()->getWorldManager()->getDefaultWorld() or $damager->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::getArenaFactory()->getSumoDArena()) or $damager->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::getArenaFactory()->getKnockbackArena()) or $damager->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::getArenaFactory()->getOITCArena()) or $damager->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::getArenaFactory()->getKitPVPArena())) return;
                    foreach ([$player, $damager] as $p) {
                        $p->Combat = true;
                        $p->CombatTime = 10;
                    }
                    if ($damager->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::getArenaFactory()->getBoxingArena())) {
                        if ($damager->BoxingPoint <= 100) {
                            $damager->BoxingPoint += 1;
                            foreach ([$damager, $player] as $p) {
                                $boxingp = $p->BoxingPoint;
                                $opponent = $p->Opponent;
                                if ($opponent !== null) {
                                    $oppopl = Server::getInstance()->getPlayerByPrefix($opponent);
                                    /** @var NeptunePlayer $oppopl */
                                    $opponentboxingp = $oppopl->BoxingPoint;
                                } else {
                                    $opponentboxingp = 0;
                                }
                                $lines = [
                                    1 => "§7---------------§0",
                                    2 => "§dYour§f: §a$boxingp",
                                    3 => "§dOpponent§f: §c$opponentboxingp",
                                    4 => "§7---------------"
                                ];
                                Loader::getScoreboardUtils()->new($p, "ObjectiveName", Loader::getScoreboardTitle());
                                foreach ($lines as $line => $content) {
                                    Loader::getScoreboardUtils()->setLine($p, $line, $content);
                                }
                            }
                        }
                        if ($damager->BoxingPoint >= 100) {
                            $player->kill();
                        }
                    }
                }
            } elseif ($player->Opponent !== null and $damager->Opponent === null) {
                $event->cancel();
                $damager->sendMessage(Loader::getPrefixCore() . "§cDon't Interrupt!");
            } elseif ($player->Opponent === null and $damager->Opponent !== null) {
                $event->cancel();
                $damager->sendMessage(Loader::getPrefixCore() . "§cDon't Interrupt!");
            }
        }
    }

    /**
     *
     * @throws Exception
     */
    public function onDamage(EntityDamageEvent $event)
    {
        $entity = $event->getEntity();
        if ($entity instanceof NeptunePlayer) {
            switch ($event->getCause()) {
                case EntityDamageEvent::CAUSE_VOID:
                    if ($entity->getWorld() === Server::getInstance()->getWorldManager()->getDefaultWorld()) {
                        $event->cancel();
                        $entity->teleport(Server::getInstance()->getWorldManager()->getDefaultWorld()->getSafeSpawn());
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
                    $owner = $event->getChild()->getOwningEntity();
                    if ($owner instanceof Player) {
                        $name = $owner->getName();
                        if ($name === $entity->getName()) {
                            $event->cancel();
                            $entity->sendMessage(Loader::getPrefixCore() . "§cYou can't attack yourself!");
                        }
                    }
                    break;
            }
        }
    }

    public function onMove(PlayerMoveEvent $event)
    {
        $player = $event->getPlayer();
        $name = $player->getName();
        $block = $player->getWorld()->getBlock(new Vector3($player->getPosition()->getX(), $player->getPosition()->asPosition()->getY() - 0.5, $player->getPosition()->asPosition()->getZ()));
        if ($player->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::getArenaFactory()->getKnockbackArena()) or $player->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::getArenaFactory()->getBuildArena())) {
            if ($player->getPosition()->getY() <= 0) {
                $player->kill();
            }
        }
        if ($player instanceof NeptunePlayer) {
            switch ($player->getWorld()) {
                case Server::getInstance()->getWorldManager()->getWorldByName(Loader::getArenaFactory()->getBuildArena()):
                    if ($block->getId() === BlockLegacyIds::GOLD_BLOCK) {
                        $smallpp = $player->getDirectionPlane()->normalize()->multiply(2 * 3.75 / 20);
                        $player->setMotion(new Vector3($smallpp->getX(), 1.5, $smallpp->getY()));
                    }
                    break;
                case Server::getInstance()->getWorldManager()->getWorldByName(Loader::getArenaFactory()->getKitPVPArena()):
                    if ($block->getId() === BlockLegacyIds::GOLD_BLOCK) {
                        $player->getEffects()->add(new EffectInstance(VanillaEffects::LEVITATION(), 100, 3, false));
                    }
                    break;
                case Server::getInstance()->getWorldManager()->getWorldByName(Loader::getArenaFactory()->getParkourArena()):
                    switch ($block->getId()) {
                        case BlockLegacyIds::NOTE_BLOCK:
                            if ($player->TimerTask === false) {
                                $player->TimerTask = true;
                            }
                            break;
                        case BlockLegacyIds::PODZOL:
                            if ($player->TimerTask === true) {
                                $player->TimerTask = false;
                            }
                            break;
                        case BlockLegacyIds::LIT_REDSTONE_LAMP:
                            if ($player->TimerTask === true) {
                                $player->TimerTask = false;
                                $mins = floor($player->TimerData / 6000);
                                $secs = floor(($player->TimerData / 100) % 60);
                                $mili = $player->TimerData % 100;
                                Server::getInstance()->broadcastMessage(Loader::getPrefixCore() . ($name . " §aHas Finished Parkour " . $mins . " : " . $secs . " : " . $mili));
                                $player->ParkourCheckPoint = new Vector3(255, 77, 255);
                                $player->teleport(new Vector3(275, 66, 212));
                                Loader::getInstance()->getScheduler()->scheduleDelayedRepeatingTask(new ParkourFinishTask($player, $player->getWorld()), 0, 2);
                            }
                            break;
                        case BlockLegacyIds::REPEATING_COMMAND_BLOCK:
                            $vector = $player->getPosition()->asVector3();
                            if ($player->ParkourCheckPoint !== $vector) {
                                $player->ParkourCheckPoint = $vector;
                            }
                            break;
                        default:
                            return;
                    }
            }
        }
    }

    public function onEntityDeath(EntityDeathEvent $event)
    {
        $entity = $event->getEntity();
        if ($entity instanceof FistBot) {
            $cause = $entity->getLastDamageCause();
            if ($cause instanceof EntityDamageByEntityEvent) {
                $damager = $cause->getDamager();
                if ($damager instanceof NeptunePlayer) {
                    $damager->sendMessage(Loader::getPrefixCore() . "§aYou have been killed a bot!");
                    $damager->teleport(Server::getInstance()->getWorldManager()->getDefaultWorld()->getSafeSpawn());
                    Loader::getInstance()->getArenaUtils()->GiveItem($damager);
                    $damager->setHealth(20);
                }
            }
        }
    }

    /**
     * @throws Exception
     */
    public function onDeath(PlayerDeathEvent $event): void
    {
        $event->setDeathMessage("");
        $event->setDrops([]);
        $player = $event->getPlayer();
        $name = $player->getName();
        $cause = $player->getLastDamageCause();
        if ($cause instanceof EntityDamageByEntityEvent) {
            if ($player instanceof NeptunePlayer) {
                $damager = Server::getInstance()->getPlayerByPrefix($player->getLastDamagePlayer());
                if ($cause->getDamager() instanceof FistBot) {
                    foreach (Server::getInstance()->getWorldManager()->getWorldByName($cause->getDamager()->getWorld()->getFolderName())->getPlayers() as $p) {
                        $p->sendMessage(Loader::getPrefixCore() . $name . " §ahas been killed by a bot!");
                    }
                } elseif ($damager instanceof NeptunePlayer) {
                    $dname = $damager->getName() ?? "Unknown";
                    Loader::getInstance()->getArenaUtils()->DeathReset($player, $damager, $damager->getWorld()->getFolderName());
                    foreach ([$player, $damager] as $p) {
                        $p->setLastDamagePlayer("Unknown");
                        $p->setLastDamagePlayer("Unknown");
                    }
                    foreach (Loader::getInstance()->getServer()->getOnlinePlayers() as $p) {
                        if ($p->getWorld() === $damager->getWorld()) {
                            Loader::getArenaUtils()->spawnLightning($player, $damager);
                            $p->sendMessage(Loader::getPrefixCore() . "§a" . $name . " §fhas been killed by §c" . $dname);
                        }
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
        Loader::getInstance()->getArenaUtils()->GiveItem($player);
        if ($player instanceof NeptunePlayer) {
            if ($player->EditKit !== null) {
                $player->EditKit = null;
                $player->setImmobile(false);
            }
            $player->setDueling(false);
            $player->setUnPVPTag();
        }
    }

    public function onCommandPreprocess(PlayerCommandPreprocessEvent $event)
    {
        $player = $event->getPlayer();
        $msg = $event->getMessage();
        if ($player instanceof NeptunePlayer) {
            if ($player->Combat === true or $player->EditKit !== null) {
                $msg = substr($msg, 1);
                $msg = explode(" ", $msg);
                if (in_array($msg[0], Loader::getInstance()->BanCommand)) {
                    $event->cancel();
                    $player->sendMessage(Loader::getInstance()->MessageData["CantUseWantCombat"]);
                }
            }
        }
    }

    public function onTeleport(EntityTeleportEvent $event)
    {
        $entity = $event->getEntity();
        $from = $event->getFrom();
        $to = $event->getTo();
        if ($entity instanceof NeptunePlayer) {
            if ($from->getWorld() !== $to->getWorld() and $to->getWorld() === Server::getInstance()->getWorldManager()->getDefaultWorld()) {
                $entity->TimerTask = false;
                $entity->TimerData = 0;
            }
        }
    }
}