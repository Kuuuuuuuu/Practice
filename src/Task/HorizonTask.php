<?php

declare(strict_types=1);

namespace Kohaku\Core\Task;

use Kohaku\Core\Loader;
use Kohaku\Core\Utils\ArenaUtils;
use Kohaku\Core\Utils\DeleteBlocksHandler;
use Kohaku\Core\Utils\ScoreboardUtils;
use pocketmine\entity\object\ItemEntity;
use pocketmine\entity\projectile\Arrow;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\scheduler\Task;
use pocketmine\Server;

class HorizonTask extends Task
{

    private int $tick = 0;

    public function onRun(): void
    {
        $this->tick++;
        if ($this->tick % 20 === 0) {
            DeleteBlocksHandler::getInstance()->update();
            $this->updatePlayer();
            $this->RestartServer();
        }
        if ($this->tick % 40 === 0) {
            $this->updateTag();
        }
        if ($this->tick % 60 === 0) {
            $this->updateScoreboard();
            $this->updateRank();
        }
        if ($this->tick % 300 === 0) {
            if (Loader::getInstance()->LeaderboardMode === 1) {
                Loader::getInstance()->LeaderboardMode = 2;
            } else {
                Loader::getInstance()->LeaderboardMode = 1;
            }
        }
        if ($this->tick % 2000 === 0) {
            foreach (Server::getInstance()->getWorldManager()->getWorlds() as $level) {
                foreach ($level->getEntities() as $entity) {
                    if ($entity instanceof ItemEntity or $entity instanceof Arrow) {
                        if ($entity->onGround and $entity->isAlive()) {
                            $entity->flagForDespawn();
                            $entity->close();
                        }
                    }
                }
            }
        }
        foreach (Loader::getInstance()->getServer()->getOnlinePlayers() as $player) {
            $name = $player->getName();
            if ($player->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getBoxingArena())) {
                if (isset(Loader::getInstance()->BoxingPoint[$name])) {
                    $point = Loader::getInstance()->BoxingPoint[$name];
                    $opponent = Loader::getInstance()->BoxingPoint[Loader::getInstance()->PlayerOpponent[$name ?? null] ?? null] ?? 0;
                    $player->sendTip("§aYour Points: §f" . $point . " | §cOpponent: §f" . $opponent . " | §bCPS: §f" . Loader::$cps->getClicks($player));
                } else {
                    Loader::getInstance()->BoxingPoint[$name] = 0;
                }
            } else if ($player->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getParkourArena())) {
                if (isset(Loader::getInstance()->TimerTask[$name])) {
                    if (Loader::getInstance()->TimerTask[$name] === true) {
                        if (isset(Loader::getInstance()->TimerData[$name])) {
                            Loader::getInstance()->TimerData[$name] += 5;
                        } else {
                            Loader::getInstance()->TimerData[$name] = 0;
                        }
                        $mins = floor(Loader::getInstance()->TimerData[$name] / 6000);
                        $secs = floor((Loader::getInstance()->TimerData[$name] / 100) % 60);
                        $mili = Loader::getInstance()->TimerData[$name] % 100;
                        $player->sendTip("§a" . $mins . " : " . $secs . " : " . $mili);
                    } else {
                        $player->sendTip("§a0 : 0 : 0");
                        Loader::getInstance()->TimerData[$name] = 0;
                    }
                } else {
                    Loader::getInstance()->TimerTask[$name] = false;
                }
            }
        }
    }

    private function updatePlayer()
    {
        foreach (Loader::getInstance()->getServer()->getOnlinePlayers() as $player) {
            $name = $player->getName();
            $nowcps = Loader::$cps->getClicks($player);
            if ($nowcps > Loader::getInstance()->MaximumCPS) {
                $player->kill();
            }
            if (isset(Loader::getInstance()->PlayerSprint[$name]) and Loader::getInstance()->PlayerSprint[$name] === true) {
                if (!$player->isSprinting()) {
                    $player->toggleSprint(true);
                }
            }
            if ($player->getWorld() === Server::getInstance()->getWorldManager()->getDefaultWorld()) {
                if (isset(Loader::getInstance()->CombatTimer[$name])) {
                    unset(Loader::getInstance()->CombatTimer[$name]);
                } else if (isset(Loader::getInstance()->PlayerOpponent[$name])) {
                    unset(Loader::getInstance()->PlayerOpponent[$name]);
                }
            }
            if (isset(Loader::getInstance()->SkillCooldown[$name])) {
                if (Loader::getInstance()->SkillCooldown[$name] > 0) {
                    Loader::getInstance()->SkillCooldown[$name] -= 1;
                } else {
                    if ($player->getArmorInventory()->getHelmet()->getId() === ItemIds::SKULL) {
                        $player->getArmorInventory()->setHelmet(ItemFactory::getInstance()->get(ItemIds::AIR));
                    }
                    $player->sendMessage(Loader::getInstance()->MessageData["SkillCleared"]);
                    unset(Loader::getInstance()->SkillCooldown[$name]);
                }
            }
            if (isset(Loader::getInstance()->CombatTimer[$name])) {
                if (Loader::getInstance()->CombatTimer[$name] > 0) {
                    $percent = floatval(Loader::getInstance()->CombatTimer[$name] / 10);
                    $player->getXpManager()->setXpProgress($percent);
                    Loader::getInstance()->CombatTimer[$name] -= 1;
                } else {
                    $player->getXpManager()->setXpProgress(0.0);
                    $player->sendMessage(Loader::getInstance()->MessageData["StopCombat"]);
                    unset(Loader::getInstance()->BoxingPoint[$name ?? null]);
                    unset(Loader::getInstance()->CombatTimer[$name]);
                    unset(Loader::getInstance()->PlayerOpponent[$name]);
                }
            }
            if (isset(Loader::getInstance()->ChatCooldown[$name])) {
                if (Loader::getInstance()->ChatCooldown[$name] > 0) {
                    Loader::getInstance()->ChatCooldown[$name] -= 1;
                } else {
                    unset(Loader::getInstance()->ChatCooldown[$name]);
                }
            }
            if (isset(Loader::getInstance()->ArrowOITC[$name])) {
                if (Loader::getInstance()->ArrowOITC[$name] > 0) {
                    Loader::getInstance()->ArrowOITC[$name] -= 1;
                } else {
                    if ($player->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getOITCArena())) {
                        $player->getInventory()->addItem(ItemFactory::getInstance()->get(ItemIds::ARROW, 0, 1));
                    }
                    unset(Loader::getInstance()->ArrowOITC[$name]);
                }
            }
        }
    }

    private function RestartServer()
    {
        if (Loader::getInstance()->Restarted) {
            Loader::getInstance()->RestartTime--;
            foreach (Server::getInstance()->getOnlinePlayers() as $player) {
                switch (Loader::getInstance()->RestartTime) {
                    case 30:
                        $player->sendMessage(Loader::getPrefixCore() . "§cServer will restart in §e30 §cseconds");
                        break;
                    case 15:
                        $player->sendMessage(Loader::getPrefixCore() . "§cServer will restart in §e15 §cseconds");
                        break;
                    case 10:
                        $player->sendMessage(Loader::getPrefixCore() . "§cServer will restart in §e10 §cseconds");
                        break;
                    case 5:
                        $player->sendMessage(Loader::getPrefixCore() . "§cServer will restart in §e5 §cseconds");
                        break;
                    case 4:
                        $player->sendMessage(Loader::getPrefixCore() . "§cServer will restart in §e4 §cseconds");
                        break;
                    case 3:
                        $player->sendMessage(Loader::getPrefixCore() . "§cServer will restart in §e3 §cseconds");
                        break;
                    case 2:
                        $player->sendMessage(Loader::getPrefixCore() . "§cServer will restart in §e2 §cseconds");
                        break;
                    case 1:
                        $player->sendMessage(Loader::getPrefixCore() . "§cServer will restart in §e1 §csecond");
                        break;
                    case 0:
                        Server::getInstance()->shutdown();
                        break;
                }
            }
        }
    }

    private function updateTag()
    {
        foreach (Loader::getInstance()->getServer()->getOnlinePlayers() as $player) {
            $name = $player->getName();
            $ping = $player->getNetworkSession()->getPing();
            $nowcps = Loader::$cps->getClicks($player);
            $tagparkour = "§f[§b {mins} §f: §b{secs} §f: §b{mili} {ping}ms §f]\n §f[§b Jump Count§f: §b{jump} §f]";
            $tagparkour = str_replace("{ping}", (string)$ping, $tagparkour);
            if (isset(Loader::getInstance()->JumpCount[$name])) {
                $tagparkour = str_replace("{jump}", (string)Loader::getInstance()->JumpCount[$name] ?? null, $tagparkour);
            } else {
                $tagparkour = str_replace("{jump}", "0", $tagparkour);
            }
            if (isset(Loader::getInstance()->TimerData[$name])) {
                $tagparkour = str_replace("{mili}", (string)floor(Loader::getInstance()->TimerData[$name] % 100), $tagparkour);
                $tagparkour = str_replace("{secs}", (string)floor((Loader::getInstance()->TimerData[$name] / 100) % 60), $tagparkour);
                $tagparkour = str_replace("{mins}", (string)floor(Loader::getInstance()->TimerData[$name] / 6000), $tagparkour);
            } else {
                $tagparkour = str_replace("{mili}", "0", $tagparkour);
                $tagparkour = str_replace("{secs}", "0", $tagparkour);
                $tagparkour = str_replace("{mins}", "0", $tagparkour);
            }
            $tagpvp = "§b{ping}§fms §f| §b{cps} §fCPS";
            $tagpvp = str_replace("{ping}", (string)$ping, $tagpvp);
            $tagpvp = str_replace("{cps}", (string)$nowcps, $tagpvp);
            $untagpvp = "§b" . ArenaUtils::getInstance()->getPlayerOs($player) . " §f| §b" . ArenaUtils::getInstance()->getPlayerControls($player) . " §f| §b" . ArenaUtils::getInstance()->getToolboxCheck($player);
            if ($player->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getParkourArena())) {
                $player->setScoreTag($tagparkour);
            } else {
                if (isset(Loader::getInstance()->CombatTimer[$name]) or $player->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getSumoDArena()) or $player->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getKitPVPArena()) or $player->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getOITCArena()) or $player->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getKnockbackArena()) or $player->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getBuildArena())) {
                    $player->setScoreTag($tagpvp);
                } else if (!isset(Loader::getInstance()->CombatTimer[$name])) {
                    $player->setScoreTag($untagpvp);
                }
            }
        }
    }

    private function updateScoreboard()
    {
        foreach (Server::getInstance()->getOnlinePlayers() as $player) {
            if ($player->isOnline()) {
                if ($player->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName("aqua")) {
                    Loader::$score->remove($player);
                } else if ($player->getWorld() === Server::getInstance()->getWorldManager()->getDefaultWorld()) {
                    ScoreboardUtils::getInstance()->sb($player);
                } else if ($player->getWorld() !== Server::getInstance()->getWorldManager()->getDefaultWorld() and $player->getWorld() !== Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getParkourArena())) {
                    ScoreboardUtils::getInstance()->sb2($player);
                } else if ($player->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getParkourArena())) {
                    ScoreboardUtils::getInstance()->Parkour($player);
                }
            }
        }
    }

    private function updateRank()
    {
        foreach (Server::getInstance()->getOnlinePlayers() as $player) {
            $name = $player->getName();
            if (ArenaUtils::getInstance()->getData($name)->getTag() !== null) {
                $player->setNameTag(ArenaUtils::getInstance()->getData($name)->getRank() . "§a " . $player->getDisplayName() . " §f[" . ArenaUtils::getInstance()->getData($name)->getTag() . "§f]");
            } else {
                $player->setNameTag(ArenaUtils::getInstance()->getData($name)->getRank() . "§a " . $player->getDisplayName());
            }
        }
    }
}