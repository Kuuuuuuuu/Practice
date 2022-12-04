<?php

namespace Kuu\Task;

use Kuu\Players\PlayerSession;
use Kuu\PracticeCore;
use pocketmine\player\Player;
use pocketmine\scheduler\CancelTaskException;
use pocketmine\scheduler\Task;

class OncePearlTask extends Task
{
    /** @var Player */
    private Player $player;
    /** @var PlayerSession */
    private PlayerSession $session;

    public function __construct(Player $player)
    {
        $this->session = PracticeCore::getPlayerSession()::getSession($player);
        $this->player = $player;
        $this->session->PearlCooldown = 10;
        $player->sendTip(PracticeCore::getPrefixCore() . 'EnderPearl Cooldown Increased');
    }

    /**
     * @return void
     * @throws CancelTaskException
     */
    public function onRun(): void
    {
        if ($this->session->PearlCooldown >= 0) {
            $percent = (float)($this->session->PearlCooldown / 10);
            $this->player->getXpManager()->setXpProgress($percent);
            $this->session->PearlCooldown--;
        } else {
            $this->player->sendTip(PracticeCore::getPrefixCore() . 'EnderPearl Cooldown Reduced');
            throw new CancelTaskException();
        }
    }
}
