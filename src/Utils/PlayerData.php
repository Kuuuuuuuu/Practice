<?php /** @noinspection PhpPropertyOnlyWrittenInspection */

declare(strict_types=1);

namespace Kohaku\Core\Utils;

use JetBrains\PhpStorm\Pure;
use Kohaku\Core\Loader;

class PlayerData
{

    private string $player;
    private int $kills = 0;
    private int $killStreak = 0;
    private int|float $kdr = 0;
    private int $deaths = 0;
    private mixed $data = null;

    public function __construct(string $player)
    {
        $this->player = $player ?? null;
        $path = $this->getPath();
        if (is_file($path)) {
            $data = yaml_parse_file($path);
            $this->data = $data;
            $this->kills = $data["kills"];
            if (isset($data["killstreak"])) {
                $this->killStreak = $data["killstreak"];
            } else {
                $this->killStreak = 0;
            }
            if (isset($data["kdr"])) {
                $this->kdr = $data["kdr"];
            } else {
                $this->kdr = 0;
            }
            $this->deaths = $data["deaths"];
        }
    }

    #[Pure] private function getPath(): string
    {
        return Loader::getInstance()->getDataFolder() . "players/" . strtolower($this->player) . ".yml";
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

    public function addKill()
    {
        $this->kills++;
        $this->killStreak++;
        $this->save();
    }

    private function save()
    {
        yaml_emit_file($this->getPath(), ["name" => $this->player, "kills" => $this->kills, "killstreak" => $this->killStreak, "kdr" => $this->getKdr(), "deaths" => $this->deaths]);
    }

    public function getKdr(): float|int
    {
        if ($this->deaths > 0) {
            return $this->kills / $this->deaths;
        } else {
            return 1;
        }
    }

    public function addDeath()
    {
        $this->deaths++;
        $this->killStreak = 0;
        $this->save();
    }
}
