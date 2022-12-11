<?php

namespace Kuu\Task;

use Kuu\Players\PlayerSession;
use Kuu\PracticeCore;
use pocketmine\player\Player;
use pocketmine\scheduler\CancelTaskException;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

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
        $player->sendMessage(PracticeCore::getPrefixCore() . TextFormat::RED . "You can't use pearl for 10 seconds.");
    }

    /**
     * @return void
     * @throws CancelTaskException
     */
    public function onRun(): void
    {
        if ($this->player->isConnected()) {
            if ($this->session->PearlCooldown > 1 && $this->player->getWorld() !== Server::getInstance()->getWorldManager()->getDefaultWorld()) {
                $percent = (float)($this->session->PearlCooldown / 10);
                $this->player->getXpManager()->setXpProgress($percent);
                $this->session->PearlCooldown--;
            } else {
                $this->player->getXpManager()->setXpProgress(0.0);
                $this->session->PearlCooldown = 0;
                $this->player->sendMessage(PracticeCore::getPrefixCore() . TextFormat::GREEN . 'You can now use pearl.');
                throw new CancelTaskException();
            }
        } else {
            throw new CancelTaskException();
        }
    }
}
