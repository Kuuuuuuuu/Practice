<?php /** @noinspection PhpIllegalStringOffsetInspection */
/** @noinspection PhpInconsistentReturnPointsInspection */
/** @noinspection PhpParamsInspection */

/** @noinspection PhpPossiblePolymorphicInvocationInspection */

declare(strict_types=1);

namespace Kohaku\Core\Events;

use Exception;
use JsonException;
use Kohaku\Core\HorizonPlayer;
use Kohaku\Core\Loader;
use Kohaku\Core\Task\ParkourFinishTask;
use Kohaku\Core\Task\ScoreboardTask;
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
use pocketmine\event\entity\EntityShootBowEvent;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\entity\ProjectileHitBlockEvent;
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
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\math\Vector3;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\world\particle\HeartParticle;

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
        if ($item->getCustomName() === "§r§bPlay") {
            Loader::$form->Form1($player);
        }
        if (!isset(Loader::getInstance()->SkillCooldown[$name])) {
            if ($item->getCustomName() === "§r§6Reaper") {
                $player->sendMessage(Loader::getInstance()->message["StartSkillMessage"]);
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
                $player->sendMessage(Loader::getInstance()->message["StartSkillMessage"]);
                $player->getEffects()->add(new EffectInstance(VanillaEffects::REGENERATION(), 120, 1, false));
                $player->getEffects()->add(new EffectInstance(VanillaEffects::RESISTANCE(), 120, 1, false));
                $player->getEffects()->add(new EffectInstance(VanillaEffects::HEALTH_BOOST(), 120, 1, false));
                Loader::getInstance()->SkillCooldown[$name] = 10;
            } else if ($item->getCustomName() === "§r§6Ultimate Boxing") {
                $player->getEffects()->add(new EffectInstance(VanillaEffects::STRENGTH(), 120, 1, false));
                $player->getEffects()->add(new EffectInstance(VanillaEffects::RESISTANCE(), 120, 1, false));
                $player->sendMessage(Loader::getInstance()->message["StartSkillMessage"]);
                Loader::getInstance()->SkillCooldown[$name] = 10;
            } else if ($item->getCustomName() === "§r§6Ultimate Bower") {
                $player->getEffects()->add(new EffectInstance(VanillaEffects::STRENGTH(), 120, 1, false));
                $player->getEffects()->add(new EffectInstance(VanillaEffects::RESISTANCE(), 120, 1, false));
                $player->getEffects()->add(new EffectInstance(VanillaEffects::SPEED(), 120, 3, false));
                $player->getEffects()->add(new EffectInstance(VanillaEffects::JUMP_BOOST(), 120, 3, false));
                $player->sendMessage(Loader::getInstance()->message["StartSkillMessage"]);
                Loader::getInstance()->SkillCooldown[$name] = 10;
            } else if ($item->getCustomName() === "§r§6Teleport") {
                $player->sendMessage(Loader::getInstance()->message["StartSkillMessage"]);
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
        if ($item->getCustomName() === "§r§bSettings") {
            Loader::$form->settingsForm($player);
        } else if ($item->getCustomName() === "§r§aStop Timer §f| §bClick to use") {
            Loader::getInstance()->TimerData[$name] = 0;
            Loader::getInstance()->TimerTask[$name] = false;
            $player->teleport(new Vector3(275, 66, 212));
            $player->sendMessage(Loader::getPrefixCore() . "§aYou Has been reset!");
        } else if ($item->getCustomName() === "§r§aBack to Checkpoint §f| §bClick to use") {
            $config = new Config(Loader::getInstance()->getDataFolder() . "pkdata/" . $name . ".yml", CONFIG::YAML);
            $player->sendMessage(Loader::getPrefixCore() . "§aTeleport to Checkpoint");
            $player->teleport(new Vector3($config->get("X"), $config->get("Y"), $config->get("Z")));
        }
    }

    public function onArrow(ProjectileHitBlockEvent $event)
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
                Loader::getInstance()->ArrowOITC[$entity->getName()] = 3;
            }
        }
    }

    public function onPlayerDropItem(PlayerDropItemEvent $event): void
    {
        $player = $event->getPlayer();
        if ($event->isCancelled()) return;
        if ($player->getWorld() !== Server::getInstance()->getWorldManager()->getWorldByName("aqua")) {
            $event->cancel();
        }
    }

    /**
     * @throws JsonException
     */
    public function onPlayerLogin(PlayerLoginEvent $event)
    {
        $player = $event->getPlayer();
        $name = $player->getName();
        $banplayer = $player->getName();
        $banInfo = Loader::getInstance()->db->query("SELECT * FROM banPlayers WHERE player = '$banplayer';");
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
                $player->kick(str_replace(["{day}", "{hour}", "{minute}", "{second}", "{reason}", "{staff}"], [$day, $hour, $minute, $second, $reason, $staff], Loader::getInstance()->message["LoginBanMessage"]));
            } else {
                Loader::getInstance()->db->query("DELETE FROM banPlayers WHERE player = '$banplayer';");
            }
        } else {
            $player->teleport(Server::getInstance()->getWorldManager()->getDefaultWorld()->getSafeSpawn());
            $player->teleport(new Vector3(255, 6, 255));
            ArenaUtils::getInstance()->DeviceCheck($player);
            Loader::$cps->initPlayerClickData($player);
            Loader::getinstance()->getScheduler()->scheduleRepeatingTask(new ScoreboardTask($player), 50);
            if ($player instanceof HorizonPlayer) {
                $cosmetic = CosmeticHandler::getInstance();
                if (strlen($player->getSkin()->getSkinData()) >= 131072 || strlen($player->getSkin()->getSkinData()) <= 8192 || $cosmetic->getSkinTransparencyPercentage($player->getSkin()->getSkinData()) > 6) {
                    copy($cosmetic->stevePng, $cosmetic->saveSkin . "$name.png");
                    $cosmetic->resetSkin($player);
                } else {
                    $skin = new Skin($player->getSkin()->getSkinId(), $player->getSkin()->getSkinData(), '', $player->getSkin()->getGeometryName() !== 'geometry.humanoid.customSlim' ? 'geometry.humanoid.custom' : $player->getSkin()->getGeometryName(), '');
                    $cosmetic->saveSkin($skin->getSkinData(), $name);
                }
                $player->getAllCape();
            }
        }
    }

    public function onPlayerLog(PlayerPreLoginEvent $event)
    {
        foreach (Server::getInstance()->getOnlinePlayers() as $p) {
            if ($p->getUniqueId() !== $event->getPlayerInfo()->getUuid() and strtolower($event->getPlayerInfo()->getUsername()) === strtolower($p->getName())) {
                $event->setKickReason(3, "§bGuardian §f» §cThis player is already online!");
            }
        }
    }

    /**
     * @throws JsonException
     */
    public function onJoin(PlayerJoinEvent $event)
    {
        $player = $event->getPlayer();
        $event->setJoinMessage("§f[§a+§f] §a" . $player->getName());
        $player->getEffects()->clear();
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        $player->sendMessage(Loader::getPrefixCore() . "§eLoading Player Data");
        ArenaUtils::getInstance()->GiveItem($player);
        if ($player instanceof HorizonPlayer) {
            $player->LoadCape();
            $player->setCosmetic();
        }
    }

    public function onExhaust(PlayerExhaustEvent $event)
    {
        $player = $event->getPlayer();
        if ($player->getHungerManager()->getFood() < 20) {
            $event->cancel();
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
            Loader::getInstance()->PlayerSkin[$player->getName()] = $player->getSkin();
        }
    }

    public function onChat(PlayerChatEvent $event)
    {
        $player = $event->getPlayer();
        $message = $event->getMessage();
        $event->setFormat("§e" . ArenaUtils::getInstance()->getPlayerOs($player) . " §f| §a" . $player->getDisplayName() . "§6 > §f" . $message);
        if (isset(Loader::getInstance()->SumoSetup[$player->getName()])) {
            $event->cancel();
            $args = explode(" ", $event->getMessage());
            $arena = Loader::getInstance()->SumoSetup[$player->getName()];
            $arena->data["level"] = $player->getWorld()->getFolderName();
            switch ($args[0]) {
                case "help":
                    $player->sendMessage(Loader::getPrefixCore() . "§aSumo setup help (1/1):\n" .
                        "§7help : Displays list of available setup commands\n" .
                        "§7level : Set arena level\n" .
                        "§7setspawn : Set arena spawns\n" .
                        "§7joinsign : Set arena joinsign\n" .
                        "§7enable : Enable the arena");
                    break;
                case "setspawn":
                    if (!isset($args[1])) {
                        $player->sendMessage(Loader::getPrefixCore() . "§cUsage: §7setspawn <int: spawn>");
                        break;
                    }
                    if (!is_numeric($args[1])) {
                        $player->sendMessage(Loader::getPrefixCore() . "§cType number!");
                        break;
                    }
                    if ((int)$args[1] > $arena->data["slots"]) {
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
                    }
                    if (!$arena->enable()) {
                        $player->sendMessage(Loader::getPrefixCore() . "§cCould not load arena, there are missing information!");
                        break;
                    }
                    $player->sendMessage(Loader::getPrefixCore() . "§aArena enabled!");
                    break;
                case "done":
                    $player->sendMessage(Loader::getPrefixCore() . "§aYou are successfully leaved setup mode!");
                    unset(Loader::getInstance()->SumoSetup[$player->getName()]);
                    if (isset(Loader::getInstance()->SumoData[$player->getName()])) {
                        unset(Loader::getInstance()->SumoData[$player->getName()]);
                    }
                    break;
                default:
                    $player->sendMessage(Loader::getPrefixCore() . "§6You are in setup mode.\n" .
                        "§7- use §lhelp §r§7to display available commands\n" .
                        "§7- or §ldone §r§7to leave setup mode");
                    break;
            }
        } else {
            if (isset(Loader::getInstance()->ChatCooldown[$player->getName()])) {
                if (Loader::getInstance()->ChatCooldown[$player->getName()] > 0) {
                    $event->cancel();
                    $player->sendMessage(str_replace(["&", "{cooldown}"], ["§", Loader::getInstance()->ChatCooldown[$player->getName()]], Loader::getInstance()->message["CooldownMessage"]));
                }
            } else {
                $web = new DiscordWebhook(Loader::getInstance()->getConfig()->get("api"));
                $msg = new DiscordWebhookUtils();
                $msg2 = str_replace(["@here", "@everyone"], "", $message);
                $msg->setContent(">>> " . $player->getNetworkSession()->getPing() . "ms | " . ArenaUtils::getInstance()->getPlayerOs($player) . " " . $player->getDisplayName() . " > " . $msg2);
                $web->send($msg);
                Loader::getInstance()->ChatCooldown[$player->getName()] = 1.5;
            }
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
        $player->teleport(new Vector3(255, 70, 255));
        if (isset(Loader::getInstance()->BoxingPoint[$name])) {
            unset(Loader::getInstance()->BoxingPoint[$name]);
        }
        if (isset(Loader::getInstance()->opponent[$name])) {
            Loader::getInstance()->BoxingPoint[Loader::getInstance()->opponent[$name]] = 0;
            unset(Loader::getInstance()->opponent[$name]);
        }
        if (isset(Loader::getInstance()->CombatTimer[$name])) {
            $player->kill();
            unset(Loader::getInstance()->CombatTimer[$name]);
        }
    }

    public function onDamage(EntityDamageEvent $event)
    {
        $entity = $event->getEntity();
        if ($event->getCause() === EntityDamageEvent::CAUSE_FALL) {
            $event->cancel();
        } else if ($event instanceof EntityDamageByChildEntityEvent) {
            $owner = $event->getChild()->getOwningEntity();
            if ($owner instanceof Player and $entity instanceof Player) {
                $name = $owner->getName();
                if ($name === $entity->getName()) {
                    $event->cancel();
                    $entity->sendMessage(Loader::getPrefixCore() . "§cYou can't attack yourself!");
                }
            }
        } else if ($event->getCause() === EntityDamageEvent::CAUSE_ENTITY_ATTACK) {
            $damager = $event->getDamager();
            if ($entity instanceof Player and $damager instanceof Player) {
                $dis = floor($entity->getLocation()->asVector3()->distance($damager->getPosition()->asVector3()));
                $name = $damager->getName();
                if ($damager->getGamemode() !== Gamemode::CREATIVE() and $damager->getWorld() !== Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getKnockBackArena())) {
                    if ($damager->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getBuildArena())) return;
                    if ($dis >= 5.5) {
                        $event->cancel();
                        $message = (Loader::getInstance()->message["AntiCheatName"] . "§c" . $name . " §eHas " . $dis . " §cDistance" . "§f(§a" . $damager->getNetworkSession()->getPing() . " §ePing §f/ §6" . ArenaUtils::getInstance()->getPlayerControls($damager) . "§f)");
                        Server::getInstance()->broadcastMessage($message);
                        $damager->kill();
                    }
                }
                if ($entity->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getParkourArena()) or $entity->getWorld() === Server::getInstance()->getWorldManager()->getDefaultWorld()) {
                    $event->cancel();
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
        $block = $player->getWorld()->getBlock(new Vector3($player->getPosition()->getX(), $player->getPosition()->asPosition()->getY() - 0.5, $player->getPosition()->asPosition()->getZ()));
        if ($player->getPosition()->getY() <= 0) {
            if ($player->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getKnockbackArena()) or $player->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getBuildArena())) {
                $player->kill();
            }
        } else if ($player->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getKitPVPArena())) {
            if ($block->getId() === BlockLegacyIds::GOLD_BLOCK) {
                $player->getEffects()->add(new EffectInstance(VanillaEffects::LEVITATION(), 100, 3, false));
            }
        } else if ($player->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getParkourArena())) {
            if ($block->getId() === 25) {
                if (Loader::getInstance()->TimerTask[$name] === false) {
                    Loader::getInstance()->TimerTask[$name] = true;
                }
            } else if ($block->getId() === 243) {
                if (Loader::getInstance()->TimerTask[$name] === true) {
                    Loader::getInstance()->TimerTask[$name] = false;
                }
            } else if ($block->getId() === 124) {
                if (Loader::getInstance()->TimerTask[$name] === true) {
                    $mins = floor(Loader::getInstance()->TimerData[$name] / 6000);
                    $secs = floor((Loader::getInstance()->TimerData[$name] / 100) % 60);
                    $mili = Loader::getInstance()->TimerData[$name] % 100;
                    $prefix = Loader::getPrefixCore();
                    $message = ($name . " §aHas Finished Parkour " . $mins . " : " . $secs . " : " . $mili);
                    Server::getInstance()->broadcastMessage($prefix . $message);
                    Loader::getInstance()->TimerTask[$name] = false;
                    $config = new Config(Loader::getInstance()->getDataFolder() . "pkdata/" . $name . ".yml", CONFIG::YAML);
                    $player->teleport(new Vector3(275, 66, 212));
                    Loader::getInstance()->getScheduler()->scheduleDelayedRepeatingTask(
                        new ParkourFinishTask($player, $player->getWorld()), 0, 2
                    );
                    $config->set("X", 255);
                    $config->set("Y", 76);
                    $config->set("Z", 255);
                }
            } else if ($block->getId() === 188) {
                $config = new Config(Loader::getInstance()->getDataFolder() . "pkdata/" . $name . ".yml", CONFIG::YAML);
                $config->set("X", $player->getPosition()->getX());
                $config->set("Y", $player->getPosition()->getY());
                $config->set("Z", $player->getPosition()->getZ());
                try {
                    $config->save();
                } catch (JsonException $e) {
                    $player->sendMessage(Loader::getPrefixCore() . "§cError while saving the file");
                    ArenaUtils::getLogger((string)$e);
                    return null;
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
        $pos = $player->getPosition();
        $world = $player->getWorld();
        $player->getEffects()->clear();
        $player->kill();
        $world->addParticle($pos, new HeartParticle(3));
        $cause = $player->getLastDamageCause();
        if ($cause instanceof EntityDamageByEntityEvent) {
            $damager = $cause->getDamager();
            if ($damager instanceof Player) {
                if ($damager->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getOITCArena())) {
                    ArenaUtils::getInstance()->DeathReset($player, $damager, "OITC");
                } else if ($damager->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getBoxingArena())) {
                    ArenaUtils::getInstance()->DeathReset($player, $damager, "Boxing");
                } else if ($damager->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getBuildArena())) {
                    ArenaUtils::getInstance()->DeathReset($player, $damager, "Build");
                } else {
                    ArenaUtils::getInstance()->DeathReset($player, $damager);
                }
                foreach (Loader::getInstance()->getServer()->getOnlinePlayers() as $p) {
                    if ($p->getWorld() === $damager->getWorld()) {
                        $p->sendMessage(Loader::getPrefixCore() . "§a" . $player->getName() . " §fhas been killed by §c" . $player->getLastDamageCause()->getDamager()->getName());
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
        $player->getArmorInventory()->clearAll();
        $player->getInventory()->clearAll();
        ArenaUtils::getInstance()->GiveItem($player);
        ScoreboardUtils::getInstance()->sb($player);
    }

    public function onTeleport(EntityTeleportEvent $event)
    {
        $player = $event->getEntity();
        if (!$player instanceof Player) return;
        if ($player->getWorld() !== Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getParkourArena())) {
            if (isset(Loader::getInstance()->TimerTask[$player->getName()])) {
                unset(Loader::getInstance()->TimerTask[$player->getName()]);
            }
            if (isset(Loader::getInstance()->TimerData[$player->getName()])) {
                unset(Loader::getInstance()->TimerData[$player->getName()]);
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
            if (!in_array($msg[0], Loader::getInstance()->BanCommand)) return;
            $event->cancel();
            $player->sendMessage(Loader::getInstance()->message["CantUseWantCombat"]);
        }
    }
}
