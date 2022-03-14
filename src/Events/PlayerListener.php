<?php /** @noinspection PhpIllegalStringOffsetInspection */
/** @noinspection PhpInconsistentReturnPointsInspection */
/** @noinspection PhpParamsInspection */

/** @noinspection PhpPossiblePolymorphicInvocationInspection */

declare(strict_types=1);

namespace Kohaku\Core\Events;

use JsonException;
use Kohaku\Core\HorizonPlayer;
use Kohaku\Core\Loader;
use Kohaku\Core\Task\ParkourFinishTask;
use Kohaku\Core\Task\ScoreboardTask;
use Kohaku\Core\Utils\ArenaUtils;
use Kohaku\Core\Utils\DiscordUtils\DiscordWebhook;
use Kohaku\Core\Utils\DiscordUtils\DiscordWebhookUtils;
use pocketmine\block\BlockLegacyIds;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\event\entity\EntityDamageByChildEntityEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChangeSkinEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerCreationEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerDropItemEvent;
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
use pocketmine\world\particle\LavaParticle;
use pocketmine\world\Position;

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
            }
            if ($item->getCustomName() === "§r§6Ultimate Tank") {
                $player->sendMessage(Loader::getInstance()->message["StartSkillMessage"]);
                $player->getEffects()->add(new EffectInstance(VanillaEffects::REGENERATION(), 60, 1, false));
                $player->getEffects()->add(new EffectInstance(VanillaEffects::RESISTANCE(), 60, 1, false));
                $player->getEffects()->add(new EffectInstance(VanillaEffects::HEALTH_BOOST(), 60, 1, false));
                Loader::getInstance()->SkillCooldown[$name] = 10;
            }
            if ($item->getCustomName() === "§r§6Ultimate Boxing") {
                $player->getEffects()->add(new EffectInstance(VanillaEffects::STRENGTH(), 60, 1, false));
                $player->getEffects()->add(new EffectInstance(VanillaEffects::RESISTANCE(), 60, 1, false));
                $player->sendMessage(Loader::getInstance()->message["StartSkillMessage"]);
                Loader::getInstance()->SkillCooldown[$name] = 10;
            }
            if ($item->getCustomName() === "§r§6Ultimate Bower") {
                $player->getEffects()->add(new EffectInstance(VanillaEffects::STRENGTH(), 60, 1, false));
                $player->getEffects()->add(new EffectInstance(VanillaEffects::RESISTANCE(), 60, 1, false));
                $player->getEffects()->add(new EffectInstance(VanillaEffects::SPEED(), 60, 3, false));
                $player->getEffects()->add(new EffectInstance(VanillaEffects::JUMP_BOOST(), 60, 3, false));
                $player->sendMessage(Loader::getInstance()->message["StartSkillMessage"]);
                Loader::getInstance()->SkillCooldown[$name] = 10;
            }
            if ($item->getCustomName() === "§r§6Teleport") {
                $player->sendMessage(Loader::getInstance()->message["StartSkillMessage"]);
                foreach (Server::getInstance()->getOnlinePlayers() as $p) {
                    if ($p->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getKitPVPArena())) {
                        if ($p->getName() !== $name) {
                            $player->teleport($p->getPosition()->asVector3());
                            Loader::getInstance()->SkillCooldown[$name] = 10;
                        } else {
                            $player->sendMessage(Loader::getInstance()->message["NoPlayer"]);
                        }
                    }
                }
            }
            if ($item->getCustomName() === "§r§eLeap§r") {
                $directionvector = $player->getDirectionVector()->multiply(4 / 2);
                $dx = $directionvector->getX();
                $dy = $directionvector->getY();
                $dz = $directionvector->getZ();
                $player->setMotion(new Vector3($dx, $dy + 0.5, $dz));
                Loader::getInstance()->SkillCooldown[$name] = 10;
            }
        } else if (isset(Loader::getInstance()->SkillCooldown[$name]) and Loader::getInstance()->SkillCooldown[$name] > 0) {
            $player->sendMessage(Loader::getInstance()->getPrefixCore() . "§r§cYou can't use this skill for " . floor(Loader::getInstance()->SkillCooldown[$name]) . " §cseconds");
            return;
        }
        if ($item->getCustomName() === "§r§bSettings") {
            Loader::$form->settingsForm($player);
        }
        if ($item->getCustomName() === "§r§aStop Timer §f| §bClick to use") {
            Loader::getInstance()->TimerData[$name] = 0;
            Loader::getInstance()->TimerTask[$name] = "no";
            $player->teleport(new Vector3(275, 66, 212));
            $player->sendMessage(Loader::getInstance()->getPrefixCore() . "§aYou Has been reset!");
        }
        if ($item->getCustomName() === "§r§aBack to Checkpoint §f| §bClick to use") {
            $config = new Config(Loader::getInstance()->getDataFolder() . "pkdata/" . $name . ".yml", CONFIG::YAML);
            $player->sendMessage(Loader::getInstance()->getPrefixCore() . "§aTeleport to Checkpoint");
            $player->teleport(new Vector3($config->get("X"), $config->get("Y"), $config->get("Z")));
        }
    }

    public function onPlayerDropItem(PlayerDropItemEvent $event): void
    {
        if ($event->isCancelled()) return;
        $event->cancel();
    }

    public function onPlayerLogin(PlayerLoginEvent $event)
    {
        $player = $event->getPlayer();
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
                $event->cancel();
            } else {
                Loader::getInstance()->db->query("DELETE FROM banPlayers WHERE player = '$banplayer';");
            }
        }
        $player->teleport(Server::getInstance()->getWorldManager()->getDefaultWorld()->getSafeSpawn());
        $player->teleport(new Vector3(255, 6, 255));
        ArenaUtils::getInstance()->DeviceCheck($player);
    }

    public function onPlayerLog(PlayerPreLoginEvent $event)
    {
        foreach (Server::getInstance()->getOnlinePlayers() as $p) {
            if ($p->getUniqueId() !== $event->getPlayerInfo()->getUuid() and strtolower($event->getPlayerInfo()->getUsername()) === strtolower($p->getName())) {
                $event->setKickReason(3, "§bGuardian §f» §cThis player is already online!");
            }
        }
    }

    public function onJoin(PlayerJoinEvent $event)
    {
        $player = $event->getPlayer();
        $player->getEffects()->clear();
        $player->sendMessage(Loader::getInstance()->getPrefixCore() . "§eLoading Player Data");
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        $event->setJoinMessage("§f[§a+§f] §a" . $player->getName());
        Loader::$cps->initPlayerClickData($player);
        $player->sendMessage(Loader::getInstance()->getPrefixCore() . "§aWelcome back to the game!");
        Loader::getinstance()->getScheduler()->scheduleRepeatingTask(new ScoreboardTask($player), 40);
        ArenaUtils::getInstance()->GiveItem($player);
    }

    public function onChat(PlayerChatEvent $event)
    {
        $player = $event->getPlayer();
        $message = $event->getMessage();
        $event->setFormat("§e" . ArenaUtils::getInstance()->getPlayerOs($player) . " §f| §a" . $player->getName() . "§6 > §f" . $message);
        if (isset(Loader::getInstance()->ChatCooldown[$player->getName()])) {
            if (time() - Loader::getInstance()->ChatCooldown[$player->getName()] < Loader::getInstance()->ChatCooldownSec) {
                $event->cancel();
                $time = Loader::getInstance()->ChatCooldownSec - (time() - Loader::getInstance()->ChatCooldown[$player->getName()]);
                $player->sendMessage(str_replace(["&", "{cooldown}"], ["§", $time], Loader::getInstance()->message["CooldownMessage"]));
            } else {
                $web = new DiscordWebhook(Loader::getInstance()->getConfig()->get("api"));
                $msg = new DiscordWebhookUtils();
                $msg2 = str_replace(["@here", "@everyone"], "", $message);
                $msg->setContent(">>> " . $player->getNetworkSession()->getPing() . "ms | " . ArenaUtils::getInstance()->getPlayerOs($player) . " " . $player->getDisplayName() . " > " . $msg2);
                $web->send($msg);
            }
        } else {
            Loader::getInstance()->ChatCooldown[$player->getName()] = (time() + Loader::getInstance()->ChatCooldownSec);
        }
    }

    public function onChangeSkin(PlayerChangeSkinEvent $event)
    {
        $player = $event->getPlayer();
        $name = $player->getName();
        if (isset(Loader::getInstance()->SkinCooldown[$name]) and Loader::getInstance()->SkinCooldown[$name] > 0) {
            $event->cancel();
            $player->sendMessage(Loader::getInstance()->getPrefixCore() . "§cYou can't change skin for " . floor(Loader::getInstance()->SkinCooldown[$name]) . " seconds!");
        } else {
            $player->sendMessage(Loader::getInstance()->getPrefixCore() . "§aSkin changed!");
            Loader::getInstance()->SkinCooldown[$name] = 10;
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
        if (isset(Loader::getInstance()->CombatTimer[$name])) {
            unset(Loader::getInstance()->CombatTimer[$name]);
        }
        if (isset(Loader::getInstance()->opponent[$name])) {
            Loader::getInstance()->BoxingPoint[Loader::getInstance()->opponent[$name]] = 0;
            unset(Loader::getInstance()->opponent[$name]);
        }
        if (isset(Loader::getInstance()->ChatCooldown[$player->getName()])) {
            unset(Loader::getInstance()->ChatCooldown[$player->getName()]);
        }
        if (isset(Loader::getInstance()->SkillCooldown[$name])) {
            unset(Loader::getInstance()->SkillCooldown[$name]);
        }
        if (isset(Loader::getInstance()->CombatTimer[$name])) {
            $player->kill();
        }
    }

    public function onDamage(EntityDamageEvent $event)
    {
        $entity = $event->getEntity();
        $world = $entity->getWorld();
        if ($event->getCause() === EntityDamageEvent::CAUSE_FALL) {
            $event->cancel();
        }
        if ($event instanceof EntityDamageByChildEntityEvent) {
            $owner = $event->getChild()->getOwningEntity();
            if ($owner instanceof Player and $entity instanceof Player) {
                $name = $owner->getName();
                if ($name === $entity->getName()) {
                    $event->cancel();
                    $entity->sendMessage(Loader::getInstance()->getPrefixCore() . "§cYou can't attack yourself!");
                }
            }
        }
        if ($event->getCause() === EntityDamageEvent::CAUSE_ENTITY_ATTACK) {
            $damager = $event->getDamager();
            $world->addParticle(new Position($entity->getPosition()->getX(), $entity->getPosition()->getY() - 1, $entity->getPosition()->getZ(), $entity->getWorld()), new LavaParticle());
            if ($entity instanceof Player and $damager instanceof Player) {
                $dis = floor($entity->getLocation()->asVector3()->distance($damager->getPosition()->asVector3()));
                $name = $damager->getName();
                if ($damager->getGamemode() !== Gamemode::CREATIVE() and $damager->getWorld() !== Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getKnockBackArena())) {
                    if ($dis > 5.5) {
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
        if ($player->getPosition()->getY() <= 0) {
            if ($player->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getKnockbackArena())) {
                $player->kill();
            }
        }
        if (isset(Loader::getInstance()->Sprinting[$player->getName()])) {
            if (Loader::getInstance()->Sprinting[$player->getName()] === true and !$player->isSprinting()) {
                $player->toggleSprint(true);
            }
        } else {
            Loader::getInstance()->Sprinting[$player->getName()] = false;
        }
        $block = $player->getWorld()->getBlock(new Vector3($player->getPosition()->asPosition()->x, $player->getPosition()->asPosition()->y - 0.5, $player->getPosition()->asPosition()->z));
        if ($player->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getKitPVPArena())) {
            if ($block->getId() === BlockLegacyIds::GOLD_BLOCK) {
                $player->getEffects()->add(new EffectInstance(VanillaEffects::LEVITATION(), 100, 3, false));
            }
        }
        if ($player->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getParkourArena())) {
            if ($block->getId() === 25) {
                if (Loader::getInstance()->TimerTask[$name] === "no") {
                    Loader::getInstance()->TimerTask[$name] = "yes";
                }
            }
            if ($block->getId() === 243) {
                if (Loader::getInstance()->TimerTask[$name] === "yes") {
                    Loader::getInstance()->TimerTask[$name] = "no";
                }
            }
            if ($block->getId() === 124) {
                if (Loader::getInstance()->TimerTask[$name] === "yes") {
                    $mins = floor(Loader::getInstance()->TimerData[$name] / 6000);
                    $secs = floor((Loader::getInstance()->TimerData[$name] / 100) % 60);
                    $mili = Loader::getInstance()->TimerData[$name] % 100;
                    $prefix = Loader::getInstance()->getPrefixCore();
                    $message = ($name . " §aHas Finished Parkour " . $mins . " : " . $secs . " : " . $mili);
                    Server::getInstance()->broadcastMessage($prefix . $message);
                    Loader::getInstance()->TimerTask[$name] = "no";
                    $config = new Config(Loader::getInstance()->getDataFolder() . "pkdata/" . $name . ".yml", CONFIG::YAML);
                    $player->teleport(new Vector3(275, 66, 212));
                    Loader::getInstance()->getScheduler()->scheduleDelayedRepeatingTask(
                        new ParkourFinishTask($player, $player->getWorld()), 0, 2
                    );
                    $config->set("X", 255);
                    $config->set("Y", 76);
                    $config->set("Z", 255);
                }
            }
            if ($block->getId() === 188) {
                $config = new Config(Loader::getInstance()->getDataFolder() . "pkdata/" . $name . ".yml", CONFIG::YAML);
                $config->set("X", $player->getPosition()->getX());
                $config->set("Y", $player->getPosition()->getY());
                $config->set("Z", $player->getPosition()->getZ());
                try {
                    $config->save();
                } catch (JsonException $e) {
                    $player->sendMessage(Loader::getInstance()->getPrefixCore() . "§cError while saving the file");
                    Loader::getInstance()->getLogger()->info($e);
                }
            }
        }
    }

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
                ArenaUtils::getInstance()->DeathReset($player, $damager);
                foreach (Loader::getInstance()->getServer()->getOnlinePlayers() as $p) {
                    $p->sendMessage(Loader::getInstance()->getPrefixCore() . "§a" . $player->getName() . " §fhas been killed by §c" . $player->getLastDamageCause()->getDamager()->getName());
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
