<?php

declare(strict_types=1);

namespace Nayuki\Utils;

use Nayuki\Game\Kits\Kit;
use Nayuki\Game\Kits\KitRegistry;
use Nayuki\PracticeConfig;
use Nayuki\PracticeCore;
use Nayuki\Utils\Forms\CustomForm;
use Nayuki\Utils\Forms\SimpleForm;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use pocketmine\Server;

class FormUtils
{
    /**
     * @param Player $player
     * @return void
     */
    public function ArenaForm(Player $player): void
    {
        $form = new SimpleForm(function (Player $player, int $data = null) {
            if ($data !== null) {
                switch ($data) {
                    case 0:
                        PracticeCore::getArenaManager()->onJoinNodebuff($player);
                        break;
                    default:
                        print 'Error';
                }
            }
        });
        $form->setTitle(PracticeConfig::Server_Name . '§cMenu');
        $form->addButton("§aNodebuff\n§bPlayers: §f" . PracticeCore::getArenaFactory()->getPlayers(PracticeCore::getArenaFactory()->getNodebuffArena()), 0, 'textures/items/potion_bottle_splash_heal.png');
        $player->sendForm($form);
    }

    /**
     * @param Player $player
     * @return void
     */
    public function SettingsForm(Player $player): void
    {
        $form = new CustomForm(function (Player $player, array $data = null) {
            if ($data !== null) {
                $session = PracticeCore::getPlayerSession()::getSession($player);
                if (isset($data['CPS'])) {
                    $session->CpsCounterEnabled = (bool)$data['CPS'];
                }
                if (isset($data['Scoreboard'])) {
                    $session->ScoreboardEnabled = (bool)$data['Scoreboard'];
                }
                if (isset($data['SmoothPearl'])) {
                    $session->SmoothPearlEnabled = (bool)$data['SmoothPearl'];
                }
            }
        });
        $form->setTitle(PracticeConfig::Server_Name . '§cMenu');
        $form->addToggle('Cps Counter', PracticeCore::getPlayerSession()::getSession($player)->CpsCounterEnabled, 'CPS');
        $form->addToggle('Scoreboard', PracticeCore::getPlayerSession()::getSession($player)->ScoreboardEnabled, 'Scoreboard');
        $form->addToggle('Smooth Pearl', PracticeCore::getPlayerSession()::getSession($player)->SmoothPearlEnabled, 'SmoothPearl');
        $player->sendForm($form);
    }

    /**
     * @param Player $player
     * @return void
     */
    public function duelForm(Player $player): void
    {
        $form = new SimpleForm(function (Player $player, int $data = null) {
            if ($data !== null) {
                $session = PracticeCore::getPlayerSession()::getSession($player);
                switch ($data) {
                    case 0:
                        $session->DuelKit = KitRegistry::fromString('NoDebuff');
                        $session->isQueueing = true;
                        $player->getInventory()->clearAll();
                        $player->getInventory()->setItem(8, VanillaItems::RED_DYE()->setCustomName('§r§cLeave Queue')->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 10)));
                        PracticeCore::getPracticeUtils()->checkQueue($player);
                        break;
                    case 1:
                        $session->DuelKit = KitRegistry::fromString('Boxing');
                        $session->isQueueing = true;
                        $player->getInventory()->clearAll();
                        $player->getInventory()->setItem(8, VanillaItems::RED_DYE()->setCustomName('§r§cLeave Queue')->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 10)));
                        PracticeCore::getPracticeUtils()->checkQueue($player);
                        break;
                }
            }
        });
        $form->setTitle(PracticeConfig::Server_Name . '§cDuel');
        $form->addButton("§aNodebuff\n§bQueue§f: " . $this->getQueue('Fist'), 0, 'textures/items/paper.png');
        $form->addButton("§aBoxing\n§bQueue§f: " . $this->getQueue('NoDebuff'), 0, 'textures/items/paper.png');
        $player->sendForm($form);
    }

    /**
     * @param string $kit
     * @return int
     */
    private function getQueue(string $kit): int
    {
        $Count = 0;
        foreach (Server::getInstance()->getOnlinePlayers() as $p) {
            if ($p instanceof Player) {
                $session = PracticeCore::getPlayerSession()::getSession($p);
                $Qkit = $session->DuelKit;
                if (($Qkit instanceof Kit) && !$session->isDueling && $Qkit->getName() === $kit) {
                    $Count++;
                }
            }
        }
        return $Count;
    }
}
