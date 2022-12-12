<?php

namespace Nayuki\Duel;

use Nayuki\Game\Generator\DuelGenerator;
use Nayuki\Game\Kits\Kit;
use Nayuki\PracticeCore;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\world\World;
use pocketmine\world\WorldCreationOptions;
use Ramsey\Uuid\Uuid;

final class DuelManager
{
    /**
     * @param Player $player1
     * @param Player $player2
     * @param Kit $kit
     * @return void
     */
    public function createMatch(Player $player1, Player $player2, Kit $kit): void
    {
        $worldName = 'Duel-' . $player1->getName() . '-' . $player2->getName() . ' - ' . Uuid::uuid4();
        $world = new WorldCreationOptions();
        $world->setGeneratorClass(DuelGenerator::class);
        $world->setSeed(0);
        $world->setSpawnPosition(new Vector3(0, 100, 0));
        Server::getInstance()->getWorldManager()->generateWorld($worldName, $world);
        foreach ([$player1, $player2] as $player) {
            $session = PracticeCore::getPlayerSession()::getSession($player);
            $player->getInventory()->clearAll();
            $session->isDueling = true;
        }
        $this->addMatch($worldName, new Duel($worldName, $player1, $player2, $kit));
    }

    /**
     * @param string $name
     * @param Duel $task
     * @return void
     */
    public function addMatch(string $name, Duel $task): void
    {
        PracticeCore::getCaches()->RunningDuel[$name] = $task;
    }

    /**
     * @param string $name
     * @return void
     */
    public function stopMatch(string $name): void
    {
        $world = Server::getInstance()->getWorldManager()->getWorldByName($name);
        if ($world instanceof World) {
            if ($world->isLoaded()) {
                Server::getInstance()->getWorldManager()->unloadWorld($world);
            }
            PracticeCore::getPracticeUtils()->deleteDir(PracticeCore::getInstance()->getServer()->getDataPath() . "worlds/$name");
            if (isset(PracticeCore::getCaches()->RunningDuel[$name])) {
                unset(PracticeCore::getCaches()->RunningDuel[$name]);
            }
        }
    }
}
