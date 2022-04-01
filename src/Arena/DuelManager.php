<?php

namespace Kohaku\Core\Arena;

use JetBrains\PhpStorm\Pure;
use Kohaku\Core\HorizonPlayer;
use Kohaku\Core\Loader;
use Kohaku\Core\Task\DuelTask;
use pocketmine\Server;
use pocketmine\utils\SingletonTrait;
use pocketmine\world\generator\Flat;
use pocketmine\world\WorldCreationOptions;
use Ramsey\Uuid\Uuid;

class DuelManager
{
    use SingletonTrait;

    private array $matches = [];
    private Loader $plugin;

    public function __construct(Loader $plugin)
    {
        $this->plugin = $plugin;
        self::$instance = $this;
    }

    public function createMatch(HorizonPlayer $player1, HorizonPlayer $player2): void
    {
        $worldName = "Duel-" . Uuid::uuid4();
        $player1->getInventory()->clearAll();
        $player2->getInventory()->clearAll();
        $creationOptions = new WorldCreationOptions();
        $creationOptions->setGeneratorClass(Flat::class);
        $this->plugin->getServer()->getWorldManager()->generateWorld($worldName, $creationOptions);
        $this->addMatch($worldName, new DuelTask($this->plugin, $worldName, $player1, $player2));
        $player1->setDueling(true);
        $player2->setDueling(true);
    }

    #[Pure] public function addMatch(string $name, DuelTask $task): void
    {
        $this->getMatches()[$name] = $task;
    }

    public function getMatches(): array
    {
        return $this->matches;
    }

    public function stopMatch(string $name)
    {
        $this->removeMatch($name);
        if (Server::getInstance()->getWorldManager()->isWorldLoaded($name)) {
            Server::getInstance()->getWorldManager()->unloadWorld(Server::getInstance()->getWorldManager()->getWorldByName($name));
        }
        Loader::getArenaUtils()->deleteDir($this->plugin->getServer()->getDataPath() . "worlds/$name");
    }

    public function removeMatch($name): void
    {
        if ($this->isMatch($name)) {
            unset($this->matches[$name]);
        }
    }

    #[Pure] public function isMatch($name): bool
    {
        return isset($this->getMatches()[$name]);
    }
}