<?php

declare(strict_types=1);

namespace Kohaku\Core\Task;

use Kohaku\Core\Loader;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\scheduler\Task;
use pocketmine\Server;

class PlayerCooldownTask extends Task
{

    public function onRun(): void
    {
        foreach (Server::getInstance()->getOnlinePlayers() as $player) {
            $name = $player->getName();
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
        }
    }

}