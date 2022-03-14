<?php

namespace Kohaku\Core\Task;

use Kohaku\Core\Loader;
use Kohaku\Core\Utils\ArenaUtils;
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
            if (isset(Loader::getInstance()->SkinCooldown[$name])) {
                if (Loader::getInstance()->SkinCooldown[$name] > 0) {
                    Loader::getInstance()->SkinCooldown[$name] -= 0.05;
                } else {
                    unset(Loader::getInstance()->SkinCooldown[$name]);
                    $player->sendMessage(Loader::getInstance()->getPrefixCore() . "§aNow you can Change Skin!");
                }
            }
            if (isset(Loader::getInstance()->SkillCooldown[$name])) {
                if (Loader::getInstance()->SkillCooldown[$name] > 0) {
                    Loader::getInstance()->SkillCooldown[$name] -= 0.05;
                } else {
                    if ($player->getArmorInventory()->getHelmet()->getId() == ItemIds::SKULL) {
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
                    Loader::getInstance()->CombatTimer[$name] -= 0.05;
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
        }
    }
}