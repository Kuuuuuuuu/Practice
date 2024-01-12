<?php

declare(strict_types=1);

namespace Nayuki\Players;

use Exception;
use Nayuki\PracticeCore;
use pocketmine\player\Player;
use pocketmine\scheduler\AsyncTask;
use function yaml_emit_file;
use function yaml_parse_file;

/** Thanks ZodiaX for this code. modified from https://github.com/ZeqaNetwork/Mineceit/blob/master/src/mineceit/data/players/AsyncLoadPlayerData.php */
final class AsyncLoadPlayerData extends AsyncTask
{
    /** @var string */
    private string $path;
    /** @var string */
    private string $playerName;

    public function __construct(Player $player, string $path)
    {
        $this->playerName = $player->getName();
        $this->path = $path;
    }

    public function onRun(): void
    {
        $playerData = [
            'name' => $this->playerName,
            'kills' => 0,
            'deaths' => 0,
            'tag' => '§aMember',
            'killStreak' => 0,
            'scoreboard' => true,
            'cps' => true,
            'cape' => '',
            'artifact' => '',
            'purchasedArtifacts' => [],
            'coins' => 0,
            'lightningKill' => true,
        ];
        $data = $this->loadFromYaml($playerData);
        $this->setResult(['data' => $data, 'player' => $this->playerName]);
    }

    /**
     * @param array $playerData
     * @return array
     */
    private function loadFromYaml(array $playerData): array
    {
        try {
            if (file_exists($this->path)) {
                $parsed = yaml_parse_file($this->path);

                foreach ($playerData as $key => $value) {
                    if (!isset($parsed[$key])) {
                        $parsed[$key] = $value;
                    }
                }

                $playerData = $parsed;
            } else {
                file_put_contents($this->path, '');
            }

            yaml_emit_file($this->path, $playerData);
        } catch (Exception) {
        }

        return $playerData;
    }


    public function onCompletion(): void
    {
        $core = PracticeCore::getInstance();
        $result = $this->getResult();
        if ($result !== null && $core->isEnabled()) {
            $server = $core->getServer();
            $playerName = (string)$result['player'];
            $data = $result['data'];
            $player = $server->getPlayerExact($playerName);
            if ($player instanceof Player && $player->isOnline()) {
                $session = PracticeCore::getSessionManager()->getSession($player);
                $player->sendMessage(PracticeCore::getPrefixCore() . 'Your data has been loaded.');
                $session->loadData($data);
                PracticeCore::getCosmeticHandler()->setSkin($player, $data['artifact']);
            }
        }
    }
}
