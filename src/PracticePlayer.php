<?php

declare(strict_types=1);

namespace Kuu;

use Exception;
use JsonException;
use Kuu\Utils\Kits\KitManager;
use pocketmine\{entity\Skin, player\GameMode, player\Player, Server};
use pocketmine\event\entity\{EntityDamageByEntityEvent, EntityDamageEvent};
use Throwable;

class PracticePlayer extends Player
{
    public int $BoxingPoint = 0;
    public string $PlayerOS = 'Unknown';
    public string $PlayerControl = 'Unknown';
    public string $PlayerDevice = 'Unknown';
    public string $ToolboxStatus = 'Normal';
    public string $lastDamagePlayer = 'Unknown';
    private int $CombatTime = 0;
    private string $cape = '';
    private string $artifact = '';
    private ?string $EditKit = null;
    private int $sec = 0;
    private ?KitManager $duelKit = null;
    private bool $isDueling = false;
    private bool $inQueue = false;
    private bool $SkillCooldown = false;
    private bool $Combat = false;
    private ?string $Opponent = null;
    private array $savekitcache = [];
    private array $validstuffs = [];
    private int $enderpearlcooldown = 0;
    private bool $isEnderpearlCooldown = false;

    public function attack(EntityDamageEvent $source): void
    {
        $attackSpeed = 10;
        parent::attack($source);
        if ($source->isCancelled()) {
            return;
        }
        if ($source instanceof EntityDamageByEntityEvent) {
            $damager = $source->getDamager();
            if ($damager instanceof Player) {
                try {
                    if ($this->isDueling()) {
                        $attackSpeed = 7.5;
                    } elseif (PracticeCore::getKnockbackManager()->getAttackspeed($this->getWorld()->getFolderName()) !== null) {
                        $attackSpeed = PracticeCore::getKnockbackManager()->getAttackspeed($this->getWorld()->getFolderName()) ?? 10;
                    }
                } catch (Throwable) {
                }
            }
        }
        $this->attackTime = $attackSpeed;
    }

    public function isDueling(): bool
    {
        return $this->isDueling;
    }

    public function knockBack(float $x, float $z, float $force = 0.4, ?float $verticalLimit = 0.4): void
    {
        $xzKB = 0.4;
        $yKb = 0.4;
        try {
            if ($this->isDueling()) {
                $yKb = 0.32;
                $xzKB = 0.34;
            } elseif (PracticeCore::getKnockbackManager()->getKnockback($this->getWorld()->getFolderName()) !== null) {
                $xzKB = PracticeCore::getKnockbackManager()->getKnockback($this->getWorld()->getFolderName())['hkb'] ?? 0.4;
                $yKb = PracticeCore::getKnockbackManager()->getKnockback($this->getWorld()->getFolderName())['ykb'] ?? 0.4;
            }
        } catch (Throwable) {
        }
        $f = sqrt($x * $x + $z * $z);
        if ($f <= 0) {
            return;
        }
        if (mt_rand() / mt_getrandmax() > $this->knockbackResistanceAttr->getValue()) {
            $f = 1 / $f;
            $motion = clone $this->motion;
            $motion->x /= 2;
            $motion->y /= 2;
            $motion->z /= 2;
            $motion->x += $x * $f * $xzKB;
            $motion->y += $yKb;
            $motion->z += $z * $f * $xzKB;
            if ($motion->y > $yKb) {
                $motion->y = $yKb;
            }
            $this->setMotion($motion);
        }
    }

    /**
     * @throws JsonException
     */
    public function setStuff(string $stuff): void
    {
        PracticeCore::getInstance()->ArtifactData->set($this->getName(), $stuff);
        PracticeCore::getInstance()->ArtifactData->save();
    }

    public function getCape(): string
    {
        return $this->cape;
    }

    public function getValidStuffs(): array
    {
        return $this->validstuffs;
    }

    public function setValidStuffs(string $stuff): void
    {
        $key = in_array($stuff, $this->validstuffs, true);
        if ($key === false) {
            $this->validstuffs[] = $stuff;
        }
    }

