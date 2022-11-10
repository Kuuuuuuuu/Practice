<?php

declare(strict_types=1);

namespace Kuu\Players;

use Kuu\PracticePlayer;
use pocketmine\scheduler\AsyncTask;

class AsyncSavePlayerData extends AsyncTask
{
    /** @var string */
    private string $path;
    /** @var array */
    private array $playerdata;

    public function __construct(PracticePlayer $player, string $path)
    {
        $this->path = $path;
        $this->playerdata = [
            'kills' => $player->getKills(),
            'deaths' => $player->getDeaths(),
            'tag' => $player->getCustomTag(),
            'killStreak' => $player->getStreak(),
            'artifact' => $player->getStuff(),
            'cape' => $player->getCape(),
        ];
    }

    public function onRun(): void
    {
        $info = (array)$this->playerdata;
        $keys = array_keys($info);
        $parsed = yaml_parse_file($this->path);
        foreach ($keys as $key) {
            $parsed[$key] = $info[$key];
        }
        yaml_emit_file($this->path, $parsed);
    }
}