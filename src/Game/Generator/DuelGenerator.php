<?php

declare(strict_types=1);

namespace Nayuki\Game\Generator;

use pocketmine\block\VanillaBlocks;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;
use pocketmine\world\generator\Generator;

final class DuelGenerator extends Generator
{
    public function generateChunk(ChunkManager $world, int $chunkX, int $chunkZ): void
    {
        $chunk = $world->getChunk($chunkX, $chunkZ);
        if (!($chunk instanceof Chunk)) {
            return;
        }
        $conditions = [
            [0, 0, 0, 15, 255, 0], // border on left and bottom
            [15, 0, 0, 15, 255, 15], // border on right and bottom
            [15, 0, 15, 0, 255, 15], // border on right and top
            [0, 0, 15, 0, 255, 0], // border on left and top
            [0, 0, 1, 15, 255, 1], // vertical border on left
            [1, 0, 0, 1, 255, 15], // vertical border on right
            [0, 0, 0, 15, 255, 15], // center grass
            [1, 0, 15, 15, 255, 15], // vertical border on top
        ];
        foreach ($conditions as $condition) {
            [$minX, $minY, $minZ, $maxX, $maxY, $maxZ] = $condition;
            for ($x = $minX; $x <= $maxX; $x++) {
                for ($y = $minY; $y <= $maxY; $y++) {
                    for ($z = $minZ; $z <= $maxZ; $z++) {
                        $block = ($x == $minX || $x == $maxX || $z == $minZ || $z == $maxZ) ? VanillaBlocks::GLASS() : VanillaBlocks::GRASS();
                        $chunk->setFullBlock($x, $y, $z, $block->getFullId());
                    }
                }
            }
        }
    }

    public function populateChunk(ChunkManager $world, int $chunkX, int $chunkZ): void
    {
    }
}
