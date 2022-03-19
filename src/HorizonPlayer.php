<?php

declare(strict_types=1);

namespace Kohaku\Core;

use Exception;
use JsonException;
use Kohaku\Core\Utils\CosmeticHandler;
use Kohaku\Core\Utils\KnockbackManager;
use pocketmine\{entity\Skin, player\Player};
use pocketmine\event\entity\{EntityDamageByEntityEvent, EntityDamageEvent};

class HorizonPlayer extends Player
{

    private float|int $xzKB = 0.32;
    private float|int $yKb = 0.34;
    private string $cape = '';
    private string $stuff = '';
    private array $validstuffs = [];

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
                    }
                } catch (Exception $e) {
                    $attackSpeed = 7;
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
            }
        } catch (Exception $e) {
            $this->xzKB = 0.32;
            $this->yKb = 0.34;
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
        Loader::getInstance()->PlayerSkin[$this->getName()] = $this->getSkin();
        if (file_exists(Loader::getInstance()->getDataFolder() . "capes/" . Loader::getInstance()->CapeData->get($this->getName()) . ".png")) {
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
        if ($this->getStuff() !== "") {
            CosmeticHandler::getInstance()->setSkin($this, $this->getStuff());
        }
    }

    public function getStuff(): string
    {
        return $this->stuff;
    }

    public function setStuff(string $stuff): string
    {
        return $this->stuff = $stuff;
    }

    public function getCape(): string
    {
        return $this->cape;
    }

    public function setCape(string $cape): string
    {
        return $this->cape = $cape;
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
        $this->setValidStuffs('AngelWing');
        $this->setValidStuffs('AngelWingV2');
        $this->setValidStuffs('Antler');
        $this->setValidStuffs('Axe');
        $this->setValidStuffs('BackCap');
        $this->setValidStuffs('Backpack');
        $this->setValidStuffs('BackStabKnife');
        $this->setValidStuffs('Bald Headband');
        $this->setValidStuffs('Banana');
        $this->setValidStuffs('Adidas');
        $this->setValidStuffs('Boxing');
        $this->setValidStuffs('Nike');
        $this->setValidStuffs('LouisVuitton');
        $this->setValidStuffs('BlackAngleSet');
        $this->setValidStuffs('BlazeRod');
        $this->setValidStuffs('BlueWing');
        $this->setValidStuffs('Bubble');
        $this->setValidStuffs('Creeper');
        $this->setValidStuffs('Crown');
        $this->setValidStuffs('CrownV2');
        $this->setValidStuffs('DevilHaloWing');
        $this->setValidStuffs('DragonWing');
        $this->setValidStuffs('EnderWing');
        $this->setValidStuffs('HeadphoneNote');
    }
}
