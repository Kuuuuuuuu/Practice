<?php

declare(strict_types=1);

namespace Kohaku\Core\Task;

use pocketmine\entity\object\ItemEntity;
use pocketmine\entity\projectile\Arrow;
use pocketmine\scheduler\Task;
use pocketmine\Server;

class ClearLag extends Task
{

    public function onRun(): void
    {
        foreach (Server::getInstance()->getWorldManager()->getWorlds() as $level) {
            foreach ($level->getEntities() as $entity) {
                if ($entity instanceof ItemEntity or $entity instanceof Arrow) {
                    if ($entity->onGround and $entity->isAlive()) {
                        $entity->flagForDespawn();
                        $entity->close();
                    }
                }
            }
        }
    }
}
