<?php

namespace Kuu\Utils\Generator;

use pocketmine\block\VanillaBlocks;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;
use pocketmine\world\generator\Generator;

class DuelGenerator extends Generator
{

    public function generateChunk(ChunkManager $world, int $chunkX, int $chunkZ): void
    {
        $chunk = $world->getChunk($chunkX, $chunkZ);
        assert($chunk instanceof Chunk);
        if ($chunkX % 20 === 0 && $chunkZ % 20 === 0) {
            for ($x = 0; $x < 16; $x++) {
                for ($z = 0; $z < 16; $z++) {
                    if ($x === 0 || $z === 0) {
                        for ($y = 99; $y < 256; $y++) {
                            $chunk->setFullBlock($x, $y, $z, VanillaBlocks::BARRIER()->getId() << 4);
                        }
                    } else {
                        $chunk->setFullBlock($x, 100, $z, VanillaBlocks::GRASS()->getId() << 4);
                        $chunk->setFullBlock($x, 100, $z, VanillaBlocks::GRASS()->getId() << 4);
                    }
                }
            }
        } elseif ($chunkX % 20 === 1 && $chunkZ % 20 === 0) {
            for ($x = 0; $x < 16; $x++) {
                for ($z = 0; $z < 16; $z++) {
                    if ($z === 0) {
                        for ($y = 99; $y < 256; $y++) {
                            $chunk->setFullBlock($x, $y, $z, VanillaBlocks::BARRIER()->getId() << 4);
                        }
                    } else {
                        $chunk->setFullBlock($x, 100, $z, VanillaBlocks::GRASS()->getId() << 4);
                        $chunk->setFullBlock($x, 100, $z, VanillaBlocks::GRASS()->getId() << 4);
                    }
                }
            }
        } elseif ($chunkX % 20 === 2 && $chunkZ % 20 === 0) {
            for ($x = 0; $x < 16; $x++) {
                for ($z = 0; $z < 16; $z++) {
                    if ($x === 15 || $z === 0) {
                        for ($y = 99; $y < 256; $y++) {
                            $chunk->setFullBlock($x, $y, $z, VanillaBlocks::BARRIER()->getId() << 4);
                        }
                    } else {
                        $chunk->setFullBlock($x, 100, $z, VanillaBlocks::GRASS()->getId() << 4);
                        $chunk->setFullBlock($x, 100, $z, VanillaBlocks::GRASS()->getId() << 4);
                    }
                }
            }
        } elseif ($chunkX % 20 === 2 && $chunkZ % 20 === 1) {
            for ($x = 0; $x < 16; $x++) {
                for ($z = 0; $z < 16; $z++) {
                    if ($x === 15) {
                        for ($y = 99; $y < 256; $y++) {
                            $chunk->setFullBlock($x, $y, $z, VanillaBlocks::BARRIER()->getId() << 4);
                        }
                    } else {
                        $chunk->setFullBlock($x, 100, $z, VanillaBlocks::GRASS()->getId() << 4);
                        $chunk->setFullBlock($x, 100, $z, VanillaBlocks::GRASS()->getId() << 4);
                    }
                }
            }
        } elseif ($chunkX % 20 === 2 && $chunkZ % 20 === 2) {
            for ($x = 0; $x < 16; $x++) {
                for ($z = 0; $z < 16; $z++) {
                    if ($x === 15 || $z === 15) {
                        for ($y = 99; $y < 256; $y++) {
                            $chunk->setFullBlock($x, $y, $z, VanillaBlocks::BARRIER()->getId() << 4);
                        }
                    } else {
                        $chunk->setFullBlock($x, 100, $z, VanillaBlocks::GRASS()->getId() << 4);
                        $chunk->setFullBlock($x, 100, $z, VanillaBlocks::GRASS()->getId() << 4);
                    }
                }
            }
        } elseif ($chunkX % 20 === 0 && $chunkZ % 20 === 1) {
            for ($x = 0; $x < 16; $x++) {
                for ($z = 0; $z < 16; $z++) {
                    if ($x === 0) {
                        for ($y = 99; $y < 256; $y++) {
                            $chunk->setFullBlock($x, $y, $z, VanillaBlocks::BARRIER()->getId() << 4);
                        }
                    } else {
                        $chunk->setFullBlock($x, 100, $z, VanillaBlocks::GRASS()->getId() << 4);
                        $chunk->setFullBlock($x, 100, $z, VanillaBlocks::GRASS()->getId() << 4);
                    }
                }
            }
        } elseif ($chunkX % 20 === 1 && $chunkZ % 20 === 1) {
            for ($x = 0; $x < 16; $x++) {
                for ($z = 0; $z < 16; $z++) {
                    $chunk->setFullBlock($x, 100, $z, VanillaBlocks::GRASS()->getId() << 4);
                    $chunk->setFullBlock($x, 100, $z, VanillaBlocks::GRASS()->getId() << 4);
                }
            }
        } elseif ($chunkX % 20 === 1 && $chunkZ % 20 === 2) {
            for ($x = 0; $x < 16; $x++) {
                for ($z = 0; $z < 16; $z++) {
                    if ($z === 15) {
                        for ($y = 99; $y < 256; $y++) {
                            $chunk->setFullBlock($x, $y, $z, VanillaBlocks::BARRIER()->getId() << 4);
                        }
                    } else {
                        $chunk->setFullBlock($x, 100, $z, VanillaBlocks::GRASS()->getId() << 4);
                        $chunk->setFullBlock($x, 100, $z, VanillaBlocks::GRASS()->getId() << 4);
                    }
                }
            }
        } elseif ($chunkX % 20 === 0 && $chunkZ % 20 === 2) {
            for ($x = 0; $x < 16; $x++) {
                for ($z = 0; $z < 16; $z++) {
                    if ($x === 0 || $z === 15) {
                        for ($y = 99; $y < 256; $y++) {
                            $chunk->setFullBlock($x, $y, $z, VanillaBlocks::BARRIER()->getId() << 4);
                        }
                    } else {
                        $chunk->setFullBlock($x, 100, $z, VanillaBlocks::GRASS()->getId() << 4);
                        $chunk->setFullBlock($x, 100, $z, VanillaBlocks::GRASS()->getId() << 4);
                    }
                }
            }
        } elseif ($chunkX % 20 === 1 && $chunkZ % 20 === 2) {
            for ($x = 0; $x < 16; $x++) {
                for ($z = 0; $z < 16; $z++) {
                    if ($z === 15 && $x === 15) {
                        for ($y = 99; $y < 256; $y++) {
                            $chunk->setFullBlock($x, $y, $z, VanillaBlocks::BARRIER()->getId() << 4);
                        }
                    } else {
                        $chunk->setFullBlock($x, 100, $z, VanillaBlocks::GRASS()->getId() << 4);
                        $chunk->setFullBlock($x, 100, $z, VanillaBlocks::GRASS()->getId() << 4);
                    }
                }
            }
        }
    }

    public function populateChunk(ChunkManager $world, int $chunkX, int $chunkZ): void
    {
    }
}