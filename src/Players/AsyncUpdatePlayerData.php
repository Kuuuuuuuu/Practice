<?php

declare(strict_types=1);

namespace Kuu\Players;

use pocketmine\scheduler\AsyncTask;

class AsyncUpdatePlayerData extends AsyncTask
{
    private array $data;
    private $name;
    private string $path;

    public function __construct(string $name, string $path, array $values = [])
    {
        $this->name = $name;
        $this->path = $path;
        $this->data = $values;
    }

    public function onRun(): void
    {
        if (!file_exists($this->path)) {
            return;
        }
        $info = $this->data;
        $parsed = yaml_parse_file($this->path);
        $keys = array_keys($info);
        foreach ($keys as $key) {
            if (isset($parsed[$key], $info[$key])) {
                $infoValue = $info[$key];
                if (is_array($infoValue)) {
                    $parsedValue = $parsed[$key];
                    $parsedKeys = array_keys($parsedValue);
                    foreach ($parsedKeys as $pKey) {
                        if (isset($parsedValue[$pKey]) && !isset($infoValue[$pKey])) {
                            $infoValue[$pKey] = $parsedValue[$pKey];
                        }
                    }
                }
                $parsed[$key] = $infoValue;
            }
        }
        yaml_emit_file($this->path, $parsed);
    }
}