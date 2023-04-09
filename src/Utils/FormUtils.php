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
use pocketmine\entity\Skin;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use pocketmine\Server;

final class FormUtils
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
                        PracticeCore::getArenaManager()->joinArenas($player, 'Fist');
                        break;
                    case 1:
                        PracticeCore::getArenaManager()->joinArenas($player, 'Resistance');
                        break;
                    default:
                        print 'Error';
                }
            }
        });
        $form->setTitle(PracticeConfig::Server_Name . '§cMenu');
        $form->addButton("§aFist\n§bPlayers: §f" . PracticeCore::getArenaFactory()->getPlayers(PracticeCore::getArenaFactory()->getArenas('Fist')), 0, 'textures/items/beef_cooked.png');
        $form->addButton("§aResistance\n§bPlayers: §f" . PracticeCore::getArenaFactory()->getPlayers(PracticeCore::getArenaFactory()->getArenas('Resistance')), 0, 'textures/items/snowball.png');
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
                        case 'lightning':
                            $session->isLightningKill = (bool)$value;
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
        $form->addToggle('Lightning Kill', PracticeCore::getSessionManager()->getSession($player)->isLightningKill, 'lightning');
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
        $banKits = array_flip([
            'resistance',
        ]);
        foreach (KitRegistry::getKits() as $kit) {
            /** @var Kit $kit */
            if (isset($banKits[strtolower($kit->getName())])) {
                continue;
            }
            $queue = $this->getQueue($kit->getName());
            $form->addButton("§a{$kit->getName()}\n§bQueue§f: $queue", 0, 'textures/items/paper.png', $kit->getName());
        }
        $player->sendForm($form);
    }

    /**
     * @param string $kit
     * @return int
     */
    private function getQueue(string $kit): int
    {
        return count(array_filter(Server::getInstance()->getOnlinePlayers(), function (Player $p) use ($kit) {
            $session = PracticeCore::getSessionManager()->getSession($p);
            $Qkit = $session->DuelKit;
            return ($Qkit instanceof Kit) && !$session->isDueling && $Qkit->getName() === $kit;
        }));
    }

    /**
     * @param Player $player
     * @return void
     */
    public function cosmeticForm(Player $player): void
    {
        $session = PracticeCore::getSessionManager()->getSession($player);
        $form = new SimpleForm(function (Player $player, int $data = null) {
            if ($data !== null) {
                switch ($data) {
                    case 0:
                        $this->getArtifactForm($player);
                        break;
                    case 1:
                        $this->getCapeForm($player);
                        break;
                    case 2:
                        $this->getArtifactShopForm($player);
                        break;
                    case 3:
                        $this->getCustomTagForm($player);
                        break;
                }
            }
        });
        $form->setTitle(PracticeConfig::Server_Name . '§cCosmetics');
        $form->setContent('§bYour Coins: §f' . $session->coins);
        $form->addButton("§aArtifact\n§r§8Tap to continue");
        $form->addButton("§aCape\n§r§8Tap to continue");
        $form->addButton("§aArtifact Shop\n§r§8Tap to continue");
        $form->addButton("§aCustom Tag\n§r§8Tap to continue");
        $player->sendForm($form);
    }

    /**
     * @param Player $player
     * @return void
     */
    private function getArtifactForm(Player $player): void
    {
        $session = PracticeCore::getSessionManager()->getSession($player);
        $form = new SimpleForm(function (Player $event, $data = null) use ($session) {
            if ($data !== null) {
                if ($data === 'None') {
                    return;
                }
                $cosmetic = PracticeCore::getCosmeticHandler();
                if (($key = array_search($data, $cosmetic->cosmeticAvailable, true)) !== false) {
                    if (str_contains($data, 'SP-')) {
                        $session->artifact = '';
                        $cosmetic->setCostume($event, $cosmetic->cosmeticAvailable[$key]);
                    } else {
                        $session->artifact = $cosmetic->cosmeticAvailable[$key];
                        $cosmetic->setSkin($event, $cosmetic->cosmeticAvailable[$key]);
                    }
                    $event->sendMessage(PracticeCore::getPrefixCore() . 'Change Artifact to' . " {$cosmetic->cosmeticAvailable[$key]}.");
                }
            }
        });
        $form->setTitle(PracticeConfig::Server_Name . '§cArtifact');
        $validStuffs = ($player->hasPermission('practice.cosmetic.all')) ? PracticeCore::getCosmeticHandler()->cosmeticAvailable : $session->purchasedArtifacts;
        if (count($validStuffs) <= 1) {
            $form->addButton('None', -1, '', 'None');
            $player->sendForm($form);
        }
        foreach ($validStuffs as $stuff) {
            if ($stuff === 'None') {
                continue;
            }
            $form->addButton('§a' . $stuff, -1, '', $stuff);
        }
        $player->sendForm($form);
    }

    /**
     * @param Player $player
     * @return void
     */
    private function getCapeForm(Player $player): void
    {
        $session = PracticeCore::getSessionManager()->getSession($player);
        $form = new SimpleForm(function (Player $player, $data = null) use ($session) {
            if ($data !== null) {
                switch ($data) {
                    case 0:
                        $oldSkin = $player->getSkin();
                        $setCape = new Skin($oldSkin->getSkinId(), $oldSkin->getSkinData(), '', $oldSkin->getGeometryName(), $oldSkin->getGeometryData());
                        $player->setSkin($setCape);
                        $player->sendSkin();
                        $session->cape = '';
                        $player->sendMessage(PracticeCore::getPrefixCore() . '§aCape Removed!');
                        break;
                    case 1:
                        $this->getCapeListForm($player);
                        break;
                }
            }
        });
        $form->setTitle(PracticeConfig::Server_Name . '§cCapes');
        $form->addButton('§aRemove your Cape');
        $form->addButton('§aChoose a Cape');
        $player->sendForm($form);
    }

    /**
     * @param Player $player
     * @return void
     */
    private function getCapeListForm(Player $player): void
    {
        $session = PracticeCore::getSessionManager()->getSession($player);
        $form = new SimpleForm(function (Player $player, $data = null) use ($session) {
            if ($data !== null) {
                if (!file_exists(PracticeCore::getInstance()->getDataFolder() . 'cosmetic/capes/' . $data . '.png')) {
                    $player->sendMessage(PracticeCore::getPrefixCore() . '§cCape not found!');
                    return;
                }
                $session->cape = $data;
                $msg = PracticeCore::getPrefixCore() . '§aCape set to {name}!';
                $msg = str_replace('{name}', $data, $msg);
                $player->sendMessage($msg);
                PracticeCore::getCosmeticHandler()->setSkin($player, $session->artifact);
            }
        });
        $form->setTitle(PracticeConfig::Server_Name . '§cCapes');
        foreach (PracticeCore::getCosmeticHandler()->getCapes() as $capes) {
            $form->addButton("§a$capes", -1, '', $capes);
        }
        $player->sendForm($form);
    }

    /**
     * @param Player $player
     * @return void
     */
    private function getArtifactShopForm(Player $player): void
    {
        $session = PracticeCore::getSessionManager()->getSession($player);
        $box_item = [
            'MiniAngelWing' => 1000,
            'AngelWing' => 1000,
            'EnderWing' => 3000,
            'DevilWing' => 2000,
            'PhantomWing' => 3000,
            'Halo' => 500,
            'Crown' => 1000,
            'BackCap' => 2500,
            'Viking' => 3000,
            'ThunderCloud' => 3000,
            'Questionmark' => 1000,
            'Santa' => 1000,
            'Necktie' => 3000,
            'Backpack' => 2000,
            'Headphones' => 3000,
            'HeadphoneNote' => 1000,
            'BlazeRod' => 1000,
            'Bubble' => 1000,
            'Katana' => 3000,
            'Sickle' => 2000,
            'SWAT Shield' => 2000
        ];
        $form = new SimpleForm(function (Player $player, $data = null) use ($session, $box_item) {
            if ($data !== null) {
                if (in_array($data, $session->purchasedArtifacts)) {
                    $player->sendMessage(PracticeCore::getPrefixCore() . '§cYou already purchased this artifact!');
                    return;
                }
                if ($session->coins < $box_item[$data]) {
                    $player->sendMessage(PracticeCore::getPrefixCore() . '§cYou do not have enough coins to purchase this artifact!');
                    return;
                }
                $session->coins = ($session->coins - $box_item[$data]);
                $session->purchasedArtifacts[] = $data;
                $player->sendMessage(PracticeCore::getPrefixCore() . '§aYou have purchased ' . $data . ' for ' . $box_item[$data] . ' coins!');
            }
        });
        $form->setTitle(PracticeConfig::Server_Name . '§cArtifact Shop');
        foreach ($box_item as $key => $value) {
            if (in_array($key, $session->purchasedArtifacts)) {
                continue;
            }
            $form->addButton("§a$key §7- §a$value Coins", -1, '', $key);
        }
        $player->sendForm($form);
    }

    /**
     * @param Player $player
     * @return void
     */
    public function getCustomTagForm(Player $player): void
    {
        $session = PracticeCore::getSessionManager()->getSession($player);
        $form = new CustomForm(function (Player $player, $data = null) use ($session) {
            if ($data !== null) {
                $tag = $data['tag'];
                if (strlen($tag) < 1) {
                    $player->sendMessage(PracticeCore::getPrefixCore() . '§cCustom Tag cannot be empty!');
                    return;
                }
                if (strlen($tag) > 8) {
                    $player->sendMessage(PracticeCore::getPrefixCore() . '§cCustom Tag cannot be longer than 8 characters!');
                    return;
                }
                if ($session->coins < 1500) {
                    $player->sendMessage(PracticeCore::getPrefixCore() . '§cYou do not have enough coins to purchase this custom tag!');
                    return;
                }
                $session->coins = ($session->coins - 1000);
                $session->setCustomTag($tag);
                $player->sendMessage(PracticeCore::getPrefixCore() . '§aCustom Tag set to ' . $tag . '!');
            }
        });
        $form->setTitle(PracticeConfig::Server_Name . '§cCustom Tag');
        $form->addLabel('§aCustom Tag costs 1500 coins!');
        $form->addLabel('§aCustom Tag cannot be longer than 8 characters!');
        $form->addInput('CustomTag', 'Custom Tag', $session->getCustomTag(), 'tag');
        $player->sendForm($form);
    }
}
