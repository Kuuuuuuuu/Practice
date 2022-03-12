<?php

declare(strict_types=1);

namespace Kohaku\Core\Task;

use Kohaku\Core\Loader;
use pocketmine\entity\object\ItemEntity;
use pocketmine\entity\projectile\Arrow;
use pocketmine\scheduler\Task;
use pocketmine\Server;

class ClearLag extends Task
{

    private int $count = 0;

    public function onRun(): void
    {
        foreach (Server::getInstance()->getWorldManager()->getWorlds() as $level) {
            foreach ($level->getEntities() as $entity) {
                if ($entity instanceof ItemEntity or $entity instanceof Arrow) {
                    if ($entity->onGround and $entity->isAlive()) {
                        $this->count = count($level->getEntities());
                        $entity->flagForDespawn();
                        $entity->close();
                    }
                }
            }
        }
        Server::getInstance()->broadcastMessage(Loader::getInstance()->getPrefixCore() . "§eCleared Entity: §7" . $this->count);
    }
}
