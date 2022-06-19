<?php

declare(strict_types=1);

namespace Kuu\Utils;

use Kuu\PracticeCore;
use pocketmine\player\Player;
use function array_filter;
use function array_unshift;
use function count;
use function microtime;
use function round;

class ClickHandler
{

    private array $clicksData = [];

    public function addClick(Player $p): void
    {
        if (!isset($this->clicksData[mb_strtolower($p->getName())])) {
            $this->initPlayerClickData($p);
        } else {
            $p->sendTip('§dCPS: §f' . PracticeCore::getClickHandler()->getClicks($p));
            array_unshift($this->clicksData[mb_strtolower($p->getName())], microtime(true));
            if (count($this->clicksData[mb_strtolower($p->getName())]) >= 50) {
                array_pop($this->clicksData[mb_strtolower($p->getName())]);
            }
        }
    }

    public function initPlayerClickData(Player $p): void
    {
        $this->clicksData[mb_strtolower($p->getName())] = [];
    }

    public function getClicks(Player $player): float
    {
        if (!isset($this->clicksData[mb_strtolower($player->getName())]) || empty($this->clicksData[mb_strtolower($player->getName())])) {
            return 0;
        }
        $ct = microtime(true);
        return round(count(array_filter($this->clicksData[mb_strtolower($player->getName())], static function (float $t) use ($ct): bool {
                return ($ct - $t) <= 1.0;
            })) / 1.0, 1);
    }

    public function removePlayerClickData(Player $p): void
    {
        unset($this->clicksData[mb_strtolower($p->getName())]);
    }
}