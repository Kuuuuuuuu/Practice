<?php

/** @noinspection PhpPropertyOnlyWrittenInspection */

declare(strict_types=1);

namespace Kuu\Utils;

use Exception;
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
    private int $elo = 1000;
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
            if (isset($data['elo'])) {
                $this->elo = $data['elo'];
            } else {
                $this->elo = 1000;
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
        yaml_emit_file($this->getPath(), ['name' => $this->player, 'kills' => $this->kills, 'killstreak' => $this->killStreak, 'kdr' => $this->getKdr(), 'deaths' => $this->deaths, 'elo' => $this->elo, 'tag' => $this->tag]);
    }

    public function getKdr(): float|int
    {
        if ($this->deaths > 0) {
            return $this->kills / $this->deaths;
        }
        return 1;
    }

    /**
     * @throws Exception
     */
    public function addElo(): int
    {
        $random = random_int(1, 30);
        $this->elo += $random;
        $this->save();
        return $random;
    }

    /**
     * @throws Exception
     */
    public function removeElo(): int
    {
        $random = random_int(1, 30);
        $this->elo -= $random;
        $this->save();
        return $random;
    }

    public function getElo(): int
    {
        return $this->elo;
    }

    public function addDeath(): void
    {
        $this->deaths++;
        $this->killStreak = 0;
        $this->save();
    }

    public function getRank(): string
    {
        $format = '§6Rookie';
        if ($this->elo >= 1200 && $this->elo < 1400) {
            $format = '§fSilver';
        } elseif ($this->elo >= 1400 && $this->elo < 1600) {
            $format = '§eGold';
        } elseif ($this->elo >= 1600 && $this->elo < 1800) {
            $format = '§dPlatinum';
        } elseif ($this->elo >= 1800 && $this->elo < 2000) {
            $format = '§bDiamond';
        } elseif ($this->elo >= 2000 && $this->elo < 2200) {
            $format = '§6Master';
        } elseif ($this->elo >= 2200 && $this->elo < 2400) {
            $format = '§3Grandmaster';
        } elseif ($this->elo >= 2400 && $this->elo < 2600) {
            $format = '§4Challenger';
        } elseif ($this->elo >= 2600 && $this->elo < 2800) {
            $format = '§5Legend';
        } elseif ($this->elo >= 2800 && $this->elo < 3000) {
            $format = '§6Legendary';
        } elseif ($this->elo >= 3000 && $this->elo < 3200) {
            $format = '§7Godlike';
        } elseif ($this->elo >= 3200) {
            $format = '§cUnbeatable';
        }
        return $format;
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
