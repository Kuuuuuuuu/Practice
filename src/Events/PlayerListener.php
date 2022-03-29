<?php /** @noinspection PhpIllegalStringOffsetInspection */
/** @noinspection PhpInconsistentReturnPointsInspection */
/** @noinspection PhpParamsInspection */

/** @noinspection PhpPossiblePolymorphicInvocationInspection */

declare(strict_types=1);

namespace Kohaku\Core\Events;

use Exception;
use JsonException;
use Kohaku\Core\Entity\FistBot;
use Kohaku\Core\HorizonPlayer;
use Kohaku\Core\Loader;
use Kohaku\Core\Task\ParkourFinishTask;
use Kohaku\Core\Utils\ArenaUtils;
use Kohaku\Core\Utils\CosmeticHandler;
use Kohaku\Core\Utils\DiscordUtils\DiscordWebhook;
use Kohaku\Core\Utils\DiscordUtils\DiscordWebhookUtils;
use Kohaku\Core\Utils\ScoreboardUtils;
use pocketmine\block\BlockLegacyIds;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\entity\projectile\Arrow;
use pocketmine\entity\Skin;
use pocketmine\event\entity\EntityDamageByChildEntityEvent;
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
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerJumpEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\inventory\transaction\CraftingTransaction;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\math\Vector3;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;

class PlayerListener implements Listener
{

    public function onCreation(PlayerCreationEvent $event)
    {
        $event->setPlayerClass(HorizonPlayer::class);
    }

