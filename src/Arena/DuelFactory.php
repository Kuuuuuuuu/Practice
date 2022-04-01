<?php

declare(strict_types=1);

namespace JavierLeon9966\ProperDuels\arena;

use pocketmine\math\Vector3;

final class DuelFactory
{

    private string $name;
    private string $levelName;
    private Vector3 $firstSpawnPos;
    private Vector3 $secondSpawnPos;

    public function __construct(string $name, string $levelName, Vector3 $firstSpawnPos, Vector3 $secondSpawnPos)
    {
        $this->firstSpawnPos = clone $firstSpawnPos;
        $this->secondSpawnPos = clone $secondSpawnPos;
        $this->name = $name;
        $this->levelName = $levelName;
    }

    public function getFirstSpawnPos(): Vector3
    {
        return clone $this->firstSpawnPos;
    }

    public function getSecondSpawnPos(): Vector3
    {
        return clone $this->secondSpawnPos;
    }

    public function getLevelName(): string
    {
        return $this->levelName;
    }

    public function getName(): string
    {
        return $this->name;
    }
}