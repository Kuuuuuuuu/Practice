<?php

declare(strict_types=1);

namespace Kohaku\Core\Utils;

use pocketmine\player\Player;
use function array_filter;
use function array_unshift;
use function count;
use function microtime;
use function round;

class CpsCounter
{

    private array $clicksData = [];

    public function addClick(Player $p): void
    {
        if (!isset($this->clicksData[mb_strtolower($p->getName())])) {
            $this->initPlayerClickData($p);
        } else {
            array_unshift($this->clicksData[mb_strtolower($p->getName())], microtime(true));
        }
    }

    public function initPlayerClickData(Player $p): void
    {
        $this->clicksData[mb_strtolower($p->getName())] = [];
    }

    public function getClicks(Player $player, float $deltaTime = 1.0, int $roundPrecision = 1): float
    {
        if (!isset($this->clicksData[mb_strtolower($player->getName())]) || empty($this->clicksData[mb_strtolower($player->getName())])) {
            return 0;
        }
        $ct = microtime(true);
        return round(count(array_filter($this->clicksData[mb_strtolower($player->getName())], static function (float $t) use ($deltaTime, $ct): bool {
                return ($ct - $t) <= $deltaTime;
            })) / $deltaTime, $roundPrecision);
    }

    public function removePlayerClickData(Player $p): void
    {
        unset($this->clicksData[mb_strtolower($p->getName())]);
    }
}