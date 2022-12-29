<?php

declare(strict_types=1);

namespace Nayuki\Misc;

use pocketmine\player\Player;
use pocketmine\world\particle\Particle as PMParticle;

final class ParticleOffsetDisplayer
{
    /**
     * @param Player $player
     * @param PMParticle $particle
     * @return void
     */
    public static function display(Player $player, PMParticle $particle): void
    {
        $slice = 2 * M_PI / 16;
        $radius = 0.75;
        for ($i = 0; $i < 16; $i++) {
            $angle = $slice * $i;
            $dx = $radius * cos($angle);
            $dz = $radius * sin($angle);
            $player->getWorld()->addParticle($player->getPosition()->add($dx, 0.5, $dz), $particle);
        }
    }
}
