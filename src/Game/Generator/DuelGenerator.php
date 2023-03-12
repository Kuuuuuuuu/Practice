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
        if ($chunk instanceof Chunk) {
            if ($chunkX % 20 === 0 && $chunkZ % 20 === 0) {
                for ($x = 0; $x < 16; $x++) {
                    for ($z = 0; $z < 16; $z++) {
                        if ($x === 0 || $z === 0) {
                            for ($y = 99; $y < 256; $y++) {
                                $chunk->setBlockStateId($x, $y, $z, VanillaBlocks::GLASS()->getStateId());
                            }
                        } else {
                            $chunk->setBlockStateId($x, 100, $z, VanillaBlocks::GRASS()->getStateId());
                        }
                    }
                }
            } elseif ($chunkX % 20 == 1 && $chunkZ % 20 === 0) {
                for ($x = 0; $x < 16; $x++) {
                    for ($z = 0; $z < 16; $z++) {
                        if ($z === 0) {
                            for ($y = 99; $y < 256; $y++) {
                                $chunk->setBlockStateId($x, $y, $z, VanillaBlocks::GLASS()->getStateId());
                            }
                        } else {
                            $chunk->setBlockStateId($x, 100, $z, VanillaBlocks::GRASS()->getStateId());
                        }
                    }
                }
            } elseif ($chunkX % 20 === 2 && $chunkZ % 20 === 0) {
                for ($x = 0; $x < 16; $x++) {
                    for ($z = 0; $z < 16; $z++) {
                        if ($x === 15 || $z === 0) {
                            for ($y = 99; $y < 256; $y++) {
                                $chunk->setBlockStateId($x, $y, $z, VanillaBlocks::GLASS()->getStateId());
                            }
                        } else {
                            $chunk->setBlockStateId($x, 100, $z, VanillaBlocks::GRASS()->getStateId());
                        }
                    }
                }
            } elseif ($chunkX % 20 == 2 && $chunkZ % 20 === 1) {
                for ($x = 0; $x < 16; $x++) {
                    for ($z = 0; $z < 16; $z++) {
                        if ($x === 15) {
                            for ($y = 99; $y < 256; $y++) {
                                $chunk->setBlockStateId($x, $y, $z, VanillaBlocks::GLASS()->getStateId());
                            }
                        } else {
                            $chunk->setBlockStateId($x, 100, $z, VanillaBlocks::GRASS()->getStateId());
                        }
                    }
                }
            } elseif ($chunkX % 20 == 2 && $chunkZ % 20 === 2) {
                for ($x = 0; $x < 16; $x++) {
                    for ($z = 0; $z < 16; $z++) {
                        if ($x == 15 || $z == 15) {
                            for ($y = 99; $y < 256; $y++) {
                                $chunk->setBlockStateId($x, $y, $z, VanillaBlocks::GLASS()->getStateId());
                            }
                        } else {
                            $chunk->setBlockStateId($x, 100, $z, VanillaBlocks::GRASS()->getStateId());
                        }
                    }
                }
            } elseif ($chunkX % 20 === 0 && $chunkZ % 20 === 1) {
                for ($x = 0; $x < 16; $x++) {
                    for ($z = 0; $z < 16; $z++) {
                        if ($x === 0) {
                            for ($y = 99; $y < 256; $y++) {
                                $chunk->setBlockStateId($x, $y, $z, VanillaBlocks::GLASS()->getStateId());
                            }
                        } else {
                            $chunk->setBlockStateId($x, 100, $z, VanillaBlocks::GRASS()->getStateId());
                        }
                    }
                }
            } elseif ($chunkX % 20 === 1 && $chunkZ % 20 === 1) {
                for ($x = 0; $x < 16; $x++) {
                    for ($z = 0; $z < 16; $z++) {
                        $chunk->setBlockStateId($x, 100, $z, VanillaBlocks::GRASS()->getStateId());
                    }
                }
            } elseif ($chunkX % 20 === 1 && $chunkZ % 20 === 2) {
                for ($x = 0; $x < 16; $x++) {
                    for ($z = 0; $z < 16; $z++) {
                        if ($z === 15) {
                            for ($y = 99; $y < 256; $y++) {
                                $chunk->setBlockStateId($x, $y, $z, VanillaBlocks::GLASS()->getStateId());
                            }
                        } else {
                            $chunk->setBlockStateId($x, 100, $z, VanillaBlocks::GRASS()->getStateId());
                        }
                    }
                }
            } elseif ($chunkX % 20 === 0 && $chunkZ % 20 === 2) {
                for ($x = 0; $x < 16; $x++) {
                    for ($z = 0; $z < 16; $z++) {
                        if ($x === 0 || $z == 15) {
                            for ($y = 99; $y < 256; $y++) {
                                $chunk->setBlockStateId($x, $y, $z, VanillaBlocks::GLASS()->getStateId());
                            }
                        } else {
                            $chunk->setBlockStateId($x, 100, $z, VanillaBlocks::GRASS()->getStateId());
                        }
                    }
                }
            } elseif ($chunkX % 20 === 1 && $chunkZ % 20 === 2) {
                for ($x = 0; $x < 16; $x++) {
                    for ($z = 0; $z < 16; $z++) {
                        if ($z === 15 && $x === 15) {
                            for ($y = 99; $y < 256; $y++) {
                                $chunk->setBlockStateId($x, $y, $z, VanillaBlocks::GLASS()->getStateId());
                            }
                        } else {
                            $chunk->setBlockStateId($x, 100, $z, VanillaBlocks::GRASS()->getStateId());
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
