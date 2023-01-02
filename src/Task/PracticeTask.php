<?php

declare(strict_types=1);

namespace Nayuki\Task;

use Nayuki\Duel\Duel;
use Nayuki\Misc\AbstractTask;
use Nayuki\Misc\ParticleOffsetDisplayer;
use Nayuki\PracticeCore;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use pocketmine\world\particle\FlameParticle;

class PracticeTask extends AbstractTask
{
    /**
     * @param int $tick
     * @return void
     */
    public function onUpdate(int $tick): void
    {
        foreach (PracticeCore::getCaches()->RunningDuel as $duel) {
            if ($duel instanceof Duel) {
                $duel->update($tick);
            }
        }
        foreach (PracticeCore::getPracticeUtils()->getPlayerSession() as $session) {
            $player = $session->getPlayer();
            if ($session->loadedData && $player->isConnected()) {
                $session->updateScoreTag();
                if ($tick % 20 === 0) {
                    $session->updateNameTag();
                    $session->updateScoreboard();
                    if ($player->getWorld() === Server::getInstance()->getWorldManager()->getDefaultWorld()) {
                        ParticleOffsetDisplayer::display($player, new FlameParticle());
                    }
                    if ($session->isCombat()) {
                        $session->CombatTime--;
                        if ($session->CombatTime <= 0) {
                            $session->setCombat(false);
                            $player->sendMessage(PracticeCore::getPrefixCore() . TextFormat::RED . 'You are no longer in combat.');
                            $session->BoxingPoint = 0;
                            $session->setOpponent(null);
                        }
                    }
                }
            } elseif (!$session->loadedData && !$player->isOnline()) {
                PracticeCore::getSessionManager()::removeSession($player);
            }
        }
    }
}
