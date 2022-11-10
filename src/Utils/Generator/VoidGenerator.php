<?php

declare(strict_types=1);

namespace Kuu\Utils\Generator;

use pocketmine\block\BlockLegacyIds;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;
use pocketmine\world\generator\Generator;

class VoidGenerator extends Generator
{

    /**
     * @param ChunkManager $world
     * @param int $chunkX
     * @param int $chunkZ
     * @return void
     */
    public function generateChunk(ChunkManager $world, int $chunkX, int $chunkZ): void
    {
        $chunk = $world->getChunk($chunkX, $chunkZ);
        assert($chunk instanceof Chunk);
        if ($chunkX === 16 && $chunkZ === 16) {
            $chunk->setFullBlock(0, 64, 0, BlockLegacyIds::GRASS << 4);
        }
    }

    /**
     * @param ChunkManager $world
     * @param int $chunkX
     * @param int $chunkZ
     * @return void
     */
    public function populateChunk(ChunkManager $world, int $chunkX, int $chunkZ): void
    {
    }
}