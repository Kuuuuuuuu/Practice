<?php

declare(strict_types=1);

namespace Kuu\Misc;

class Time
{
    public static function calculateTime(int $time): string
    {
        return gmdate('i:s', $time);
    }
}