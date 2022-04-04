<?php

declare(strict_types=1);

namespace Kohaku\Utils;

use pocketmine\math\Vector3;
use pocketmine\world\ChunkListener;
use pocketmine\world\ChunkLoader;
use pocketmine\world\format\Chunk;
use pocketmine\world\Position;
use pocketmine\world\World;

class ChunkManager implements ChunkListener, ChunkLoader
{
    private Position $position;
    private int $chunkX;
    private int $chunkZ;
    private World $world;
    private mixed $callable;

    public function __construct(World $level, int $chunkX, int $chunkZ, callable $callable)
    {
        $this->position = Position::fromObject(new Vector3($chunkX << 4, 0, $chunkZ << 4), $level);
        $this->chunkX = $chunkX;
        $this->chunkZ = $chunkZ;
        $this->world = $level;
        $this->callable = $callable;
    }

    public function isLoaderActive(): bool
    {
        return true;
    }

    public function getPosition(): Position
    {
        return $this->position;
    }

    public function getWorld(): World
    {
        return $this->world;
    }

    public function onChunkChanged(int $chunkX, int $chunkZ, Chunk $chunk): void
    {
    }

    public function onChunkLoaded(int $chunkX, int $chunkZ, Chunk $chunk): void
    {
        if (!$chunk->isPopulated()) {
            return;
        }
        $this->world->unregisterChunkLoader($this, $this->chunkX, $this->chunkZ);
        $this->world->unregisterChunkListener($this, $this->chunkX, $this->chunkZ);
        ($this->callable)();
    }

    public function onChunkUnloaded(int $chunkX, int $chunkZ, Chunk $chunk): void
    {
    }

    public function onChunkPopulated(int $chunkX, int $chunkZ, Chunk $chunk): void
    {
        $this->world->unregisterChunkLoader($this, intval($this->getX()), intval($this->getZ()));
        $this->world->unregisterChunkListener($this, $this->chunkX, $this->chunkZ);
        ($this->callable)();
    }

    public function getX(): float|int
    {
        return $this->chunkX;
    }

    public function getZ(): float|int
    {
        return $this->chunkZ;
    }

    public function onBlockChanged(Vector3 $block): void
    {
    }
}