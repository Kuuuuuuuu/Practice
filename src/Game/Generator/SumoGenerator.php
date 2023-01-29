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
        $chunk = $world->getChunk($chunkX, $chunkZ);
        if (($chunk !== null) && $chunkX % 20 === 0 && $chunkZ % 20 === 0) {
            for ($x = 0; $x < 16; $x++) {
                for ($z = 0; $z < 16; $z++) {
                    if ($x === 0 || $z === 0) {
                        for ($y = 99; $y < 256; $y++) {
                            $chunk->setFullBlock($x, $y, $z, VanillaBlocks::GLASS()->getFullId());
                        }
                    } else {
                        $chunk->setFullBlock($x, 100, $z, VanillaBlocks::OAK_PLANKS()->getFullId());
                        $chunk->setFullBlock($x, 100, $z, VanillaBlocks::OAK_PLANKS()->getFullId());
                    }
                }
            }
        }
    }

    public function populateChunk(ChunkManager $world, int $chunkX, int $chunkZ): void
    {
    }
}
