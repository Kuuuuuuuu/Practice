<?php

declare(strict_types=1);

namespace Kuu\Utils\Generator;

use pocketmine\block\BlockLegacyIds;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;
use pocketmine\world\generator\Generator;

class SumoGenerator extends Generator
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
        if ($chunkX === 0 && $chunkZ === 0) {
            for ($x = -5; $x < 12; $x++) {
                for ($z = 0; $z < 12; $z++) {
                    $chunk->setFullBlock($x, 100, $z, BlockLegacyIds::GRASS << 4);
                }
            }
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