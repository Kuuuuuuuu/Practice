<?php

declare(strict_types=1);

namespace Kohaku\Core\Task;

use Kohaku\Core\Loader;
use Kohaku\Core\Utils\ArenaUtils;
use pocketmine\item\ItemIds;
use pocketmine\permission\DefaultPermissions;
use pocketmine\player\GameMode;
use pocketmine\scheduler\Task;
use pocketmine\Server;

class PlayerTask extends Task
{

    public function onRun(): void
    {
        foreach (Loader::getInstance()->getServer()->getOnlinePlayers() as $player) {
            $name = $player->getName();
            if (!isset(Loader::getInstance()->inSumo[$name])) {
                Loader::getInstance()->inSumo[$name] = false;
            }
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
            /*-------------------------------------------------------------------------- tag PVP --------------------------------------------------------------------------*/
            $tagpvp = "§b{hp}§fHP§f | §b{ping}§fms §f| §aCPS §f: §b{cps}";
            $tagpvp = str_replace("{ping}", (string)$ping, $tagpvp);
            $tagpvp = str_replace("{hp}", (string)round($player->getHealth(), 1), $tagpvp);
            $tagpvp = str_replace("{cps}", (string)$nowcps, $tagpvp);
            /*-------------------------------------------------------------------------- Untag PVP --------------------------------------------------------------------------*/
            $untagpvp = "§b" . ArenaUtils::getInstance()->getPlayerOs($player) . " §f| §b" . ArenaUtils::getInstance()->getPlayerControls($player) . " §f| §b" . ArenaUtils::getInstance()->getToolboxCheck($player);
            if ($player->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getParkourArena())) {
                $player->setScoreTag($tagparkour);
            } else {
                if (isset(Loader::getInstance()->CombatTimer[$name]) or $player->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getKitPVPArena()) or $player->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName("sumo")) {
                    $player->setScoreTag($tagpvp);
                } else if (!isset(Loader::getInstance()->CombatTimer[$name])) {
                    $player->setScoreTag($untagpvp);
                }
            }
            if (isset(Loader::getInstance()->inSumo[$name]) and Loader::getInstance()->inSumo[$name] === true) {
                if ($player->getWorld() === Server::getInstance()->getWorldManager()->getDefaultWorld()) {
                    Loader::getInstance()->inSumo[$name] = false;
                }
                if ($player->getPosition()->getY() < 20) {
                    Loader::getInstance()->inSumo[$name] = false;
                    $player->teleport(Server::getInstance()->getWorldManager()->getDefaultWorld()->getSpawnLocation());
                    ArenaUtils::getInstance()->addDeath($player);
                }
            }
            if (isset(Loader::getInstance()->AutoClickWarn[$name])) {
                if ($nowcps > Loader::getInstance()->MaximumCPS) {
                    Loader::getInstance()->AutoClickWarn[$name]++;
                }
                if (Loader::getInstance()->AutoClickWarn[$name] > 5) {
                    Loader::getInstance()->AutoClickWarn[$name] = 0;
                    $message = ($name . " §eHas " . $nowcps . " §cCPS" . "§f(§a" . $player->getNetworkSession()->getPing() . " §ePing §f/ §6" . ArenaUtils::getInstance()->getPlayerControls($player) . "§f)");
                    Server::getInstance()->broadcastMessage(Loader::getInstance()->AntiCheatName . $message);
                    $player->kick(Loader::getInstance()->AntiCheatName . "§cYou have been kicked for using §e" . $nowcps . " §cCPS§f(§a" . $player->getNetworkSession()->getPing() . " §ePing §f/ §6" . ArenaUtils::getInstance()->getPlayerControls($player) . "§f)");
                }
            } else {
                Loader::getInstance()->AutoClickWarn[$name] = 0;
            }
            if (isset(Loader::getInstance()->TimerTask[$name])) {
                if (isset(Loader::getInstance()->TimerData[$name])) {
                    if (Loader::getInstance()->TimerTask[$name] === "yes") {
                        $now = Loader::getInstance()->TimerData[$name] + 5;
                        Loader::getInstance()->TimerData[$name] = $now;
                    } else {
                        Loader::getInstance()->TimerData[$name] = 0;
                    }
                } else {
                    Loader::getInstance()->TimerData[$name] = 0;
                }
            } else {
                Loader::getInstance()->TimerTask[$name] = "no";
            }
            if ($player->getHungerManager()->getFood() < 20) {
                $player->getHungerManager()->setFood(20);
            }
            if ($player->getInventory()->getItemInHand()->getId() === ItemIds::TNT or $player->getInventory()->getItemInHand()->getId() === ItemIds::BUCKET) {
                $player->sendMessage(Loader::getInstance()->getPrefixCore() . "§cThis Item is not allowed in this world!");
                $player->getCursorInventory()->clearAll();
            }
            if ($player->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName("plot")) {
                if (!$player->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
                    if ($player->getGamemode() !== GameMode::CREATIVE()) {
                        $player->setGamemode(GameMode::CREATIVE());
                        $player->sendMessage(Loader::getInstance()->getPrefixCore() . "§aNow you are in §eCreative Mode§a!");
                    }
                }
            } else {
                if (!$player->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
                    if ($player->getGamemode() !== GameMode::ADVENTURE()) {
                        $player->setGamemode(GameMode::ADVENTURE());
                        $player->sendMessage(Loader::getInstance()->getPrefixCore() . "§aNow you are in §eAdventure Mode§a!");
                    }
                }
            }
            if ($player->getWorld() !== Server::getInstance()->getWorldManager()->getWorldByName("plot") and $player->getWorld() !== Server::getInstance()->getWorldManager()->getWorldByName("plot") and $player->getWorld() !== Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getParkourArena())) {
                $player->sendTip("§bCPS: §f" . Loader::$cps->getClicks($player));
            }
            if ($player->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getBoxingArena())) {
                if (isset(Loader::getInstance()->BoxingPoint[$name])) {
                    $point = Loader::getInstance()->BoxingPoint[$name ?? null] ?? 0;
                    $opponent = Loader::getInstance()->BoxingPoint[Loader::getInstance()->opponent[$name ?? null] ?? null] ?? 0;
                    $player->sendTip("§aYour Points: §f" . $point . " | §cOpponent: §f" . $opponent . " | §bCPS: §f" . Loader::$cps->getClicks($player));
                } else {
                    Loader::getInstance()->BoxingPoint[$name] = 0;
                }
            }
            if ($player->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getParkourArena())) {
                if (isset(Loader::getInstance()->TimerTask[$name])) {
                    if (Loader::getInstance()->TimerTask[$name] === "yes") {
                        $mins = floor(Loader::getInstance()->TimerData[$name] / 6000);
                        $secs = floor((Loader::getInstance()->TimerData[$name] / 100) % 60);
                        $mili = Loader::getInstance()->TimerData[$name] % 100;
                        $player->sendTip("§a" . $mins . " : " . $secs . " : " . $mili);
                    } else {
                        $player->sendTip("§a0 : 0 : 0");
                    }
                } else {
                    Loader::getInstance()->TimerTask[$name] = "no";
                }
            }
        }
    }
}