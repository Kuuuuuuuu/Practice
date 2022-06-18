<?php

declare(strict_types=1);

namespace Kuu\Utils\Generator;

use pocketmine\block\BlockLegacyIds;
use pocketmine\world\ChunkManager;
use pocketmine\world\generator\Generator;

class SumoGenerator extends Generator
{

    public function generateChunk(ChunkManager $world, int $chunkX, int $chunkZ): void
    {
        $chunk = $world->getChunk($chunkX, $chunkZ);
        if ($chunkX === 16 && $chunkZ === 16) {
            for ($x = 0; $x < 6; $x++) {
                for ($z = 0; $z < 6; $z++) {
                    $chunk?->setFullBlock($x, 0, $z, BlockLegacyIds::GRASS << 4);
                }
            }
        }
    }

    public function populateChunk(ChunkManager $world, int $chunkX, int $chunkZ): void
    {
    }
}