<?php

declare(strict_types=1);

namespace Kuu\Entity\Leaderboard;

use Kuu\PracticeCore;

class DeathLeaderboard extends BaseLeaderboard
{

    public function onUpdate(int $currentTick): bool
    {
        $subtitle = '';
        $tops = PracticeCore::getCaches()->DeathLeaderboard;
        if (count($tops) > 0) {
            arsort($tops);
            $i = 1;
            foreach ($tops as $name => $wins) {
                $subtitle .= ' §7[§d# ' . $i . '§7]. §f' . $name . '§7: §f' . $wins . "§e Deaths\n";
                if ($i >= 10) {
                    break;
                }
                ++$i;
            }
        }
        $this->setNameTag("§dMost Death Players\n" . $subtitle);
        $this->setNameTagAlwaysVisible();
        return parent::onUpdate($currentTick);
    }
}