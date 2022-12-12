<?php

declare(strict_types=1);

namespace Nayuki\Game\Kits;

use pocketmine\player\Player;

abstract class Kit
{
    private string $kitName;

    public function __construct(string $kitName)
    {
        $this->kitName = $kitName;
    }

    public function getName(): string
    {
        return $this->kitName ?? 'none';
    }

    abstract public function getArmorItems(): array;

    abstract public function getInventoryItems(): array;

    abstract public function setEffect(Player $player): void;
}
