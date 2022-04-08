<?php

namespace Kohaku\Arena;

use JetBrains\PhpStorm\Pure;
use Kohaku\Loader;
use Kohaku\NeptunePlayer;
use Kohaku\Utils\DuelGenerator;
use Kohaku\Utils\Kits\KitManager;
use pocketmine\Server;
use pocketmine\utils\SingletonTrait;
use pocketmine\world\WorldCreationOptions;

class DuelManager
{
    use SingletonTrait;

    private array $matches = [];
    private Loader $plugin;

    public function __construct()
    {
        self::$instance = $this;
    }

    public function createMatch(NeptunePlayer $player1, NeptunePlayer $player2, KitManager $kit): void
    {
        $worldName = "Duel-" . $player1->getName() . "-" . $player2->getName();
        $world = new WorldCreationOptions();
        $world->setGeneratorClass(DuelGenerator::class);
        Server::getInstance()->getWorldManager()->generateWorld($worldName, $world);
        foreach ([$player1, $player2] as $player) {
            $player->getInventory()->clearAll();
            $player->setDueling(true);
        }
        $this->addMatch($worldName, new DuelFactory($worldName, $player1, $player2, $kit));
    }

    #[Pure] public function addMatch(string $name, DuelFactory $task): void
    {
        $this->getMatches()[$name] = $task;
    }

    public function getMatches(): array
    {
        return $this->matches;
    }

    public function stopMatch(string $name)
    {
        if (Server::getInstance()->getWorldManager()->isWorldLoaded($name)) {
            Server::getInstance()->getWorldManager()->unloadWorld(Server::getInstance()->getWorldManager()->getWorldByName($name));
        }
        Loader::getArenaUtils()->deleteDir(Loader::getInstance()->getServer()->getDataPath() . "worlds/$name");
        unset(Loader::getInstance()->CoreTask->DuelTask[$name]);
        if ($this->isMatch($name)) {
            unset($this->matches[$name]);
        }
    }

    #[Pure] public function isMatch($name): bool
    {
        return isset($this->getMatches()[$name]);
    }
}