<?php

declare(strict_types=1);

namespace Nayuki\Arena;

use JsonException;
use Nayuki\PracticeCore;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

use function count;
use function is_string;

final class ArenaFactory
{
    /**
     * @param mixed $arena
     * @return string
     */
    public function getPlayers(mixed $arena): string
    {
        if (is_string($arena)) {
            $world = Server::getInstance()->getWorldManager()->getWorldByName($arena);
            if ($world) {
                return (string)count($world->getPlayers());
            }
        }
        return 'Unknown';
    }

    /**
     * @param string $mode
     * @return string|null
     */
    public function getArenas(string $mode): string|null
    {
        $data = new Config(PracticeCore::getInstance()->getDataFolder() . 'data/arenas.yml', Config::YAML);
        return $data->get($mode, null);
    }

    /**
     * @param Player $player
     * @param string $mode
     * @param string $world
     * @return void
     * @throws JsonException
     */
    public function setArenas(Player $player, string $mode, string $world): void
    {
        $data = new Config(PracticeCore::getInstance()->getDataFolder() . 'data/arenas.yml', Config::YAML);
        $data->set($mode, $world);
        $data->save();
        $player->sendMessage(PracticeCore::getPrefixCore() . TextFormat::GREEN . 'The Arena was saved');
    }

    /**
     * @param Player $player
     * @param string $mode
     * @return void
     * @throws JsonException
     */
    public function removeArenas(Player $player, string $mode): void
    {
        $data = new Config(PracticeCore::getInstance()->getDataFolder() . 'data/arenas.yml', Config::YAML);
        $data->remove($mode);
        $data->save();
        $player->sendMessage(PracticeCore::getPrefixCore() . 'Removed arena');
    }
}
