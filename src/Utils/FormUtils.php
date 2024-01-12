<?php

declare(strict_types=1);

namespace Nayuki\Utils;

use Nayuki\Game\Duel\DuelStatus;
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
use pocketmine\utils\TextFormat;

final class FormUtils
{
    /**
     * @param Player $player
     * @return void
     */
    public function ArenaForm(Player $player): void
    {
        $form = new SimpleForm(function (Player $player, string $data = null) {
            if ($data === null) {
                return;
            }
            PracticeCore::getArenaManager()->joinArenas($player, $data);
        });

        $form->setTitle(PracticeConfig::Server_Name . TextFormat::RED . 'Menu');

        $bannedKits = ['boxing', 'sumo']; // TODO: better way todo this lol
        $kits = KitRegistry::getKits();

        foreach ($kits as $kit) {
            /** @var Kit $kit */
            if (in_array(strtolower($kit->getName()), $bannedKits, true)) {
                continue;
            }

            $playerCount = PracticeCore::getArenaFactory()->getPlayers(PracticeCore::getArenaFactory()->getArenas($kit->getName()));
            $form->addButton(TextFormat::GREEN . "{$kit->getName()}\n§ePlayers: §a$playerCount", 0, 'textures/items/paper.png', $kit->getName());
        }

        $player->sendForm($form);
    }


