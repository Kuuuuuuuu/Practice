<?php

declare(strict_types=1);

namespace Kohaku\Core\Utils\Kits;

abstract class KitManager
{
    private string $kitName;

    public function __construct(string $kitName)
    {
        $this->kitName = $kitName;
    }

    public function getName(): string
    {
        return $this->kitName ?? "none";
    }

    public abstract function getArmorItems(): array;

    public abstract function getInventoryItems(): array;
}