    public function getAllArtifact(): void
    {
        $this->setValidStuffs('Adidas');
        $this->setValidStuffs('AngelWing');
        $this->setValidStuffs('AngelWingV2');
        $this->setValidStuffs('Antler');
        $this->setValidStuffs('Axe');
        $this->setValidStuffs('BackCap');
        $this->setValidStuffs('Backpack');
        $this->setValidStuffs('BackStabKnife');
        $this->setValidStuffs('Bald Headband');
        $this->setValidStuffs('Banana');
        $this->setValidStuffs('BlackAngleSet');
        $this->setValidStuffs('BlazeRod');
        $this->setValidStuffs('BlueWing');
        $this->setValidStuffs('Boxing');
        $this->setValidStuffs('Bubble');
        $this->setValidStuffs('Creeper');
        $this->setValidStuffs('Crown');
        $this->setValidStuffs('CrownV2');
        $this->setValidStuffs('DevilHaloWing');
        $this->setValidStuffs('DevilWing');
        $this->setValidStuffs('Dollar');
        $this->setValidStuffs('DragonWing');
        $this->setValidStuffs('EnderTail');
        $this->setValidStuffs('EnderWing');
        $this->setValidStuffs('Fox');
        $this->setValidStuffs('Glasses');
        $this->setValidStuffs('Goat');
        $this->setValidStuffs('Gudoudame');
        $this->setValidStuffs('Halo');
        $this->setValidStuffs('HeadphoneNote');
        $this->setValidStuffs('Headphones');
        $this->setValidStuffs('Kaqune');
        $this->setValidStuffs('Katana');
        $this->setValidStuffs('Koala');
        $this->setValidStuffs('LightSaber');
        $this->setValidStuffs('LouisVuitton');
        $this->setValidStuffs('MiniAngelWing');
        $this->setValidStuffs('MiniAngelWingV2');
        $this->setValidStuffs('MLG RUSH 1st');
        $this->setValidStuffs('Moustache');
        $this->setValidStuffs('Neckite');
        $this->setValidStuffs('Nike');
        $this->setValidStuffs('PhantomWing');
        $this->setValidStuffs('Questionmark');
        $this->setValidStuffs('Rabbit Costume');
        $this->setValidStuffs('Rabbit');
        $this->setValidStuffs('RedWing');
        $this->setValidStuffs('Rich Bandanna');
        $this->setValidStuffs('Santa');
        $this->setValidStuffs('Sickle');
        $this->setValidStuffs('SP-BananaMan');
        $this->setValidStuffs('Susanno');
        $this->setValidStuffs('SusanooBlue');
        $this->setValidStuffs('SusanooPurple');
        $this->setValidStuffs('SWAT Shield');
        $this->setValidStuffs('ThunderCloud');
        $this->setValidStuffs('UFO');
        $this->setValidStuffs('Viking');
        $this->setValidStuffs('Wave Bandanna');
        $this->setValidStuffs('White Heart');
        $this->setValidStuffs('Witchhat');
        $this->setValidStuffs('Wither Head');
    }

    public function getLastDamagePlayer(): string
    {
        return $this->lastDamagePlayer;
    }

    public function setLastDamagePlayer(string $name): void
    {
        $this->lastDamagePlayer = $name;
    }

    public function update(): void
    {
        $this->sec++;
        $this->updateTag();
        if ($this->isCombat()) {
            $percent = (float)($this->CombatTime / 10);
            $this->getXpManager()->setXpProgress($percent);
            $this->CombatTime--;
            if ($this->CombatTime <= 0) {
                $this->setCombat(false);
                $this->getXpManager()->setXpProgress(0.0);
                $this->sendMessage(PracticeCore::getInstance()->MessageData['StopCombat']);
                $this->BoxingPoint = 0;
                $this->setOpponent(null);
                $this->setSkillCooldown(false);
                $this->setUnPVPTag();
            }
        }
        if ($this->isEnderPearlCooldown()) {
            $this->enderpearlcooldown--;
            if ($this->enderpearlcooldown <= 0) {
                $this->setEnderPearlCooldown(false);
                $this->sendMessage(PracticeCore::getInstance()->MessageData['EnderPearlCooldownEnd']);
            }
        }
        if ($this->sec % 3 === 0) {
            PracticeCore::getPracticeUtils()->DeviceCheck($this);
            $this->updateScoreboard();
            $this->updateNametag();
        }
    }

