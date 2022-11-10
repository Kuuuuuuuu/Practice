<?php

declare(strict_types=1);

namespace Kuu\Players;

use JsonException;
use Kuu\PracticeCore;
use Kuu\PracticePlayer;
use pocketmine\scheduler\AsyncTask;

class AsyncLoadPlayerData extends AsyncTask
{
    /** @var string */
    private string $path;
    /** @var string */
    private string $playerName;

    public function __construct(PracticePlayer $player, string $path)
    {
        $this->playerName = $player->getName();
        $this->path = $path;
    }

    public function onRun(): void
    {
        $playerData = [
            'kills' => 0,
            'deaths' => 0,
            'tag' => '',
            'killStreak' => 0,
            'artifact' => '',
            'cape' => '',
        ];
        $data = $this->loadFromYaml($playerData);
        $this->setResult(['data' => $data, 'player' => $this->playerName]);
    }

    private function loadFromYaml(array $playerData): array
    {
        if (!file_exists($this->path)) {
            $file = fopen($this->path, 'wb');
            fclose($file);
        } else {
            $keys = array_keys($playerData);
            $parsed = yaml_parse_file($this->path);
            foreach ($keys as $key) {
                $value = $playerData[$key];
                if (!isset($parsed[$key])) {
                    $parsed[$key] = $value;
                }
            }
            $playerData = $parsed;
        }
        yaml_emit_file($this->path, $playerData);
        return $playerData;
    }

    /**
     * @throws JsonException
     */
    public function onCompletion(): void
    {
        $core = PracticeCore::getInstance();
        $result = $this->getResult();
        if ($core->isEnabled() && $result !== null) {
            $server = $core->getServer();
            $playerName = (string)$result['player'];
            $data = $result['data'];
            $player = $server->getPlayerByPrefix($playerName);
            if ($player instanceof PracticePlayer && $player->isOnline()) {
                $player->loadData($data);
            }
        }
    }
}