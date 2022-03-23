<?php

namespace Kohaku\Core\Items;

use pocketmine\item\EnderPearl as ItemEnderPearl;
use pocketmine\item\ItemIdentifier;

class EnderPearl extends ItemEnderPearl
{

    public function __construct(ItemIdentifier $id, string $name)
    {
        parent::__construct($id, $name);
    }

    public function getThrowForce(): float
    {
        return 2.0;
    }
}