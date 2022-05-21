<?php

namespace Kuu\Arena;

use Kuu\Loader;
use Kuu\NeptunePlayer;
use Kuu\Task\NeptuneTask;
use Kuu\Utils\Kits\KitManager;
use pocketmine\Server;
use pocketmine\world\World;
use pocketmine\world\WorldException;

class EventManager
{

    private int $time = 903;
    private array $players = [];
    private World $level;
    private bool $ended = false;

    public function __construct(string $name, array $playerlist)
    {
        $world = Server::getInstance()->getWorldManager()->getWorldByName($name);
        if ($world === null) {
            throw new WorldException('World does not exist');
        }
        $this->level = $world;
        $this->players = $playerlist;
    }

}