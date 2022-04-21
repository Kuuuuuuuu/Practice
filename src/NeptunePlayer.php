<?php

declare(strict_types=1);

namespace Kohaku;

use Exception;
use JsonException;
use Kohaku\Utils\Kits\KitManager;
use Kohaku\Utils\PartyFactory;
use Kohaku\Utils\PartyManager;
use pocketmine\{entity\Skin, player\GameMode, player\Player, Server};
use pocketmine\event\entity\{EntityDamageByEntityEvent, EntityDamageEvent};
use Throwable;

class NeptunePlayer extends Player
{
    public int $BoxingPoint = 0;
    public string $cape = '';
    public string $artifact = '';
    public string $PlayerOS = 'Unknown';
    public string $PlayerControl = 'Unknown';
    public string $PlayerDevice = 'Unknown';
    public string $ToolboxStatus = 'Normal';
    public string $lastDamagePlayer = 'Unknown';
    public int|float $CombatTime = 0;
    public array $points = [];
    public ?string $EditKit = null;
    private int $tick = 0;
    private ?PartyFactory $party;
    private ?KitManager $duelKit = null;
    private bool $isDueling = false;
    private bool $inQueue = false;
    private bool $SkillCooldown = false;
    private bool $Combat = false;
    private ?string $Opponent = null;
    private ?string $partyrank = null;
    private array $savekitcache = [];
    private array $validstuffs = [];

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
                    } elseif (Loader::getKnockbackManager()->getAttackspeed($this->getWorld()->getFolderName()) !== null) {
                        $attackSpeed = Loader::getKnockbackManager()->getAttackspeed($this->getWorld()->getFolderName());
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
                $yKb = 0.37;
                $xzKB = 0.35;
            } elseif (Loader::getKnockbackManager()->getKnockback($this->getWorld()->getFolderName()) !== null) {
                $xzKB = Loader::getKnockbackManager()->getKnockback($this->getWorld()->getFolderName())['hkb'];
                $yKb = Loader::getKnockbackManager()->getKnockback($this->getWorld()->getFolderName())['ykb'];
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
        Loader::getInstance()->ArtifactData->set($this->getName(), $stuff);
        Loader::getInstance()->ArtifactData->save();
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
        $key = in_array($stuff, $this->validstuffs);
        if ($key === false) {
            $this->validstuffs[] = $stuff;
        }
    }

    public function getAllArtifact()
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

    public function update()
    {
        $this->tick++;
        if ($this->tick % 20 === 0) {
            $this->updateTag();
            if ($this->isCombat()) {
                $percent = floatval($this->CombatTime / 10);
                $this->getXpManager()->setXpProgress($percent);
                $this->CombatTime -= 1;
                if ($this->CombatTime <= 0) {
                    $this->setCombat(false);
                    $this->getXpManager()->setXpProgress(0.0);
                    $this->sendMessage(Loader::getInstance()->MessageData['StopCombat']);
                    $this->BoxingPoint = 0;
                    $this->setOpponent(null);
                    $this->setUnPVPTag();
                }
            }
        }
        if ($this->tick % 60 === 0) {
            $this->updateScoreboard();
            $this->updateNametag();
        }
        // TODO: Change this to ClickHandler
        $this->sendTip('§dCPS: §f' . Loader::getClickHandler()->getClicks($this));
    }

    private function updateTag()
    {
        if ($this->isCombat() or $this->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::getArenaFactory()->getSumoDArena()) or $this->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::getArenaFactory()->getKitPVPArena()) or $this->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::getArenaFactory()->getOITCArena()) or $this->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::getArenaFactory()->getKnockbackArena()) or $this->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::getArenaFactory()->getBuildArena())) {
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
        $this->Combat = $bool;
    }

    private function setPVPTag()
    {
        $ping = $this->getNetworkSession()->getPing();
        $nowcps = Loader::getClickHandler()->getClicks($this);
        $tagpvp = '§d' . $ping . '§fms §f| §d' . $nowcps . ' §fCPS';
        $this->setScoreTag($tagpvp);
    }

    private function setUnPVPTag()
    {
        $untagpvp = '§d' . $this->PlayerOS . ' §f| §d' . $this->PlayerControl . ' §f| §d' . $this->ToolboxStatus;
        $this->setScoreTag($untagpvp);
    }

    private function updateScoreboard()
    {
        if ($this->getWorld() === Server::getInstance()->getWorldManager()->getDefaultWorld()) {
            Loader::getInstance()->getScoreboardManager()->sb($this);
        } elseif ($this->getWorld() !== Server::getInstance()->getWorldManager()->getDefaultWorld() and $this->getWorld() !== Server::getInstance()->getWorldManager()->getWorldByName(Loader::getArenaFactory()->getBoxingArena())) {
            Loader::getInstance()->getScoreboardManager()->sb2($this);
        } elseif ($this->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::getArenaFactory()->getBoxingArena())) {
            Loader::getInstance()->getScoreboardManager()->Boxing($this);
        }
    }

    private function updateNametag()
    {
        $name = $this->getName();
        if (Loader::getInstance()->getArenaUtils()->getData($name)->getTag() !== null and Loader::getInstance()->getArenaUtils()->getData($name)->getTag() !== '') {
            $nametag = Loader::getInstance()->getArenaUtils()->getData($name)->getRank() . '§a ' . $this->getDisplayName() . ' §f[' . Loader::getInstance()->getArenaUtils()->getData($name)->getTag() . '§f]';
        } else {
            $nametag = Loader::getInstance()->getArenaUtils()->getData($name)->getRank() . '§a ' . $this->getDisplayName();
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
    public function onJoin()
    {
        $this->getEffects()->clear();
        $this->getInventory()->clearAll();
        $this->getArmorInventory()->clearAll();
        Loader::getInstance()->getArenaUtils()->GiveItem($this);
        $this->LoadData();
        $this->sendMessage(Loader::getPrefixCore() . '§eLoading Player Data');
    }

    /**
     * @throws JsonException
     */
    private function LoadData()
    {
        $this->cape = Loader::getInstance()->CapeData->get($this->getName()) ? Loader::getInstance()->CapeData->get($this->getName()) : '';
        $this->artifact = Loader::getInstance()->ArtifactData->get($this->getName()) ? Loader::getInstance()->ArtifactData->get($this->getName()) : '';
        $this->setCosmetic();
    }

    /**
     * @throws JsonException
     */
    public function setCosmetic(): void
    {
        if (file_exists(Loader::getInstance()->getDataFolder() . 'cosmetic/artifact/' . Loader::getInstance()->ArtifactData->get($this->getName()) . '.png')) {
            if ($this->getStuff() !== '' or $this->getStuff() !== null) {
                Loader::getCosmeticHandler()->setSkin($this, $this->getStuff());
            }
        } else {
            Loader::getInstance()->ArtifactData->remove($this->getName());
            Loader::getInstance()->ArtifactData->save();
        }
        if (file_exists(Loader::getInstance()->getDataFolder() . 'cosmetic/capes/' . Loader::getInstance()->CapeData->get($this->getName()) . '.png')) {
            $oldSkin = $this->getSkin();
            $capeData = Loader::getCosmeticHandler()->createCape(Loader::getInstance()->CapeData->get($this->getName()));
            $setCape = new Skin($oldSkin->getSkinId(), $oldSkin->getSkinData(), $capeData, $oldSkin->getGeometryName(), $oldSkin->getGeometryData());
            $this->setSkin($setCape);
            $this->sendSkin();
        } else {
            Loader::getInstance()->CapeData->remove($this->getName());
            Loader::getInstance()->CapeData->save();
        }
    }

    public function getStuff(): string
    {
        return $this->artifact;
    }

    public function checkQueue(): void
    {
        $this->sendMessage(Loader::getPrefixCore() . 'Entering queue...');
        foreach ($this->getServer()->getOnlinePlayers() as $player) {
            if ($player instanceof NeptunePlayer and $player->getName() !== $this->getName()) {
                if ($player->isInQueue() and $this->getDuelKit() === $player->getDuelKit()) {
                    Loader::getInstance()->getDuelManager()->createMatch($this, $player, $this->getDuelKit());
                    $this->sendMessage(Loader::getPrefixCore() . 'Found a match against §c' . $player->getName());
                    $player->sendMessage(Loader::getPrefixCore() . 'Found a match against §c' . $this->getName());
                    foreach ([$player, $this] as $p) {
                        $p->setInQueue(false);
                    }
                    return;
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

    public function queueBotDuel(): void
    {
        Loader::getInstance()->getBotDuelManager()->createMatch($this);
        $this->setInQueue(false);
    }

    public function onQuit()
    {
        Loader::getClickHandler()->removePlayerClickData($this);
        $party = $this->getParty();
        if (!is_null($party)) {
            if ($party->isLeader($this)) {
                $party->disband();
            } else {
                $party->removeMember($this);
            }
        }
        if ($this->isDueling() or $this->isCombat()) {
            $this->kill();
        }
        $this->setGamemode(GameMode::SURVIVAL());
    }

    public function getParty(): ?PartyFactory
    {
        $result = null;
        if ($this->isInParty()) {
            $result = $this->party;
        }
        return $result;
    }

    public function setParty(?PartyFactory $party)
    {
        $this->party = $party;
    }

    public function isInParty(): bool
    {
        return PartyManager::getPartyFromPlayer($this) !== null;
    }

    public function setCurrentKit(?KitManager $kit): void
    {
        $this->duelKit = $kit;
    }

    /**
     * @throws JsonException
     */
    public function saveKit()
    {
        $name = $this->getName();
        try {
            // TODO: implement
            foreach ($this->getInventory()->getContents() as $slot => $item) {
                $this->savekitcache[$slot] = $item->jsonSerialize();
            }
            Loader::getInstance()->KitData->set($name, $this->savekitcache);
        } catch (Exception $exception) {
            Loader::getInstance()->getLogger()->error($exception->getMessage());
            $this->kill();
            $this->setImmobile(false);
            $this->sendMessage(Loader::getPrefixCore() . '§cAn error occurred while saving your kit.');
            $this->EditKit = null;
            return;
        }
        Loader::getInstance()->KitData->save();
        $this->EditKit = null;
        $this->sendMessage(Loader::getPrefixCore() . '§aYou have successfully saved your kit!');
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

    public function __destruct()
    {
        parent::__destruct();
        // TODO: Make this to remove player data when onQuit() is called
    }

    public function getPartyRank(): ?string
    {
        return $this->partyrank;
    }

    public function setPartyRank(?string $rank)
    {
        $this->partyrank = $rank;
    }
}
