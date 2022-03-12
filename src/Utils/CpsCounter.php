<?php

declare(strict_types=1);

namespace Kohaku\Core\Utils;

use pocketmine\player\Player;
use pocketmine\Server;
use function array_filter;
use function array_unshift;
use function count;
use function microtime;
use function round;

class CpsCounter
{

    public array $clicksData = [];

    public function initPlayerClickData(Player $p): void
    {
        $this->clicksData[mb_strtolower($p->getName())] = [];
    }

    public function addClick(Player $p): void
    {
        try {
            array_unshift($this->clicksData[mb_strtolower($p->getName())], microtime(true));
        } catch (\Exception $e) {
            Server::getInstance()->getLogger()->error($e);
        }
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