    private function updateTag(): void
    {
        if ($this->isCombat() || $this->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(PracticeCore::getArenaFactory()->getKitPVPArena()) || $this->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(PracticeCore::getArenaFactory()->getOITCArena()) || $this->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(PracticeCore::getArenaFactory()->getKnockbackArena()) || $this->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(PracticeCore::getArenaFactory()->getBuildArena())) {
            $this->setPVPTag();
        } elseif (!$this->isCombat()) {
            $this->setUnPVPTag();
        }
    }

    public function isCombat(): bool
    {
        return $this->Combat;
    }

    public function setCombat(bool $bool): void
    {
        if (!$bool && $this->CombatTime > 0) {
            $this->CombatTime = 1;
            return;
        }
        $this->Combat = $bool;
        $this->CombatTime = 10;
    }

    private function setPVPTag(): void
    {
        $ping = $this->getNetworkSession()->getPing();
        $nowcps = PracticeCore::getClickHandler()->getClicks($this);
        $tagpvp = '§d' . $ping . '§fms §f| §d' . $nowcps . ' §fCPS';
        $this->setScoreTag($tagpvp);
    }

    private function setUnPVPTag(): void
    {
        $untagpvp = '§d' . $this->PlayerOS . ' §f| §d' . $this->PlayerControl;
        $this->setScoreTag($untagpvp);
    }

    public function isEnderPearlCooldown(): bool
    {
        return $this->isEnderpearlCooldown;
    }

    public function setEnderPearlCooldown(bool $bool): void
    {
        $this->isEnderpearlCooldown = $bool;
        if ($bool) {
            $this->sendMessage(PracticeCore::getInstance()->MessageData['EnderPearlCooldownStart']);
            $this->enderpearlcooldown = PracticeConfig::EnderPearlCooldown;
        }
    }

    private function updateScoreboard(): void
    {
        if ($this->getWorld() === Server::getInstance()->getWorldManager()->getDefaultWorld()) {
            PracticeCore::getInstance()->getScoreboardManager()->sb($this);
        } elseif ($this->getWorld() !== Server::getInstance()->getWorldManager()->getDefaultWorld() && $this->getWorld() !== Server::getInstance()->getWorldManager()->getWorldByName(PracticeCore::getArenaFactory()->getBoxingArena())) {
            PracticeCore::getInstance()->getScoreboardManager()->sb2($this);
        } elseif ($this->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(PracticeCore::getArenaFactory()->getBoxingArena())) {
            PracticeCore::getInstance()->getScoreboardManager()->Boxing($this);
        }
    }

    private function updateNametag(): void
    {
        $name = $this->getName();
        if (PracticeCore::getInstance()->getPracticeUtils()->getData($name)->getTag() !== null && PracticeCore::getInstance()->getPracticeUtils()->getData($name)->getTag() !== '') {
            $nametag = PracticeCore::getInstance()->getPracticeUtils()->getData($name)->getRank() . '§a ' . $this->getDisplayName() . ' §f[' . PracticeCore::getInstance()->getPracticeUtils()->getData($name)->getTag() . '§f]';
        } else {
            $nametag = PracticeCore::getInstance()->getPracticeUtils()->getData($name)->getRank() . '§a ' . $this->getDisplayName();
        }
        $this->setNameTag($nametag);
    }

    public function setDueling(bool $playing): void
    {
        $this->isDueling = $playing;
    }

    /**
     * @throws JsonException
     */
    public function onJoin(): void
    {
        $this->getEffects()->clear();
        $this->getInventory()->clearAll();
        $this->getArmorInventory()->clearAll();
        PracticeCore::getInstance()->getPracticeUtils()->GiveLobbyItem($this);
        $this->LoadData();
        $this->sendMessage(PracticeCore::getPrefixCore() . '§eLoading Data...');
    }

    /**
     * @throws JsonException
     */
    private function LoadData(): void
    {
        $this->cape = PracticeCore::getInstance()->CapeData->get($this->getName()) ?: '';
        $this->artifact = PracticeCore::getInstance()->ArtifactData->get($this->getName()) ?: '';
        $this->setCosmetic();
    }

