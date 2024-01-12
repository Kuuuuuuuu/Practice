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
        $defaultAttackSpeed = 10;

        $session = PracticeCore::getSessionManager()->getSession($this);
        $duelingKit = $session->isDueling ? $session->DuelKit : null;
        $arenaFactory = PracticeCore::getArenaFactory();
        $arenaName = $this->getWorld()->getFolderName();

        $attackSpeed = match (true) {
            $session->isDueling && $duelingKit !== null && $duelingKit->getName() === 'Fist' => 7,
            in_array($arenaName, [$arenaFactory->getArenas('Resistance'), $arenaFactory->getArenas('Fist')]) => 8,
            $arenaName === $arenaFactory->getArenas('Combo') => 1,
            default => $defaultAttackSpeed,
        };

        parent::attack($source);

        if ($source->isCancelled()) {
            return;
        }

        $this->attackTime = $attackSpeed;
    }


    public function knockBack(float $x, float $z, float $force = 0.4, ?float $verticalLimit = 0.4): void
    {
        $defaultXzKB = 0.393;
        $defaultYKb = 0.398;

        $session = PracticeCore::getSessionManager()->getSession($this);
        $duelingKit = $session->isDueling ? $session->DuelKit : null;
        $arenaFactory = PracticeCore::getArenaFactory();
        $arenaName = $this->getWorld()->getFolderName();

        switch (true) {
            case $session->isDueling && $duelingKit !== null:
                switch ($duelingKit->getName()) {
                    case 'Boxing':
                        $xzKB = 0.378;
                        $yKb = 0.422;
                        break;
                    case 'Sumo':
                        $xzKB = 0.415;
                        $yKb = 0.395;
                        break;
                    case 'Fist':
                        $xzKB = 0.402;
                        $yKb = 0.345;
                        break;
                    default:
                        $xzKB = $defaultXzKB;
                        $yKb = $defaultYKb;
                        break;
                }
                break;
            case in_array($arenaName, [$arenaFactory->getArenas('Resistance'), $arenaFactory->getArenas('Fist')]):
                $xzKB = 0.402;
                $yKb = 0.395;
                break;
            case $arenaName === $arenaFactory->getArenas('Combo'):
                $xzKB = 0.310;
                $yKb = 0.220;
                break;
            default:
                $xzKB = $defaultXzKB;
                $yKb = $defaultYKb;
                break;
        }

        $f = sqrt($x * $x + $z * $z);
        if ($f > 0 && mt_rand() / mt_getrandmax() > $this->knockbackResistanceAttr->getValue()) {
            $f = 1 / $f;
            $motion = clone $this->motion;
            $motion->x = ($motion->x / 2) + ($x * $f * $xzKB);
            $motion->y = ($motion->y / 2) + $yKb;
            $motion->z = ($motion->z / 2) + ($z * $f * $xzKB);
            $motion->y = min($motion->y, $yKb);
            $this->setMotion($motion);
        }
    }
}
