<?php

namespace Nayuki\Task;

use Nayuki\Misc\AbstractTask;
use Nayuki\PracticeCore;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

final class OnceRestartTask extends AbstractTask
{
    /** @var int */
    private int $time;

    public function __construct(int $time)
    {
        parent::__construct(20);
        $this->time = $time;
        PracticeCore::$isRestarting = true;
    }

    /**
     * @param int $tick
     * @return void
     */
    protected function onUpdate(int $tick): void
    {
        $restartWarningInterval = 5;

        $this->time--;

        if ($this->time <= 1) {
            foreach (Server::getInstance()->getOnlinePlayers() as $player) {
                $player->kick(TextFormat::RED . 'Server restarted');
            }

            PracticeCore::getInstance()->getServer()->shutdown();
        } elseif ($this->time % $restartWarningInterval === 0) {
            $restartMessage = PracticeCore::getPrefixCore() . TextFormat::RED . 'Server will restart in ' . TextFormat::YELLOW . $this->time . TextFormat::RED . ' seconds';
            Server::getInstance()->broadcastMessage($restartMessage);
        }
    }

}
