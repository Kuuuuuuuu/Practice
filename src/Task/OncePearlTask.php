<?php

namespace Kuu\Task;

use Kuu\PracticeCore;
use Kuu\PracticePlayer;
use pocketmine\scheduler\CancelTaskException;
use pocketmine\scheduler\Task;

class OncePearlTask extends Task
{
    /** @var PracticePlayer  */
    private PracticePlayer $player;

    public function __construct(PracticePlayer $player)
    {
        $this->player = $player;
        $player->PearlCooldown = 10;
        $this->player->sendMessage(PracticeCore::getPrefixCore() . 'EnderPearl Cooldown Increased');
    }

    /**
     * @return void
     * @throws CancelTaskException
     */
    public function onRun(): void
    {
        if ($this->player->PearlCooldown >= 0) {
            $percent = (float)($this->player->PearlCooldown / 10);
            $this->player->getXpManager()->setXpProgress($percent);
            $this->player->PearlCooldown--;
        } else {
            $this->player->sendMessage(PracticeCore::getPrefixCore() . 'EnderPearl Cooldown Reduced');
            throw new CancelTaskException();
        }
    }
}