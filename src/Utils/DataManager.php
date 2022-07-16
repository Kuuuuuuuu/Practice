<?php

/** @noinspection PhpPropertyOnlyWrittenInspection */

declare(strict_types=1);

namespace Kuu\Utils;

use JetBrains\PhpStorm\Pure;
use Kuu\PracticeCore;

class DataManager
{
    private string $player;
    private int $kills = 0;
    private int $killStreak = 0;
    private int|float $kdr = 0;
    private int $deaths = 0;
    private array $data = [];
    private ?string $tag = null;

    public function __construct(string $player)
    {
        $this->player = $player;
        $path = $this->getPath();
        if (is_file($path)) {
            $data = yaml_parse_file($path);
            $this->data[] = $data;
            if (isset($data['kills'])) {
                $this->kills = $data['kills'];
            } else {
                $this->kills = 0;
            }
            if (isset($data['deaths'])) {
                $this->deaths = $data['deaths'];
            } else {
                $this->deaths = 0;
            }
            if (isset($data['killstreak'])) {
                $this->killStreak = $data['killstreak'];
            } else {
                $this->killStreak = 0;
            }
            if (isset($data['kdr'])) {
                $this->kdr = $data['kdr'];
            } else {
                $this->kdr = 1;
            }
            if (isset($data['tag'])) {
                $this->tag = $data['tag'];
            } else {
                $this->tag = null;
            }
        }
    }

    #[Pure] private function getPath(): string
    {
        return PracticeCore::getInstance()->getDataFolder() . 'players/' . strtolower($this->player) . '.yml';
    }

    public function getName(): string
    {
        return $this->player;
    }

    public function getKills()
    {
        return $this->kills;
    }

    public function getStreak()
    {
        return $this->killStreak;
    }

    public function getDeaths()
    {
        return $this->deaths;
    }

    public function addKill(): void
    {
        $this->kills++;
        $this->killStreak++;
        $this->save();
    }

    private function save(): void
    {
        yaml_emit_file($this->getPath(), ['name' => $this->player, 'kills' => $this->kills, 'killstreak' => $this->killStreak, 'kdr' => $this->getKdr(), 'deaths' => $this->deaths, 'tag' => $this->tag]);
    }

    public function getKdr(): float|int
    {
        if ($this->deaths > 0) {
            return $this->kills / $this->deaths;
        }
        return 1;
    }

    public function addDeath(): void
    {
        $this->deaths++;
        $this->killStreak = 0;
        $this->save();
    }

    public function getTag(): ?string
    {
        return $this->tag ?? null;
    }

    public function setTag(string $tag): void
    {
        $this->tag = $tag;
        $this->save();
    }
}
