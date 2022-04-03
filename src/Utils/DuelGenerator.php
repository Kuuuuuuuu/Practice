<?php

namespace Kohaku\Core\Utils;

use pocketmine\block\BlockLegacyIds;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\BiomeArray;
use pocketmine\world\format\Chunk;
use pocketmine\world\format\SubChunk;
use pocketmine\world\generator\FlatGeneratorOptions;
use pocketmine\world\generator\Generator;
use function count;

class DuelGenerator extends Generator
{

    private FlatGeneratorOptions $options;

    public function __construct()
    {
        parent::__construct(0, "grass;1;");
        $this->options = FlatGeneratorOptions::parsePreset($this->preset);
        $this->generateBaseChunk();
    }

    protected function generateBaseChunk(): void
    {
        $chunk = new Chunk([], BiomeArray::fill($this->options->getBiomeId()), false);
        $structure = $this->options->getStructure();
        $count = count($structure);
        for ($sy = 0; $sy < $count; $sy += SubChunk::EDGE_LENGTH) {
            $subchunk = $chunk->getSubChunk($sy >> SubChunk::COORD_BIT_SIZE);
            for ($y = 0; $y < SubChunk::EDGE_LENGTH && isset($structure[$y | $sy]); ++$y) {
                $id = $structure[$y | $sy];
                for ($Z = 0; $Z < SubChunk::EDGE_LENGTH; ++$Z) {
                    for ($X = 0; $X < SubChunk::EDGE_LENGTH; ++$X) {
                        $subchunk->setFullBlock($X, $y, $Z, $id);
                    }
                }
            }
        }
    }

    public function generateChunk(ChunkManager $world, int $chunkX, int $chunkZ): void
    {
        $chunk = $world->getChunk($chunkX, $chunkZ);
        if ($chunkX % 20 == 0 && $chunkZ % 20 == 0) {
            for ($z = 0; $z < 16; ++$z) {
                for ($x = 0; $x < 16; ++$x) {
                    if ($x == 0 or $z == 0) {
                        for ($y = 99; $y < 110; ++$y) {
                            $chunk->setFullBlock($x, $y, $z, BlockLegacyIds::CONCRETE);
                        }
                    }
                }
            }
        }
    }

    public function populateChunk(ChunkManager $world, int $chunkX, int $chunkZ): void
    {
    }
}
