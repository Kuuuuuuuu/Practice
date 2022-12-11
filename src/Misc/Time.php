<?php

namespace Kuu\Misc;

use function gmdate;

class Time
{
    /**
     * @param int $time
     * @return string
     */
    public static function calculateTime(int $time): string
    {
        return gmdate('i:s', $time);
    }
}
