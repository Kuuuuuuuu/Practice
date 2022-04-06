<?php

namespace Kohaku\Utils;

use pocketmine\world\ChunkManager;
use pocketmine\world\format\BiomeArray;
use pocketmine\world\format\Chunk;
use pocketmine\world\format\SubChunk;
use pocketmine\world\generator\FlatGeneratorOptions;
use pocketmine\world\generator\Generator;
use function count;

class DuelGenerator extends Generator
{

    private Chunk $chunk;

    private FlatGeneratorOptions $options;

    public function __construct()
    {
        parent::__construct(    0, "2;bedrock,stonebrick;1;");
        $this->options = FlatGeneratorOptions::parsePreset($this->preset);
        $this->generateBaseChunk();
    }

    protected function generateBaseChunk(): void
    {
        $this->chunk = new Chunk([], BiomeArray::fill($this->options->getBiomeId()), false);
        $structure = $this->options->getStructure();
        $count = count($structure);
        for ($sy = 0; $sy < $count; $sy += SubChunk::EDGE_LENGTH) {
            $subchunk = $this->chunk->getSubChunk($sy >> SubChunk::COORD_BIT_SIZE);
            for ($y = 0; $y < SubChunk::EDGE_LENGTH and isset($structure[$y | $sy]); ++$y) {
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
        $world->setChunk($chunkX, $chunkZ, clone $this->chunk);
        $world->getChunk($chunkX, $chunkZ)->collectGarbage();
    }

    public function populateChunk(ChunkManager $world, int $chunkX, int $chunkZ): void
    {
    }
}
