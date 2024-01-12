<?php

declare(strict_types=1);

namespace Nayuki\Utils;

use Nayuki\PracticeConfig;
use Nayuki\PracticeCore;
use pocketmine\player\Player;
use function array_unshift;
use function count;
use function microtime;

final class ClickHandler
{
    /** @var array */
    public static array $ClickData = [];

    /**
     * @param Player $player
     * @return void
     */
    public function addClick(Player $player): void
    {
        $session = PracticeCore::getSessionManager()->getSession($player);
        $playerCps = $this->getClicks($player);

        $var = self::$ClickData[spl_object_hash($player)] ?? [];
        $clickData = &$var;

        if ($session->CpsCounterEnabled) {
            $player->sendTip(PracticeConfig::COLOR . 'CPS: ' . PracticeConfig::COLOR . $playerCps);
        }

        array_unshift($clickData, microtime(true));

        if (count($clickData) > 50) {
            array_pop($clickData);
        }
    }


    /**
     * @param Player $player
     * @return int
     */
    public function getClicks(Player $player): int
    {
        $clickData = self::$ClickData[spl_object_hash($player)] ?? [];

        if (count($clickData) === 0) {
            return 0;
        }

        $currentTime = microtime(true);

        $recentClicks = array_filter($clickData, static fn($clickTime) => ($currentTime - $clickTime) <= 1.0);

        return count($recentClicks);
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
