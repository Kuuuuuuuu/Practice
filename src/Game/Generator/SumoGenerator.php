<?php

declare(strict_types=1);

namespace Nayuki\Game\Generator;

use pocketmine\block\VanillaBlocks;
use pocketmine\world\ChunkManager;
use pocketmine\world\generator\Generator;

final class SumoGenerator extends Generator
{
    public function generateChunk(ChunkManager $world, int $chunkX, int $chunkZ): void
    {
        if ($chunkX % 20 !== 0 || $chunkZ % 20 !== 0) {
            return;
        }
        $chunk = $world->getChunk($chunkX, $chunkZ);
        if ($chunk === null) {
            return;
        }
        $blocks = [
            VanillaBlocks::GRASS(),
            VanillaBlocks::DIRT(),
            VanillaBlocks::STONE(),
            VanillaBlocks::COBBLESTONE(),
        ];
        for ($x = 0; $x < 16; ++$x) {
            for ($z = 0; $z < 16; ++$z) {
                $chunk->setFullBlock($x, 100, $z, $blocks[array_rand($blocks)]->getFullId());
            }
        }
    }

    public function populateChunk(ChunkManager $world, int $chunkX, int $chunkZ): void
    {
    }
}
