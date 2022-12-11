<?php

declare(strict_types=1);

namespace Kuu\Misc;

use pocketmine\player\Player;
use pocketmine\world\particle\Particle as PMParticle;

class ParticleDisplayer
{
    /**
     * @param Player $player
     * @param PMParticle $particle
     * @return void
     */
    public static function display(Player $player, PMParticle $particle): void
    {
        $slice = 2 * M_PI / 16;
        $radius = 0.65;
        $playerOffset = 2;
        for ($i = 0; $i < 16; $i++) {
            $angle = $slice * $i;
            $dx = $radius * cos($angle);
            $dy = $playerOffset;
            $dz = $radius * sin($angle);
            $player->getWorld()->addParticle($player->getPosition()->add($dx, $dy, $dz), $particle);
        }
    }
}
