<?php

declare(strict_types=1);

namespace Nayuki\Utils;

use Nayuki\PracticeCore;
use pocketmine\player\Player;

use function array_unshift;
use function count;
use function microtime;

class ClickHandler
{
    /** @var array */
    public static array $ClickData = [];

    /**
     * @param Player $p
     * @return void
     */
    public function addClick(Player $p): void
    {
        $session = PracticeCore::getSessionManager()->getSession($p);
        if (!isset(self::$ClickData[spl_object_hash($p)])) {
            self::$ClickData[spl_object_hash($p)] = [];
        }
        $clickData = &self::$ClickData[spl_object_hash($p)];
        if ($session->CpsCounterEnabled) {
            $p->sendTip('§bCPS: §f' . $this->getClicks($p));
        }
        array_unshift($clickData, microtime(true));
        if (count($clickData) >= 50) {
            array_pop($clickData);
        }
    }

    /**
     * @param Player $player
     * @return float
     */
    public function getClicks(Player $player): float
    {
        $clickData = self::$ClickData[spl_object_hash($player)] ?? [];
        if (count($clickData) === 0) {
            return 0.0;
        }
        $currentTime = microtime(true);
        $recentClickCount = array_reduce($clickData, static function (int $count, float $clickTime) use ($currentTime): int {
            return ($currentTime - $clickTime) <= 1.0 ? $count + 1 : $count;
        }, 0);
        return round($recentClickCount);
    }

    /**
     * @param Player $p
     * @return void
     */
    public function removePlayerClickData(Player $p): void
    {
        unset(self::$ClickData[spl_object_hash($p)]);
    }
}