    /**
     * @throws JsonException
     */
    public function setCosmetic(): void
    {
        if (file_exists(PracticeCore::getInstance()->getDataFolder() . 'cosmetic/artifact/' . PracticeCore::getInstance()->ArtifactData->get($this->getName()) . '.png')) {
            if ($this->getStuff() !== '' && $this->getStuff() !== null) {
                PracticeCore::getCosmeticHandler()->setSkin($this, $this->getStuff());
            }
        } else {
            PracticeCore::getInstance()->ArtifactData->remove($this->getName());
            PracticeCore::getInstance()->ArtifactData->save();
        }
        if (file_exists(PracticeCore::getInstance()->getDataFolder() . 'cosmetic/capes/' . PracticeCore::getInstance()->CapeData->get($this->getName()) . '.png')) {
            $oldSkin = $this->getSkin();
            $capeData = PracticeCore::getCosmeticHandler()->createCape(PracticeCore::getInstance()->CapeData->get($this->getName()));
            $setCape = new Skin($oldSkin->getSkinId(), $oldSkin->getSkinData(), $capeData, $oldSkin->getGeometryName(), $oldSkin->getGeometryData());
            $this->setSkin($setCape);
            $this->sendSkin();
        } else {
            PracticeCore::getInstance()->CapeData->remove($this->getName());
            PracticeCore::getInstance()->CapeData->save();
        }
    }

    public function getStuff(): string
    {
        return $this->artifact;
    }

    /**
     * @throws Exception
     */
    public function checkQueue(): void
    {
        $this->sendMessage(PracticeCore::getPrefixCore() . 'Entering queue...');
        foreach ($this->getServer()->getOnlinePlayers() as $player) {
            if (($player instanceof self && $player->getName() !== $this->getName()) && ($this->isInQueue() && $player->isInQueue()) && $this->getDuelKit() === $player->getDuelKit()) {
                PracticeCore::getInstance()->getDuelManager()->createMatch($this, $player, $this->getDuelKit());
                $this->sendMessage(PracticeCore::getPrefixCore() . 'Found a match against §c' . $player->getName());
                $player->sendMessage(PracticeCore::getPrefixCore() . 'Found a match against §c' . $this->getName());
                foreach ([$player, $this] as $p) {
                    $p->setInQueue(false);
                }
            }
        }
    }

    public function isInQueue(): bool
    {
        return $this->inQueue;
    }

    public function setInQueue(bool $inQueue): void
    {
        $this->inQueue = $inQueue;
    }

    public function getDuelKit(): ?KitManager
    {
        return $this->duelKit ?? null;
    }

    /**
     * @throws Exception
     */
    public function queueBotDuel(string $mode): void
    {
        PracticeCore::getInstance()->getDuelManager()->createBotMatch($this, $this->getDuelKit(), $mode);
        $this->setInQueue(false);
    }

    public function onQuit(): void
    {
        PracticeCore::getClickHandler()->removePlayerClickData($this);
        if ($this->isDueling() || $this->isCombat()) {
            $this->kill();
        }
        $this->setGamemode(GameMode::SURVIVAL());
    }

    public function setCurrentKit(?KitManager $kit): void
    {
        $this->duelKit = $kit;
    }

    /**
     * @throws JsonException
     */
    public function saveKit(): void
    {
        $name = $this->getName();
        try {
            foreach ($this->getInventory()->getContents() as $slot => $item) {
                $this->savekitcache[$slot] = $item->jsonSerialize();
            }
            PracticeCore::getInstance()->KitData->set($name, $this->savekitcache);
        } catch (Exception) {
            $this->kill();
            $this->setImmobile(false);
            $this->sendMessage(PracticeCore::getPrefixCore() . '§cAn error occurred while saving your kit.');
            $this->EditKit = null;
            return;
        }
        PracticeCore::getInstance()->KitData->save();
        $this->EditKit = null;
        $this->sendMessage(PracticeCore::getPrefixCore() . '§aYou have successfully saved your kit!');
        $this->kill();
        $this->setImmobile(false);
    }

    public function isSkillCooldown(): bool
    {
        return $this->SkillCooldown;
    }

    public function setSkillCooldown(bool $bool): void
    {
        $this->SkillCooldown = $bool;
    }

    public function getOpponent(): ?string
    {
        return $this->Opponent;
    }

    public function setOpponent(?string $name): void
    {
        $this->Opponent = $name;
    }

    public function getEditKit(): ?string
    {
        return $this->EditKit;
    }

    public function setEditKit(?string $kit): void
    {
        $this->EditKit = $kit;
    }
}
