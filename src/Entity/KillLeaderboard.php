<?php

declare(strict_types=1);

namespace Kuu\Entity;

use Kuu\PracticeCore;

class KillLeaderboard extends BaseLeaderboard
{

    public function onUpdate(int $currentTick): bool
    {
        $subtitle = '';
        $tops = PracticeCore::getCaches()->KillLeaderboard;
        if (count($tops) > 0) {
            arsort($tops);
            $i = 1;
            foreach ($tops as $name => $wins) {
                $subtitle .= ' §7[§d# ' . $i . '§7]. §f' . $name . '§7: §f' . $wins . "§e Kills\n";
                if ($i >= 10) {
                    break;
                }
                ++$i;
            }
        }
        $this->setNameTag("§dMost Kills Players\n" . $subtitle);
        $this->setNameTagAlwaysVisible();
        return parent::onUpdate($currentTick);
    }
}