    public function onUse(PlayerItemUseEvent $event)
    {
        $player = $event->getPlayer();
        $item = $event->getItem();
        $name = $player->getName();
        if (!isset(Loader::getInstance()->SkillCooldown[$name])) {
            if ($item->getCustomName() === "§r§6Reaper") {
                $player->sendMessage(Loader::getInstance()->MessageData["StartSkillMessage"]);
                foreach (Server::getInstance()->getOnlinePlayers() as $p) {
                    if ($p->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getKitPVPArena())) {
                        if ($p->getName() !== $name) {
                            if ($player->getPosition()->distance($p->getPosition()) <= 10) {
                                $player->getEffects()->add(new EffectInstance(VanillaEffects::INVISIBILITY(), 120, 1, false));
                                $p->getEffects()->add(new EffectInstance(VanillaEffects::WEAKNESS(), 120, 1, false));
                                $p->getEffects()->add(new EffectInstance(VanillaEffects::BLINDNESS(), 120, 1, false));
                                $player->getArmorInventory()->setHelmet(ItemFactory::getInstance()->get(ItemIds::SKULL, 1, 1)->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 4)));
                                Loader::getInstance()->SkillCooldown[$name] = 10;
                            }
                        }
                    }
                }
            } else if ($item->getCustomName() === "§r§6Ultimate Tank") {
                $player->sendMessage(Loader::getInstance()->MessageData["StartSkillMessage"]);
                $player->getEffects()->add(new EffectInstance(VanillaEffects::REGENERATION(), 120, 1, false));
                $player->getEffects()->add(new EffectInstance(VanillaEffects::RESISTANCE(), 120, 1, false));
                $player->getEffects()->add(new EffectInstance(VanillaEffects::HEALTH_BOOST(), 120, 1, false));
                Loader::getInstance()->SkillCooldown[$name] = 10;
            } else if ($item->getCustomName() === "§r§6Ultimate Boxing") {
                $player->getEffects()->add(new EffectInstance(VanillaEffects::STRENGTH(), 120, 1, false));
                $player->getEffects()->add(new EffectInstance(VanillaEffects::RESISTANCE(), 120, 1, false));
                $player->sendMessage(Loader::getInstance()->MessageData["StartSkillMessage"]);
                Loader::getInstance()->SkillCooldown[$name] = 10;
            } else if ($item->getCustomName() === "§r§6Ultimate Bower") {
                $player->getEffects()->add(new EffectInstance(VanillaEffects::STRENGTH(), 120, 1, false));
                $player->getEffects()->add(new EffectInstance(VanillaEffects::RESISTANCE(), 120, 1, false));
                $player->getEffects()->add(new EffectInstance(VanillaEffects::SPEED(), 120, 3, false));
                $player->getEffects()->add(new EffectInstance(VanillaEffects::JUMP_BOOST(), 120, 3, false));
                $player->sendMessage(Loader::getInstance()->MessageData["StartSkillMessage"]);
                Loader::getInstance()->SkillCooldown[$name] = 10;
            } else if ($item->getCustomName() === "§r§6Teleport") {
                $player->sendMessage(Loader::getInstance()->MessageData["StartSkillMessage"]);
                foreach (Server::getInstance()->getOnlinePlayers() as $p) {
                    if ($p->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getKitPVPArena())) {
                        if ($p->getName() !== $name) {
                            $player->teleport($p->getPosition()->asVector3());
                            Loader::getInstance()->SkillCooldown[$name] = 10;
                        }
                    }
                }
            } else if ($item->getCustomName() === "§r§eLeap§r") {
                $directionvector = $player->getDirectionVector()->multiply(4 / 2);
                $dx = $directionvector->getX();
                $dy = $directionvector->getY();
                $dz = $directionvector->getZ();
                $player->setMotion(new Vector3($dx, $dy + 0.5, $dz));
                Loader::getInstance()->SkillCooldown[$name] = 10;
            }
        }
        if ($item->getCustomName() === "§r§bPlay") {
            Loader::$form->Form1($player);
        } else if ($item->getCustomName() === "§r§bSettings") {
            Loader::$form->settingsForm($player);
        } else if ($item->getCustomName() === "§r§bBot") {
            Loader::$form->botForm($player);
        } else if ($item->getCustomName() === "§r§aStop Timer §f| §bClick to use") {
            Loader::getInstance()->TimerData[$name] = 0;
            Loader::getInstance()->TimerTask[$name] = false;
            $player->teleport(new Vector3(275, 66, 212));
            $player->sendMessage(Loader::getPrefixCore() . "§aYou Has been reset!");
            Loader::getInstance()->ParkourCheckPoint[$name] = new Vector3(275, 77, 212);
        } else if ($item->getCustomName() === "§r§aBack to Checkpoint §f| §bClick to use") {
            if (isset(Loader::getInstance()->ParkourCheckPoint[$name])) {
                $player->teleport(Loader::getInstance()->ParkourCheckPoint[$name]);
            } else {
                $player->teleport(new Vector3(275, 77, 212));
            }
            $player->sendMessage(Loader::getPrefixCore() . "§aTeleport to Checkpoint");
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
        if ($entity instanceof Player) {
            if ($entity->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getOITCArena())) {
                Loader::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($entity): void {
                    if ($entity->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getOITCArena())) {
                        $entity->getInventory()->setItem(19, ItemFactory::getInstance()->get(ItemIds::ARROW, 0, 1));
                    }
                }), 100);
            }
        }
    }

    public function onPlayerDropItem(PlayerDropItemEvent $event): void
    {
        $event->cancel();
    }

    /**
     * @throws JsonException
     */
    public function onPlayerLogin(PlayerLoginEvent $event)
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
            ArenaUtils::getInstance()->DeviceCheck($player);
            Loader::$cps->initPlayerClickData($player);
            if ($player instanceof HorizonPlayer) {
                $cosmetic = CosmeticHandler::getInstance();
                $skin = new Skin($player->getSkin()->getSkinId(), $player->getSkin()->getSkinData(), '', $player->getSkin()->getGeometryName() !== 'geometry.humanoid.customSlim' ? 'geometry.humanoid.custom' : $player->getSkin()->getGeometryName(), '');
                $cosmetic->saveSkin($skin->getSkinData(), $name);
            }
        }
    }

    public function onPlayerLog(PlayerPreLoginEvent $event)
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
        $player->getEffects()->clear();
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        ArenaUtils::getInstance()->GiveItem($player);
        if ($player instanceof HorizonPlayer) {
            $player->LoadData();
            $player->sendMessage(Loader::getPrefixCore() . "§eLoading Player Data");
        }
        if (isset(Loader::getInstance()->EditKit[$name])) {
            unset(Loader::getInstance()->EditKit[$name]);
        }
        if (ArenaUtils::getInstance()->getData($name)->getTag() !== null and ArenaUtils::getInstance()->getData($name)->getTag() !== "") {
            $nametag = ArenaUtils::getInstance()->getData($name)->getRank() . "§a " . $player->getDisplayName() . " §f[" . ArenaUtils::getInstance()->getData($name)->getTag() . "§f]";
        } else {
            $nametag = ArenaUtils::getInstance()->getData($name)->getRank() . "§a " . $player->getDisplayName();
        }
        $player->setNameTag($nametag);
    }

    public function onExhaust(PlayerExhaustEvent $event)
    {
        $player = $event->getPlayer();
        if ($player->getHungerManager()->getFood() < 20) {
            $player->getHungerManager()->setFood(20);
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
        $cosmetic = CosmeticHandler::getInstance();
        if ($player instanceof HorizonPlayer) {
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
            } else if ($player->getCape() !== "") {
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

    public function onItemMoved(InventoryTransactionEvent $event): void
    {
        $transaction = $event->getTransaction();
        $actions = $transaction->getActions();
        $player = $transaction->getSource();
        if ($player->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getSkywarsArena())) {
            return;
        }
        if ($transaction instanceof CraftingTransaction) {
            $event->cancel();
        }
        foreach ($actions as $action) {
            if ($player->getGamemode() === GameMode::CREATIVE() or (isset(Loader::getInstance()->EditKit[$player->getName()]) and Loader::getInstance()->EditKit[$player->getName()] === true)) {
                return;
            } else {
                $event->cancel();
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
        if (ArenaUtils::getInstance()->getData($name)->getTag() !== null and ArenaUtils::getInstance()->getData($name)->getTag() !== "") {
            $nametag = ArenaUtils::getInstance()->getData($name)->getRank() . "§a " . $player->getDisplayName() . " §f[" . ArenaUtils::getInstance()->getData($name)->getTag() . "§f]" . "§r§a > §r" . $message;
        } else {
            $nametag = ArenaUtils::getInstance()->getData($name)->getRank() . "§a " . $player->getDisplayName() . "§r§a > §r" . $message;
        }
        $event->setFormat($nametag);
        if (isset(Loader::getInstance()->EditKit[$name])) {
            $event->cancel();
            $args = explode(" ", $event->getMessage());
            if (mb_strtolower($args[0]) === "confirm") {
                try {
                    Loader::getInstance()->KitData->set($name, [
                        "0" => [
                            "item" => $player->getInventory()->getItem(0)->getId(),
                            "count" => $player->getInventory()->getItem(0)->getCount(),
                        ],
                        "1" => [
                            "item" => $player->getInventory()->getItem(1)->getId(),
                            "count" => $player->getInventory()->getItem(1)->getCount()
                        ],
                        "2" => [
                            "item" => $player->getInventory()->getItem(2)->getId(),
                            "count" => $player->getInventory()->getItem(2)->getCount(),
                        ],
                        "3" => [
                            "item" => $player->getInventory()->getItem(3)->getId(),
                            "count" => $player->getInventory()->getItem(3)->getCount()
                        ],
                        "4" => [
                            "item" => $player->getInventory()->getItem(4)->getId(),
                            "count" => $player->getInventory()->getItem(4)->getCount()
                        ],
                        "5" => [
                            "item" => $player->getInventory()->getItem(5)->getId(),
                            "count" => $player->getInventory()->getItem(5)->getCount()
                        ],
                        "6" => [
                            "item" => $player->getInventory()->getItem(6)->getId(),
                            "count" => $player->getInventory()->getItem(6)->getCount()
                        ],
                        "7" => [
                            "item" => $player->getInventory()->getItem(7)->getId(),
                            "count" => $player->getInventory()->getItem(7)->getCount()
                        ],
                        "8" => [
                            "item" => $player->getInventory()->getItem(8)->getId(),
                            "count" => $player->getInventory()->getItem(8)->getCount()
                        ],
                    ]);
                } catch (Exception) {
                    $player->kill();
                    $player->setImmobile(false);
                    $player->sendMessage(Loader::getPrefixCore() . "§cAn error occurred while saving your kit.");
                    unset(Loader::getInstance()->EditKit[$name]);
                    return;
                }
                Loader::getInstance()->KitData->save();
                unset(Loader::getInstance()->EditKit[$name]);
                $player->sendMessage(Loader::getPrefixCore() . "§aYou have successfully saved your kit!");
                $player->kill();
                $player->setImmobile(false);
            } else {
                $player->sendMessage(Loader::getPrefixCore() . "§aType §l§cConfirm §r§a to confirm");
                $player->sendMessage(Loader::getPrefixCore() . "§aพิมพ์ §l§cConfirm §r§a เพื่อยืนยัน");
            }
        } else if (isset(Loader::getInstance()->SumoSetup[$name])) {
            $event->cancel();
            $args = explode(" ", $event->getMessage());
            $arena = Loader::getInstance()->SumoSetup[$name];
            if (Loader::$arenafac->getSumoDArena() !== null) {
                $arena->data["level"] = Loader::$arenafac->getSumoDArena();
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
                    } else if (!is_numeric($args[1])) {
                        $player->sendMessage(Loader::getPrefixCore() . "§cType number!");
                        break;
                    } else if ((int)$args[1] > $arena->data["slots"]) {
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
                    } else if (!$arena->enable()) {
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
            $msg->setContent(">>> " . $player->getNetworkSession()->getPing() . "ms | " . ArenaUtils::getInstance()->getPlayerOs($player) . " " . $player->getDisplayName() . " > " . $msg2);
            $web->send($msg);
        }
    }

    public function onLeft(PlayerQuitEvent $event)
    {
        $player = $event->getPlayer();
        $name = $player->getName();
        $player->getEffects()->clear();
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        $event->setQuitMessage("§f[§c-§f] §c" . $player->getName());
        Loader::$cps->removePlayerClickData($player);
        $player->teleport(Server::getInstance()->getWorldManager()->getDefaultWorld()->getSafeSpawn());
        if (isset(Loader::getInstance()->BoxingPoint[$name])) {
            unset(Loader::getInstance()->BoxingPoint[$name]);
        }
        if (isset(Loader::getInstance()->PlayerOpponent[$name])) {
            Loader::getInstance()->BoxingPoint[Loader::getInstance()->PlayerOpponent[$name]] = 0;
            unset(Loader::getInstance()->PlayerOpponent[$name]);
        }
        if (isset(Loader::getInstance()->CombatTimer[$name])) {
            $player->kill();
            unset(Loader::getInstance()->CombatTimer[$name]);
        }
        if (isset(Loader::getInstance()->EditKit[$name])) {
            unset(Loader::getInstance()->EditKit[$name]);
        }
    }

    /**
     * @throws Exception
     */
    public function onInterrupt(EntityDamageByEntityEvent $event)
    {
        $player = $event->getEntity();
        $damager = $event->getDamager();
        if ($player instanceof HorizonPlayer and $damager instanceof HorizonPlayer) {
            $damager->setLastDamagePlayer($player->getName());
            $player->setLastDamagePlayer($damager->getName());
            if ($player->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getBotArena()) or $player->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getParkourArena()) or $player->getWorld() === Server::getInstance()->getWorldManager()->getDefaultWorld()) {
                $event->cancel();
            } else if (!isset(Loader::getInstance()->PlayerOpponent[$player->getName()]) and !isset(Loader::getInstance()->PlayerOpponent[$damager->getName()])) {
                Loader::getInstance()->PlayerOpponent[$player->getName()] = $damager->getName();
                Loader::getInstance()->PlayerOpponent[$damager->getName()] = $player->getName();
                Loader::getInstance()->CombatTimer[$player->getName()] = 10;
                Loader::getInstance()->CombatTimer[$damager->getName()] = 10;
                $player->setPVPTag();
                $damager->setPVPTag();
                $player->sendMessage(Loader::getInstance()->MessageData["StartCombat"]);
                $damager->sendMessage(Loader::getInstance()->MessageData["StartCombat"]);
            } else if (isset(Loader::getInstance()->PlayerOpponent[$damager->getName()]) and isset(Loader::getInstance()->PlayerOpponent[$player->getName()])) {
                if (Loader::getInstance()->PlayerOpponent[$player->getName()] !== $damager->getName() and Loader::getInstance()->PlayerOpponent[$damager->getName()] !== $player->getName()) {
                    if ($damager->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getSkywarsArena()) or $damager->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getBuildArena()) or $damager->getWorld() === Server::getInstance()->getWorldManager()->getDefaultWorld() or $damager->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getSumoDArena()) or $damager->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getKnockbackArena()) or $damager->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getOITCArena()) or $damager->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getKitPVPArena())) return;
                    $event->cancel();
                    $damager->sendMessage(Loader::getPrefixCore() . "§cDon't Interrupt!");
                } else if (Loader::getInstance()->PlayerOpponent[$player->getName()] === $damager->getName() and Loader::getInstance()->PlayerOpponent[$damager->getName()] === $player->getName()) {
                    Loader::getInstance()->PlayerOpponent[$player->getName()] = $damager->getName();
                    Loader::getInstance()->PlayerOpponent[$damager->getName()] = $player->getName();
                    Loader::getInstance()->CombatTimer[$player->getName()] = 10;
                    Loader::getInstance()->CombatTimer[$damager->getName()] = 10;
                    if ($damager->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getBoxingArena())) {
                        if (isset(Loader::getInstance()->BoxingPoint[$damager->getName()])) {
                            if (Loader::getInstance()->BoxingPoint[$damager->getName()] <= 100) {
                                Loader::getInstance()->BoxingPoint[$damager->getName()] += 1;
                            }
                            if (Loader::getInstance()->BoxingPoint[$damager->getName()] >= 100) {
                                $player->kill();
                            }
                        } else {
                            Loader::getInstance()->BoxingPoint[$damager->getName()] = 1;
                        }
                    }
                }
            } else if (isset(Loader::getInstance()->PlayerOpponent[$player->getName()]) and !isset(Loader::getInstance()->PlayerOpponent[$damager->getName()])) {
                if ($damager->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getSkywarsArena()) or $damager->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getBuildArena()) or $damager->getWorld() === Server::getInstance()->getWorldManager()->getDefaultWorld() or $damager->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getSumoDArena()) or $damager->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getKnockbackArena()) or $damager->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getOITCArena()) or $damager->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getKitPVPArena())) return;
                $event->cancel();
                $damager->sendMessage(Loader::getPrefixCore() . "§cDon't Interrupt!");
            } else if (!isset(Loader::getInstance()->PlayerOpponent[$player->getName()]) and isset(Loader::getInstance()->PlayerOpponent[$damager->getName()])) {
                if ($damager->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getSkywarsArena()) or $damager->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getBuildArena()) or $damager->getWorld() === Server::getInstance()->getWorldManager()->getDefaultWorld() or $damager->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getSumoDArena()) or $damager->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getKnockbackArena()) or $damager->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getOITCArena()) or $damager->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getKitPVPArena())) return;
                $event->cancel();
                $damager->sendMessage(Loader::getPrefixCore() . "§cDon't Interrupt!");
            }
        }
    }

    /**
     * @throws Exception
     */
    public function onDamage(EntityDamageEvent $event)
    {
        $entity = $event->getEntity();
        /* @var HorizonPlayer $entity */
        if ($entity instanceof Player) {
            if ($event->getCause() === EntityDamageEvent::CAUSE_VOID) {
                if ($entity->getWorld() === Server::getInstance()->getWorldManager()->getDefaultWorld()) {
                    $event->cancel();
                    $entity->teleport(Server::getInstance()->getWorldManager()->getDefaultWorld()->getSafeSpawn());
                }
            } else if ($event->getCause() === EntityDamageEvent::CAUSE_FALL) {
                $event->cancel();
            } else if ($event->getCause() === EntityDamageEvent::CAUSE_SUFFOCATION) {
                $event->cancel();
                $entity->teleport(new Vector3($entity->getPosition()->getX(), $entity->getPosition()->getY() + 3, $entity->getPosition()->getZ()));
            } else if ($event instanceof EntityDamageByChildEntityEvent) {
                $owner = $event->getChild()->getOwningEntity();
                if ($owner instanceof Player) {
                    $name = $owner->getName();
                    if ($name === $entity->getName()) {
                        $event->cancel();
                        $entity->sendMessage(Loader::getPrefixCore() . "§cYou can't attack yourself!");
                    }
                }
            }
        }
    }

    public function onJump(PlayerJumpEvent $event)
    {
        $player = $event->getPlayer();
        if ($player->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getParkourArena())) {
            if (isset(Loader::getInstance()->JumpCount[$player->getName()])) {
                Loader::getInstance()->JumpCount[$player->getName()]++;
            } else {
                Loader::getInstance()->JumpCount[$player->getName()] = 1;
            }
        }
    }

    public function onMove(PlayerMoveEvent $event)
    {
        $player = $event->getPlayer();
        $name = $player->getName();
        if ($player->getPosition()->getY() <= 0) {
            if ($player->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getKnockbackArena()) or $player->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getBuildArena())) {
                $player->kill();
            }
        } else if ($player->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getBuildArena())) {
            $block = $player->getWorld()->getBlock(new Vector3($player->getPosition()->getX(), $player->getPosition()->asPosition()->getY() - 0.5, $player->getPosition()->asPosition()->getZ()));
            if ($block->getId() === BlockLegacyIds::GOLD_BLOCK) {
                $smallpp = $player->getDirectionPlane()->normalize()->multiply(2 * 3.75 / 20);
                $player->setMotion(new Vector3($smallpp->x, 1.5, $smallpp->y));
            }
        } else if ($player->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getKitPVPArena())) {
            $block = $player->getWorld()->getBlock(new Vector3($player->getPosition()->getX(), $player->getPosition()->asPosition()->getY() - 0.5, $player->getPosition()->asPosition()->getZ()));
            if ($block->getId() === BlockLegacyIds::GOLD_BLOCK) {
                $player->getEffects()->add(new EffectInstance(VanillaEffects::LEVITATION(), 100, 3, false));
            }
        } else if ($player->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getParkourArena())) {
            $block = $player->getWorld()->getBlock(new Vector3($player->getPosition()->getX(), $player->getPosition()->asPosition()->getY() - 0.5, $player->getPosition()->asPosition()->getZ()));
            if ($block->getId() === BlockLegacyIds::NOTE_BLOCK) {
                if (isset(Loader::getInstance()->TimerTask[$name]) and Loader::getInstance()->TimerTask[$name] === false) {
                    Loader::getInstance()->TimerTask[$name] = true;
                }
            } else if ($block->getId() === BlockLegacyIds::PODZOL) {
                if (isset(Loader::getInstance()->TimerTask[$name]) and Loader::getInstance()->TimerTask[$name] === true) {
                    Loader::getInstance()->TimerTask[$name] = false;
                }
            } else if ($block->getId() === BlockLegacyIds::LIT_REDSTONE_LAMP) {
                if (isset(Loader::getInstance()->TimerTask[$name]) and Loader::getInstance()->TimerTask[$name] === true) {
                    Loader::getInstance()->TimerTask[$name] = false;
                    $mins = floor(Loader::getInstance()->TimerData[$name] / 6000);
                    $secs = floor((Loader::getInstance()->TimerData[$name] / 100) % 60);
                    $mili = Loader::getInstance()->TimerData[$name] % 100;
                    Server::getInstance()->broadcastMessage(Loader::getPrefixCore() . ($name . " §aHas Finished Parkour " . $mins . " : " . $secs . " : " . $mili));
                    Loader::getInstance()->ParkourCheckPoint[$name] = new Vector3(255, 77, 255);
                    $player->teleport(new Vector3(275, 66, 212));
                    Loader::getInstance()->getScheduler()->scheduleDelayedRepeatingTask(new ParkourFinishTask($player, $player->getWorld()), 0, 2);
                }
            } else if ($block->getId() === BlockLegacyIds::REPEATING_COMMAND_BLOCK) {
                $vector = $player->getPosition()->asVector3();
                if (isset(Loader::getInstance()->ParkourCheckPoint[$name]) and Loader::getInstance()->ParkourCheckPoint[$name] !== $vector) {
                    Loader::getInstance()->ParkourCheckPoint[$name] = $vector;
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
                if ($damager instanceof Player) {
                    $damager->sendMessage(Loader::getPrefixCore() . "§aYou have been killed a bot!");
                    $damager->teleport(Server::getInstance()->getWorldManager()->getDefaultWorld()->getSafeSpawn());
                    ArenaUtils::getInstance()->GiveItem($damager);
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
        $player->kill();
        $name = $player->getName();
        $cause = $player->getLastDamageCause();
        if ($cause instanceof EntityDamageByEntityEvent) {
            /* @var HorizonPlayer $player */
            $damager = Server::getInstance()->getPlayerByPrefix($player->getLastDamagePlayer());
            if ($cause->getDamager() instanceof FistBot) {
                foreach (Loader::getInstance()->getServer()->getOnlinePlayers() as $p) {
                    if ($p->getWorld() === $player->getWorld()) {
                        $p->sendMessage(Loader::getPrefixCore() . $name . " §ahas been killed by a bot!");
                    }
                }
            } else if ($damager instanceof Player) {
                $dname = $damager->getName() ?? "Unknown";
                /* @var HorizonPlayer $damager */
                ArenaUtils::getInstance()->DeathReset($player, $damager, $damager->getWorld()->getFolderName());
                $player->setLastDamagePlayer("Unknown");
                $damager->setLastDamagePlayer("Unknown");
                foreach (Loader::getInstance()->getServer()->getOnlinePlayers() as $p) {
                    if ($p->getWorld() === $damager->getWorld()) {
                        $p->sendMessage(Loader::getPrefixCore() . "§a" . $name . " §fhas been killed by §c" . $dname);
                    }
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
        ArenaUtils::getInstance()->GiveItem($player);
        ScoreboardUtils::getInstance()->sb($player);
        $player->setPVPTag();
    }

    public function onTeleport(EntityTeleportEvent $event)
    {
        $player = $event->getEntity();
        $from = $event->getFrom();
        $to = $event->getTo();
        if ($player instanceof Player) {
            if ($from->getWorld() !== $to->getWorld()) {
                if (isset(Loader::getInstance()->TimerTask[$player->getName()])) {
                    unset(Loader::getInstance()->TimerTask[$player->getName()]);
                }
                if (isset(Loader::getInstance()->TimerData[$player->getName()])) {
                    unset(Loader::getInstance()->TimerData[$player->getName()]);
                }
            }
        }
    }

    public function onCommandPreprocess(PlayerCommandPreprocessEvent $event)
    {
        $player = $event->getPlayer();
        $msg = $event->getMessage();
        $name = $player->getName();
        if (isset(Loader::getInstance()->CombatTimer[$name])) {
            $msg = substr($msg, 1);
            $msg = explode(" ", $msg);
            if (in_array($msg[0], Loader::getInstance()->BanCommand)) {
                $event->cancel();
                $player->sendMessage(Loader::getInstance()->MessageData["CantUseWantCombat"]);
            }
        }
    }
}