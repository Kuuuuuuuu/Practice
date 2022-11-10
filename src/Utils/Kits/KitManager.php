<?php

declare(strict_types=1);

namespace Kuu\Utils\Kits;

abstract class KitManager
{
    /** @var string */
    private string $kitName;

    public function __construct(string $kitName)
    {
        $this->kitName = $kitName;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->kitName ?? 'none';
    }

    /**
     * @return array
     */
    abstract public function getArmorItems(): array;

    /**
     * @return array
     */
    abstract public function getInventoryItems(): array;
}