<?php

namespace Kohaku\Items;

use JetBrains\PhpStorm\Pure;
use Kohaku\ConfigCore;
use Kohaku\Entity\EnderPearlEntity;
use Kohaku\Loader;
use pocketmine\entity\Location;
use pocketmine\entity\projectile\Throwable;
use pocketmine\item\EnderPearl as ItemEnderPearl;
use pocketmine\item\ItemIdentifier;
use pocketmine\player\Player;

class EnderPearl extends ItemEnderPearl
{

    public function __construct(ItemIdentifier $id, string $name)
    {
        parent::__construct($id, $name);
    }

    #[Pure] public function getThrowForce(): float
    {
        return ConfigCore::PearlForce;
    }

    protected function createEntity(Location $location, Player $thrower): Throwable
    {
        return new EnderPearlEntity($location, $thrower);
    }
}