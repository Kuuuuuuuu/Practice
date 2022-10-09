<?php

declare(strict_types=1);

namespace Kuu\Entity\Leaderboard;

use Kuu\PracticeCore;

class ParkourLeaderboard extends BaseLeaderboard
{

    public function onUpdate(int $currentTick): bool
    {
        $subtitle = '';
        $tops = PracticeCore::getCaches()->ParkourLeaderboard;
        if (count($tops) > 0) {
            arsort($tops);
            $i = 1;
            foreach ($tops as $name => $wins) {
                $subtitle .= ' §7[§b# ' . $i . '§7]. §f' . $name . '§7: §f' . $wins . "§e Secs\n";
                if ($i >= 10) {
                    break;
                }
                ++$i;
            }
        }
        $this->setNameTag("§bMost Fastest Parkour Players\n" . $subtitle);
        $this->setNameTagAlwaysVisible();
        return parent::onUpdate($currentTick);
    }
}