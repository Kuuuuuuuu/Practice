<?php

namespace Nayuki\Task;

use Nayuki\Players\PlayerSession;
use Nayuki\PracticeCore;
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
        $this->session = PracticeCore::getSessionManager()->getSession($player);
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
                $this->player->getXpManager()->setXpAndProgress($this->session->PearlCooldown, $percent);
                $this->session->PearlCooldown--;
                return;
            }
            $this->player->getXpManager()->setXpAndProgress(0, 0);
            $this->session->PearlCooldown = 0;
            $this->player->sendMessage(PracticeCore::getPrefixCore() . TextFormat::GREEN . 'You can now use pearl.');
        }
        throw new CancelTaskException();
    }
}
