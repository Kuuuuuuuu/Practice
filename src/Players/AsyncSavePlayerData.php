<?php

declare(strict_types=1);

namespace Nayuki\Players;

use Nayuki\PracticeCore;
use pocketmine\player\Player;
use pocketmine\scheduler\AsyncTask;

use function yaml_parse_file;

class AsyncSavePlayerData extends AsyncTask
{
    /** @var string */
    private string $path;
    /** @var array */
    private array $playerdata;

    public function __construct(Player $player, string $path)
    {
        $session = PracticeCore::getSessionManager()->getSession($player);
        $this->path = $path;
        $this->playerdata = (array)[
            'kills' => $session->getKills(),
            'deaths' => $session->getDeaths(),
            'tag' => $session->getCustomTag(),
            'killStreak' => $session->getStreak(),
            'scoreboard' => $session->ScoreboardEnabled,
            'cps' => $session->CpsCounterEnabled,
            'smoothpearl' => $session->SmoothPearlEnabled,
        ];
        PracticeCore::getSessionManager()->removeSession($player);
    }

    public function onRun(): void
    {
        $parsed = yaml_parse_file($this->path);
        foreach ((array)$this->playerdata as $key => $value) {
            $parsed[$key] = $value;
        }
        $yaml = yaml_emit($parsed);
        file_put_contents($this->path, $yaml);
    }
}
