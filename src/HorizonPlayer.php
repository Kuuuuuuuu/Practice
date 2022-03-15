<?php

declare(strict_types=1);

namespace Kohaku\Core;

use JsonException;
use Kohaku\Core\Utils\CapeUtils;
use pocketmine\{entity\Skin, player\Player, Server};
use pocketmine\event\entity\{EntityDamageByEntityEvent, EntityDamageEvent};

class HorizonPlayer extends Player
{

    private float|int $xzKB = 0.32;
    private float|int $yKb = 0.34;

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
                if ($damager->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getComboArena())) {
                    $attackSpeed = 1;
                } else if ($damager->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getKitPVPArena())) {
                    $attackSpeed = 8;
                } else if ($damager->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getFistArena())) {
                    $attackSpeed = 7;
                } else if ($damager->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getBoxingArena())) {
                    $attackSpeed = 7;
                } else if ($damager->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getResistanceArena())) {
                    $attackSpeed = 7;
                }
            }
        }
        $this->attackTime = $attackSpeed;
    }

    public function knockBack(float $x, float $z, float $force = 0.4, ?float $verticalLimit = 0.4): void
    {
        if ($this->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getComboArena())) {
            $this->xzKB = 0.233;
            $this->yKb = 0.166;
        } else if ($this->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getKitPVPArena())) {
            $this->xzKB = 0.33;
            $this->yKb = 0.29;
        } else if ($this->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getFistArena())) {
            $this->xzKB = 0.32;
            $this->yKb = 0.311;
        } else if ($this->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getBoxingArena())) {
            $this->xzKB = 0.32;
            $this->yKb = 0.311;
        } else if ($this->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(Loader::$arenafac->getResistanceArena())) {
            $this->xzKB = 0.32;
            $this->yKb = 0.311;
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
        if (file_exists(Loader::getInstance()->getDataFolder() . Loader::getInstance()->CapeData->get($this->getName()) . ".png")) {
            $oldSkin = $this->getSkin();
            $capeData = CapeUtils::getInstance()->createCape(Loader::getInstance()->CapeData->get($this->getName()));
            $setCape = new Skin($oldSkin->getSkinId(), $oldSkin->getSkinData(), $capeData, $oldSkin->getGeometryName(), $oldSkin->getGeometryData());
            $this->setSkin($setCape);
            $this->sendSkin();
        } else {
            Loader::getInstance()->CapeData->remove($this->getName());
            Loader::getInstance()->CapeData->save();
        }
    }
}
