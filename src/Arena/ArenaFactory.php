<?php

declare(strict_types=1);

namespace Nayuki\Arena;

use JsonException;
use Nayuki\PracticeCore;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\world\World;

use function count;
use function is_string;

class ArenaFactory
{
    /**
     * @param mixed $arena
     * @return string
     */
    public function getPlayers(mixed $arena): string
    {
        if (is_string($arena)) {
            $world = Server::getInstance()->getWorldManager()->getWorldByName($arena);
            if ($world instanceof World) {
                return (string)count($world->getPlayers());
            }
        }
        return 'Error: Unknown arena';
    }

    /**
     * @return string
     */
    public function getBoxingArena(): string
    {
        $data = new Config(PracticeCore::getInstance()->getDataFolder() . 'data/arenas.yml', Config::YAML);
        return $data->get('Boxing', 'no');
    }

    /**
     * @return string
     */
    public function getNodebuffArena(): string
    {
        $data = new Config(PracticeCore::getInstance()->getDataFolder() . 'data/arenas.yml', Config::YAML);
        return $data->get('Nodebuff', 'no');
    }

    /**
     * @param Player $player
     * @param string $world
     * @return void
     * @throws JsonException
     */
    public function setNodebuffArena(Player $player, string $world): void
    {
        $data = new Config(PracticeCore::getInstance()->getDataFolder() . 'data/arenas.yml', Config::YAML);
        $data->set('Nodebuff', $world);
        $data->save();
        $player->sendMessage(PracticeCore::getPrefixCore() . '§aThe Arena was saved');
    }

    /**
     * @param Player $player
     * @param string $world
     * @return void
     * @throws JsonException
     */

    public function setBoxingArena(Player $player, string $world): void
    {
        $data = new Config(PracticeCore::getInstance()->getDataFolder() . 'data/arenas.yml', Config::YAML);
        $data->set('Boxing', $world);
        $data->save();
        $player->sendMessage(PracticeCore::getPrefixCore() . '§aThe Arena was saved');
    }

    /**
     * @param Player $player
     * @return void
     * @throws JsonException
     */
    public function removeNodebuff(Player $player): void
    {
        $data = new Config(PracticeCore::getInstance()->getDataFolder() . 'data/arenas.yml', Config::YAML);
        $data->remove('Nodebuff');
        $data->save();
        $player->sendMessage(PracticeCore::getPrefixCore() . 'Removed arena');
    }

    /**
     * @param Player $player
     * @return void
     * @throws JsonException
     */
    public function removeBoxing(Player $player): void
    {
        $data = new Config(PracticeCore::getInstance()->getDataFolder() . 'data/arenas.yml', Config::YAML);
        $data->remove('Boxing');
        $data->save();
        $player->sendMessage(PracticeCore::getPrefixCore() . 'Removed arena');
    }
}
