<?php

namespace Nayuki\Utils;

final class ArrayUtils
{
    /**
     * @param array $array
     * @param mixed $n
     * @return int|string|null
     * Function to get the nth key of an associative array
     */
    public static function array_key_nth(array $array, mixed $n): int|string|null
    {
        $keys = array_keys($array);
        if (isset($keys[$n])) {
            return $keys[$n];
        }
        return null;
    }
}
