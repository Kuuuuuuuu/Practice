<?php

declare(strict_types=1);

namespace Nayuki\Players;

use Nayuki\PracticeCore;
use pocketmine\player\Player;
use pocketmine\scheduler\AsyncTask;

use function yaml_parse_file;
use function yaml_emit_file;
use function array_keys;

class AsyncSavePlayerData extends AsyncTask
{
    /** @var string */
    private string $path;
    /** @var array */
    private array $playerdata;

    public function __construct(Player $player, string $path)
    {
        $session = PracticeCore::getPlayerSession()::getSession($player);
        $this->path = $path;
        $this->playerdata = [
            'kills' => $session->getKills(),
            'deaths' => $session->getDeaths(),
            'tag' => $session->getCustomTag(),
            'killStreak' => $session->getStreak(),
            'scoreboard' => $session->ScoreboardEnabled,
            'cps' => $session->CpsCounterEnabled,
            'smoothpearl' => $session->SmoothPearlEnabled,
        ];
        PracticeCore::getPlayerSession()::removeSession($player);
        unset(PracticeCore::getCaches()->PlayerInSession[$player->getName()]);
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
