<?php

declare(strict_types=1);

namespace Nayuki\Utils;

use Nayuki\Game\Kits\Kit;
use Nayuki\Game\Kits\KitRegistry;
use Nayuki\PracticeConfig;
use Nayuki\PracticeCore;
use Nayuki\Utils\Forms\CustomForm;
use Nayuki\Utils\Forms\SimpleForm;
use pocketmine\block\utils\DyeColor;
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
                        PracticeCore::getArenaManager()->joinArenas($player, 'Nodebuff');
                        break;
                    case 1:
                        PracticeCore::getArenaManager()->joinArenas($player, 'Fist');
                        break;
                    case 2:
                        PracticeCore::getArenaManager()->joinArenas($player, 'Resistance');
                        break;
                    case 3:
                        PracticeCore::getArenaManager()->joinArenas($player, 'Combo');
                        break;
                    default:
                        print 'Error';
                }
            }
        });
        $form->setTitle(PracticeConfig::Server_Name . '§cMenu');
        $form->addButton("§aNodebuff\n§bPlayers: §f" . PracticeCore::getArenaFactory()->getPlayers(PracticeCore::getArenaFactory()->getArenas('Nodebuff')), 0, 'textures/items/potion_bottle_splash_heal.png');
        $form->addButton("§aFist\n§bPlayers: §f" . PracticeCore::getArenaFactory()->getPlayers(PracticeCore::getArenaFactory()->getArenas('Fist')), 0, 'textures/items/beef_cooked.png');
        $form->addButton("§aResistance\n§bPlayers: §f" . PracticeCore::getArenaFactory()->getPlayers(PracticeCore::getArenaFactory()->getArenas('Resistance')), 0, 'textures/items/snowball.png');
        $form->addButton("§aCombo\n§bPlayers: §f" . PracticeCore::getArenaFactory()->getPlayers(PracticeCore::getArenaFactory()->getArenas('Combo')), 0, 'textures/items/diamond_sword.png');
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
                $session = PracticeCore::getSessionManager()->getSession($player);
                foreach ($data as $key => $value) {
                    switch (strtolower($key)) {
                        case 'cps':
                            $session->CpsCounterEnabled = (bool)$value;
                            break;
                        case 'scoreboard':
                            $session->ScoreboardEnabled = (bool)$value;
                            break;
                        case 'smoothpearl':
                            $session->SmoothPearlEnabled = (bool)$value;
                            break;
                        default:
                            break;
                    }
                }
            }
        });
        $form->setTitle(PracticeConfig::Server_Name . '§cMenu');
        $form->addToggle('Cps Counter', PracticeCore::getSessionManager()->getSession($player)->CpsCounterEnabled, 'CPS');
        $form->addToggle('Scoreboard', PracticeCore::getSessionManager()->getSession($player)->ScoreboardEnabled, 'Scoreboard');
        $form->addToggle('Smooth Pearl', PracticeCore::getSessionManager()->getSession($player)->SmoothPearlEnabled, 'SmoothPearl');
        $player->sendForm($form);
    }

    /**
     * @param Player $player
     * @return void
     */
    public function duelForm(Player $player): void
    {
        $form = new SimpleForm(function (Player $player, string $data = null) {
            if ($data !== null) {
                $session = PracticeCore::getSessionManager()->getSession($player);
                $session->DuelKit = KitRegistry::fromString($data);
                $session->isQueueing = true;
                $player->getInventory()->clearAll();
                $player->getInventory()->setItem(8, VanillaItems::DYE()->setColor(DyeColor::RED())->setCustomName('§r§cLeave Queue')->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 10)));
                PracticeCore::getUtils()->checkQueue($player);
            }
        });
        $form->setTitle(PracticeConfig::Server_Name . '§cDuel');
        foreach (KitRegistry::getKits() as $kit) {
            /** @var Kit $kit */
            if ($kit->getName() === 'Resistance') {
                continue;
            }
            $form->addButton("§a{$kit->getName()}\n§bQueue§f: " . $this->getQueue($kit->getName()), 0, 'textures/items/paper.png', $kit->getName());
        }
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
                $session = PracticeCore::getSessionManager()->getSession($p);
                $Qkit = $session->DuelKit;
                if (($Qkit instanceof Kit) && !$session->isDueling && $Qkit->getName() === $kit) {
                    $Count++;
                }
            }
        }
        return $Count;
    }
}
