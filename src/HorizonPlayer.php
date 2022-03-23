<?php

declare(strict_types=1);

namespace Kohaku\Core;

use Exception;
use JsonException;
use Kohaku\Core\Utils\ArenaUtils;
use Kohaku\Core\Utils\CosmeticHandler;
use Kohaku\Core\Utils\KnockbackManager;
use pocketmine\{entity\Skin, player\Player};
use pocketmine\event\entity\{EntityDamageByEntityEvent, EntityDamageEvent};

class HorizonPlayer extends Player
{

    private float|int $xzKB = 0.4;
    private float|int $yKb = 0.4;
    private array $validstuffs = [];
    private string $lastDamagePlayer = "Unknown";

    public function attack(EntityDamageEvent $source): void
    {
        parent::attack($source);
        if ($source->isCancelled()) {
            return;
        }
        $attackSpeed = $source->getAttackCooldown();
        if ($attackSpeed < 0) $attackSpeed = 0;
        if ($source instanceof EntityDamageByEntityEvent) {
            $damager = $source->getDamager();
            if ($damager instanceof Player) {
                try {
                    if (KnockbackManager::getInstance()->getAttackspeed($this->getWorld()->getFolderName()) !== null) {
                        $attackSpeed = KnockbackManager::getInstance()->getAttackspeed($this->getWorld()->getFolderName());
                    } else {
                        $attackSpeed = 10;
                    }
                } catch (Exception $e) {
                    ArenaUtils::getLogger((string)$e);
                }
            }
        }
        $this->attackTime = $attackSpeed;
    }

    public function knockBack(float $x, float $z, float $force = 0.4, ?float $verticalLimit = 0.4): void
    {
        try {
            if (KnockbackManager::getInstance()->getKnockback($this->getWorld()->getFolderName()) !== null) {
                $this->xzKB = KnockbackManager::getInstance()->getKnockback($this->getWorld()->getFolderName())["hkb"];
                $this->yKb = KnockbackManager::getInstance()->getKnockback($this->getWorld()->getFolderName())["ykb"];
            } else {
                $this->xzKB = 0.4;
                $this->yKb = 0.4;
            }
        } catch (Exception $e) {
            ArenaUtils::getLogger((string)$e);
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
            $motion->x += $x * $f * $this->xzKB;
            $motion->y += $this->yKb;
            $motion->z += $z * $f * $this->xzKB;
            if ($motion->y > $this->yKb) {
                $motion->y = $this->yKb;
            }
            $this->setMotion($motion);
        }
    }

    /**
     * @throws JsonException
     */
    public function LoadCape()
    {
        if (file_exists(Loader::getInstance()->getDataFolder() . "cosmetic/capes/" . Loader::getInstance()->CapeData->get($this->getName()) . ".png")) {
            $oldSkin = $this->getSkin();
            $capeData = CosmeticHandler::getInstance()->createCape(Loader::getInstance()->CapeData->get($this->getName()));
            $setCape = new Skin($oldSkin->getSkinId(), $oldSkin->getSkinData(), $capeData, $oldSkin->getGeometryName(), $oldSkin->getGeometryData());
            $this->setSkin($setCape);
            $this->sendSkin();
        } else {
            Loader::getInstance()->CapeData->remove($this->getName());
            Loader::getInstance()->CapeData->save();
        }
    }

    public function setCosmetic(): void
    {
        if (file_exists(Loader::getInstance()->getDataFolder() . "cosmetic/artifact/" . Loader::getInstance()->ArtifactData->get($this->getName()) . ".png")) {
            if ($this->getStuff() !== "" or $this->getStuff() !== null) {
                CosmeticHandler::getInstance()->setSkin($this, $this->getStuff());
            }
        }
    }

    public function getStuff(): string
    {
        return Loader::getInstance()->ArtifactData->get($this->getName());
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
        return Loader::getInstance()->CapeData->get($this->getName());
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

    public function getAllCape()
    {
        $this->setValidStuffs("Adidas");
        $this->setValidStuffs("AngelWing");
        $this->setValidStuffs("AngelWingV2");
        $this->setValidStuffs("Antler");
        $this->setValidStuffs("Axe");
        $this->setValidStuffs("BackCap");
        $this->setValidStuffs("Backpack");
        $this->setValidStuffs("BackStabKnife");
        $this->setValidStuffs("Bald Headband");
        $this->setValidStuffs("Banana");
        $this->setValidStuffs("BlackAngleSet");
        $this->setValidStuffs("BlazeRod");
        $this->setValidStuffs("BlueWing");
        $this->setValidStuffs("Boxing");
        $this->setValidStuffs("Bubble");
        $this->setValidStuffs("Creeper");
        $this->setValidStuffs("Crown");
        $this->setValidStuffs("CrownV2");
        $this->setValidStuffs("DevilHaloWing");
        $this->setValidStuffs("DevilWing");
        $this->setValidStuffs("Dollar");
        $this->setValidStuffs("DragonWing");
        $this->setValidStuffs("EnderTail");
        $this->setValidStuffs("EnderWing");
        $this->setValidStuffs("Fox");
        $this->setValidStuffs("Glasses");
        $this->setValidStuffs("Goat");
        $this->setValidStuffs("Gudoudame");
        $this->setValidStuffs("Halo");
        $this->setValidStuffs("HeadphoneNote");
        $this->setValidStuffs("Headphones");
        $this->setValidStuffs("Kaqune");
        $this->setValidStuffs("Katana");
        $this->setValidStuffs("Koala");
        $this->setValidStuffs("LightSaber");
        $this->setValidStuffs("LouisVuitton");
        $this->setValidStuffs("MiniAngelWing");
        $this->setValidStuffs("MiniAngelWingV2");
        $this->setValidStuffs("MLG RUSH 1st");
        $this->setValidStuffs("Moustache");
        $this->setValidStuffs("Neckite");
        $this->setValidStuffs("Nike");
        $this->setValidStuffs("PhantomWing");
        $this->setValidStuffs("Questionmark");
        $this->setValidStuffs("Rabbit Costume");
        $this->setValidStuffs("Rabbit");
        $this->setValidStuffs("RedWing");
        $this->setValidStuffs("Rich Bandanna");
        $this->setValidStuffs("Santa");
        $this->setValidStuffs("Sickle");
        $this->setValidStuffs("SP-BananaMan");
        $this->setValidStuffs("Susanno");
        $this->setValidStuffs("SusanooBlue");
        $this->setValidStuffs("SusanooPurple");
        $this->setValidStuffs("SWAT Shield");
        $this->setValidStuffs("ThunderCloud");
        $this->setValidStuffs("UFO");
        $this->setValidStuffs("Viking");
        $this->setValidStuffs("Wave Bandanna");
        $this->setValidStuffs("White Heart");
        $this->setValidStuffs("Witchhat");
        $this->setValidStuffs("Wither Head");
    }

    public function getLastDamagePlayer(): string
    {
        return $this->lastDamagePlayer;
    }

    public function setLastDamagePlayer(string $name): void
    {
        $this->lastDamagePlayer = $name;
    }
}
