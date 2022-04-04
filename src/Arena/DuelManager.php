<?php

namespace Kohaku\Arena;

use JetBrains\PhpStorm\Pure;
use Kohaku\NeptunePlayer;
use Kohaku\Loader;
use Kohaku\Task\DuelTask;
use Kohaku\Utils\DuelGenerator;
use Kohaku\Utils\Kits\KitManager;
use pocketmine\Server;
use pocketmine\utils\SingletonTrait;
use pocketmine\world\WorldCreationOptions;
use Ramsey\Uuid\Uuid;

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
        $worldName = "Duel-" . Uuid::uuid4();
        $player1->getInventory()->clearAll();
        $player2->getInventory()->clearAll();
        $world = new WorldCreationOptions();
        $world->setGeneratorClass(DuelGenerator::class);
        Server::getInstance()->getWorldManager()->generateWorld($worldName, $world);
        $this->addMatch($worldName, new DuelTask($worldName, $player1, $player2, $kit));
        $player1->setDueling(true);
        $player2->setDueling(true);
        foreach ([$player1, $player2] as $player) {
            Loader::getScoreboardManager()->sb2($player);
        }
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
        Loader::getArenaUtils()->deleteDir(Loader::getInstance()->getServer()->getDataPath() . "worlds/$name");
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