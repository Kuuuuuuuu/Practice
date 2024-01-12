<?php

declare(strict_types=1);

namespace Nayuki\Game\Generator;

use pocketmine\block\VanillaBlocks;
use pocketmine\world\ChunkManager;
use pocketmine\world\generator\Generator;

final class VoidGenerator extends Generator
{
    public function generateChunk(ChunkManager $world, int $chunkX, int $chunkZ): void
    {
        $chunk = $world->getChunk($chunkX, $chunkZ);
        if (($chunk !== null) && $chunkX === 16 && $chunkZ === 16) {
            $chunk->setBlockStateId(0, 64, 0, VanillaBlocks::GRASS()->getStateId() << 4);
        }
    }

    public function populateChunk(ChunkManager $world, int $chunkX, int $chunkZ): void
    {
    }
}
