<?php

declare(strict_types=1);

namespace Kohaku\Core\Task;

use Kohaku\Core\Loader;
use Kohaku\Core\Utils\ArenaUtils;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\scheduler\Task;
use pocketmine\Server;

class PlayerTask extends Task
{

    private int $tick = 0;

    public function onRun(): void
    {
        $this->tick++;
        foreach (Loader::getInstance()->getServer()->getOnlinePlayers() as $player) {
            $name = $player->getName();
            $ping = $player->getNetworkSession()->getPing();
            $nowcps = Loader::$cps->getClicks($player);
            if ($this->tick === 12) {
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
                    if (isset(Loader::getInstance()->CombatTimer[$name]) or $player->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getKitPVPArena()) or $player->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getOITCArena()) or $player->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getKnockbackArena())) {
                        $player->setScoreTag($tagpvp);
                    } else if (!isset(Loader::getInstance()->CombatTimer[$name])) {
                        $player->setScoreTag($untagpvp);
                    }
                }
            }
            if ($this->tick === 15) {
                if ($nowcps > Loader::getInstance()->MaximumCPS) {
                    $message = ($name . " §eHas " . $nowcps . " §cCPS" . "§f(§a" . $player->getNetworkSession()->getPing() . " §ePing §f/ §6" . ArenaUtils::getInstance()->getPlayerControls($player) . "§f)");
                    Server::getInstance()->broadcastMessage(Loader::getInstance()->message["AntiCheatName"] . $message);
                }
                if (isset(Loader::getInstance()->PlayerSprint[$name]) and Loader::getInstance()->PlayerSprint[$name] === true) {
                    if (!$player->isSprinting()) {
                        $player->toggleSprint(true);
                    }
                }
                if ($player->getWorld() === Server::getInstance()->getWorldManager()->getDefaultWorld()) {
                    if (isset(Loader::getInstance()->CombatTimer[$name])) {
                        unset(Loader::getInstance()->CombatTimer[$name]);
                    } else if (isset(Loader::getInstance()->opponent[$name])) {
                        unset(Loader::getInstance()->opponent[$name]);
                    }
                }
            }
            if ($this->tick >= 20) {
                if (isset(Loader::getInstance()->SkillCooldown[$name])) {
                    if (Loader::getInstance()->SkillCooldown[$name] > 0) {
                        Loader::getInstance()->SkillCooldown[$name] -= 1;
                    } else {
                        if ($player->getArmorInventory()->getHelmet()->getId() === ItemIds::SKULL) {
                            $player->getArmorInventory()->setHelmet(ItemFactory::getInstance()->get(ItemIds::AIR));
                        }
                        $player->sendMessage(Loader::getInstance()->message["SkillCleared"]);
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
                        $player->sendMessage(Loader::getInstance()->message["StopCombat"]);
                        unset(Loader::getInstance()->BoxingPoint[$name ?? null]);
                        unset(Loader::getInstance()->CombatTimer[$name]);
                        unset(Loader::getInstance()->opponent[$name]);
                    }
                } else {
                    $player->getXpManager()->setXpProgress(0.0);
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
                $this->tick = 0;
            }
            if ($player->getWorld() !== Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getParkourArena()) and $player->getWorld() !== Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getBoxingArena())) {
                $player->sendTip("§bCPS: §f" . Loader::$cps->getClicks($player));
            } else if ($player->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getBoxingArena())) {
                if (isset(Loader::getInstance()->BoxingPoint[$name])) {
                    $point = Loader::getInstance()->BoxingPoint[$name];
                    $opponent = Loader::getInstance()->BoxingPoint[Loader::getInstance()->opponent[$name ?? null] ?? null] ?? 0;
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
}