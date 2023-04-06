<?php

declare(strict_types=1);

namespace Nayuki\Players;

use Nayuki\PracticeCore;
use pocketmine\player\Player;
use pocketmine\scheduler\AsyncTask;
use function fclose;
use function is_array;
use function yaml_emit_file;
use function yaml_parse_file;

class AsyncLoadPlayerData extends AsyncTask
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
        $playerData = ['kills' => 0, 'deaths' => 0, 'tag' => '§aMember', 'killStreak' => 0, 'scoreboard' => true, 'cps' => true,];
	  $data = $this->loadFromYaml($playerData);
        $this->setResult(['data' => $data, 'player' => $this->playerName]);
    }


    /**
     * @param array<string, int|string|bool|null> $playerData
     * @return array<string, int|string|bool|null>
     */
    private function loadFromYaml(array $playerData): array
    {
	  if (file_exists($this->path)) {
		$parsed = yaml_parse_file($this->path);
		if (is_array($parsed)) {
		   foreach ($playerData as $key => $value) {
			 if (!isset($parsed[$key])) {
			    $parsed[$key] = $value;
			 }
		   }
		   $playerData = $parsed;
		}
	  } else {
		$file = fopen($this->path, 'wb');
		if ($file !== false) {
		   fclose($file);
		}
	  }
	  yaml_emit_file($this->path, $playerData);
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
            }
        }
    }
}
