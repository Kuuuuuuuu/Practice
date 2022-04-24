<?php

declare(strict_types=1);

namespace Kuu\Utils\Kits;

abstract class KitManager
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
}