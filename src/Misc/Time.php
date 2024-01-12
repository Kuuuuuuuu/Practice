<?php

namespace Nayuki\Misc;

use function gmdate;

final class Time
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
