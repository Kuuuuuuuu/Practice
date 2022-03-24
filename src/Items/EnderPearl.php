<?php

namespace Kohaku\Core\Items;

use JetBrains\PhpStorm\Pure;
use Kohaku\Core\Loader;
use pocketmine\item\EnderPearl as ItemEnderPearl;
use pocketmine\item\ItemIdentifier;

class EnderPearl extends ItemEnderPearl
{

    public function __construct(ItemIdentifier $id, string $name)
    {
        parent::__construct($id, $name);
    }

    #[Pure] public function getThrowForce(): float
    {
        return Loader::getInstance()->EnderPearlForce;
    }
}