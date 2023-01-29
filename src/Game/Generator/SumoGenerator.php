<?php

declare(strict_types=1);

namespace Nayuki\Game\Generator;

use pocketmine\block\BlockLegacyIds;
use pocketmine\world\ChunkManager;
use pocketmine\world\generator\Generator;

final class SumoGenerator extends Generator
{
    public function generateChunk(ChunkManager $world, int $chunkX, int $chunkZ): void
    {
        $chunk = $world->getChunk($chunkX, $chunkZ);
        if (($chunk !== null) && $chunkX === 5 && $chunkZ === 5) {
            for ($x = -10; $x < 12; $x++) {
                for ($z = 0; $z < 12; $z++) {
                    $chunk->setFullBlock($x, 100, $z, BlockLegacyIds::GRASS << 4);
                }
            }
        }
    }

    public function populateChunk(ChunkManager $world, int $chunkX, int $chunkZ): void
    {
    }
}
