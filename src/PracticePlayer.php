<?php

declare(strict_types=1);

namespace Nayuki;

use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\player\Player;

use function mt_getrandmax;
use function mt_rand;
use function sqrt;

/**
 * @internal
 */
class PracticePlayer extends Player
{
    public function attack(EntityDamageEvent $source): void
    {
        $attackSpeed = 10;
        $session = PracticeCore::getSessionManager()::getSession($this);
        if ($session->isDueling) {
            switch ($session->DuelKit?->getName()) {
                case 'Fist':
                    $attackSpeed = 8;
                    break;
                case 'Combo':
                    $attackSpeed = 1;
                    break;
            }
        } else {
            switch ($this->getWorld()->getFolderName()) {
                case PracticeCore::getArenaFactory()->getArenas('Resistance'):
                case PracticeCore::getArenaFactory()->getArenas('Fist'):
                    $attackSpeed = 8;
                    break;
                case PracticeCore::getArenaFactory()->getArenas('Combo'):
                    $attackSpeed = 1;
                    break;
            }
        }
        parent::attack($source);
        if ($source->isCancelled()) {
            return;
        }
        $this->attackTime = $attackSpeed;
    }

    public function knockBack(float $x, float $z, float $force = 0.4, ?float $verticalLimit = 0.4): void
    {
        $xzKB = 0.393;
        $yKb = 0.398;
        $session = PracticeCore::getSessionManager()::getSession($this);
        if ($session->isDueling) {
            switch ($session->DuelKit?->getName()) {
                case 'Boxing':
                    $xzKB = 0.378;
                    $yKb = 0.422;
                    break;
                case 'Sumo':
                    $xzKB = 0.425;
                    $yKb = 0.385;
                    break;
                case 'Fist':
                    $xzKB = 0.402;
                    $yKb = 0.345;
                    break;
                case 'Combo':
                    $xzKB = 0.310;
                    $yKb = 0.220;
                    break;
            }
        } else {
            switch ($this->getWorld()->getFolderName()) {
                case PracticeCore::getArenaFactory()->getArenas('Resistance'):
                case PracticeCore::getArenaFactory()->getArenas('Fist'):
                    $xzKB = 0.402;
                    $yKb = 0.395;
                    break;
                case PracticeCore::getArenaFactory()->getArenas('Combo'):
                    $xzKB = 0.310;
                    $yKb = 0.220;
                    break;
            }
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
}