    /**
     * @param Player $player
     * @return void
     */
    public function SettingsForm(Player $player): void
    {
        $form = new CustomForm(function (Player $player, array $data = null) {
            if ($data === null) {
                return;
            }
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
        });
        $form->setTitle(PracticeConfig::Server_Name . TextFormat::RED . 'Menu');
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
            if ($data === null) {
                return;
            }
            $session = PracticeCore::getSessionManager()->getSession($player);
            $session->DuelKit = KitRegistry::fromString($data);
            $session->isQueueing = true;
            $player->getInventory()->clearAll();
            $player->getInventory()->setItem(8, VanillaItems::DYE()->setColor(DyeColor::RED)->setCustomName('§r§cLeave Queue')->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 10)));
            PracticeCore::getUtils()->checkQueue($player);
        });
        $form->setTitle(PracticeConfig::Server_Name . TextFormat::RED . 'Duel');
        $banKits = array_flip([
            'resistance',
        ]);
        $kits = KitRegistry::getKits();
        array_walk($kits, function ($kit) use ($banKits, $form) {
            /** @var Kit $kit */
            if (isset($banKits[strtolower($kit->getName())])) {
                return;
            }
            $queue = $this->getQueue($kit->getName());
            $form->addButton(TextFormat::GREEN . "{$kit->getName()}\n§eQueue§a: $queue", 0, 'textures/items/paper.png', $kit->getName());
        });
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
            if ($data === null) {
                return;
            }
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
        });
        $form->setTitle(PracticeConfig::Server_Name . TextFormat::RED . 'Cosmetics');
        $form->setContent(TextFormat::MINECOIN_GOLD . '§eYour Coins: §f' . $session->coins);
        $form->addButton(TextFormat::GREEN . "Artifact\n§r§8Tap to continue");
        $form->addButton(TextFormat::GREEN . "Cape\n§r§8Tap to continue");
        $form->addButton(TextFormat::GREEN . "Artifact Shop\n§r§8Tap to continue");
        $form->addButton(TextFormat::GREEN . "Custom Tag\n§r§8Tap to continue");
        $player->sendForm($form);
    }

    /**
     * @param Player $player
     * @return void
     */
    private function getArtifactForm(Player $player): void
    {
        $session = PracticeCore::getSessionManager()->getSession($player);
        $form = new SimpleForm(function (Player $player, $data = null) use ($session) {
            if ($data === null || $data === 'None') {
                return;
            }
            $cosmetic = PracticeCore::getCosmeticHandler();
            $key = array_search($data, $cosmetic->cosmeticAvailable, true);
            if ($key !== false) {
                $artifact = $cosmetic->cosmeticAvailable[$key];
                $session->artifact = str_contains($data, 'SP-') ? '' : $artifact;
                if (str_contains($data, 'SP-')) {
                    $cosmetic->setCostume($player, $artifact);
                } else {
                    $cosmetic->setSkin($player, $artifact);
                }
                $player->sendMessage(sprintf('%sChange Artifact to %s.', PracticeCore::getPrefixCore(), $artifact));
            }
        });
        $form->setTitle(PracticeConfig::Server_Name . TextFormat::RED . 'Artifact');
        $validStuffs = $player->hasPermission('practice.cosmetic.all') ? PracticeCore::getCosmeticHandler()->cosmeticAvailable : $session->purchasedArtifacts;
        foreach ($validStuffs as $stuff) {
            if ($stuff === 'None') {
                $form->addButton('None', -1, '', 'None');
                $player->sendForm($form);
                break;
            }
            $form->addButton(sprintf('§a%s', $stuff), -1, '', $stuff);
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
            if ($data === null) {
                return;
            }
            switch ($data) {
                case 0:
                    $oldSkin = $player->getSkin();
                    $setCape = new Skin($oldSkin->getSkinId(), $oldSkin->getSkinData(), '', $oldSkin->getGeometryName(), $oldSkin->getGeometryData());
                    $player->setSkin($setCape);
                    $player->sendSkin();
                    $session->cape = '';
                    $player->sendMessage(PracticeCore::getPrefixCore() . TextFormat::GREEN . 'Cape Removed!');
                    break;
                case 1:
                    $this->getCapeListForm($player);
                    break;
            }
        });
        $form->setTitle(PracticeConfig::Server_Name . TextFormat::RED . 'Capes');
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
            if ($data === null) {
                return;
            }
            $capePath = PracticeCore::getInstance()->getDataFolder() . 'cosmetic/capes/';
            if (!file_exists($capePath . $data . '.png')) {
                $player->sendMessage(PracticeCore::getPrefixCore() . TextFormat::RED . 'Cape not found!');
                return;
            }
            $session->cape = $data;
            $msg = sprintf('%s§aChange Cape to %s!', PracticeCore::getPrefixCore(), $data);
            $player->sendMessage($msg);
            PracticeCore::getCosmeticHandler()->setSkin($player, $session->artifact);
        });
        $form->setTitle(PracticeConfig::Server_Name . '§cCapes');
        $capes = PracticeCore::getCosmeticHandler()->getCapes();
        foreach ($capes as $cape) {
            $form->addButton("§a$cape", -1, '', $cape);
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
        // https://github.com/ZeqaNetwork/Mineceit/blob/d417c156bf02084d20ff0a064f62b2b99f2d876f/src/mineceit/game/FormUtil.php#L4775
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
            if ($data === null) {
                return;
            }
            $prefix = PracticeCore::getPrefixCore();
            if (in_array($data, $session->purchasedArtifacts, true)) {
                $player->sendMessage($prefix . '§cYou already purchased this artifact!');
                return;
            }
            $cost = $box_item[$data];
            if ($session->coins < $cost) {
                $player->sendMessage($prefix . '§cYou do not have enough coins to purchase this artifact!');
                return;
            }
            $session->coins -= $cost;
            $session->purchasedArtifacts[] = $data;
            $player->sendMessage(sprintf('%s§aYou have purchased %s for %s coins!', $prefix, $data, $cost));
        });
        $form->setTitle(PracticeConfig::Server_Name . '§cArtifact Shop');
        foreach ($box_item as $key => $value) {
            if (in_array($key, $session->purchasedArtifacts, true)) {
                continue;
            }
            $form->addButton(sprintf('§a%s §7- §a%s Coins', $key, $value), -1, '', $key);
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
            if ($data === null) {
                return;
            }
            $tag = $data['tag'];
            if (strlen($tag) < 1) {
                $player->sendMessage(PracticeCore::getPrefixCore() . TextFormat::RED . 'Custom Tag cannot be empty!');
                return;
            }

            if (strlen($tag) > 8) {
                $player->sendMessage(PracticeCore::getPrefixCore() . TextFormat::RED . 'Custom Tag cannot be longer than 8 characters!');
                return;
            }

            if ($session->coins < 1500) {
                $player->sendMessage(PracticeCore::getPrefixCore() . TextFormat::RED . 'You do not have enough coins to purchase this custom tag!');
                return;
            }

            $session->coins = ($session->coins - 1000);
            $session->setCustomTag($tag);
            $player->sendMessage(PracticeCore::getPrefixCore() . 'Change Custom-Tag to ' . $tag . '!');
        });
        $form->setTitle(PracticeConfig::Server_Name . '§cCustom Tag');
        $form->addLabel('§aCustom Tag costs 1500 coins!');
        $form->addLabel('§aCustom Tag cannot be longer than 8 characters!');
        $form->addInput('CustomTag', 'Custom Tag', $session->getCustomTag(), 'tag');
        $player->sendForm($form);
    }

    /**
     * @param Player $player
     * @return void
     */
    public function spectateForm(Player $player): void
    {
        $arenas = PracticeCore::getDuelManager()->getArenas();
        $form = new SimpleForm(function (Player $player, ?string $data) {
            if ($data === null || $data === 'none') {
                return;
            }

            $arena = PracticeCore::getDuelManager()->getArenaByName($data);
            if ($arena === null) {
                $player->sendMessage(PracticeCore::getPrefixCore() . TextFormat::RED . 'Arena not found!');
                return;
            }

            if ($arena->status === DuelStatus::ENDING) {
                $player->sendMessage(PracticeCore::getPrefixCore() . TextFormat::RED . 'Arena is ending!');
                return;
            }

            $arena->addSpectator($player);
        });
        $form->setTitle(PracticeConfig::Server_Name . '§cSpectate');

        if (empty($arenas)) {
            $form->setContent('§cNo arenas found!');
            $form->addButton(TextFormat::RED . 'none', -1, '', 'none');
            $player->sendForm($form);
            return;
        }

        foreach ($arenas as $arena) {
            if ($arena->status === DuelStatus::ENDING) {
                continue;
            }
            $player1name = $arena->player1->getName();
            $player2name = $arena->player2?->getName() ?? 'PracticeBot';
            $form->addButton(TextFormat::GREEN . $player1name . ' §7vs ' . TextFormat::RED . $player2name, -1, '', $arena->name);
        }
        $player->sendForm($form);
    }
}
