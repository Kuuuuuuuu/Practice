<?php

declare(strict_types=1);

namespace Kuu\Task;

use Kuu\Misc\AbstractTask;
use Kuu\Misc\ParticleDisplayer;
use Kuu\Players\PlayerSession;
use Kuu\PracticeConfig;
use Kuu\PracticeCore;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

use pocketmine\world\particle\FlameParticle;
use function array_filter;

class PracticeTask extends AbstractTask
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param int $tick
     * @return void
     */
    public function onUpdate(int $tick): void
    {
        foreach (array_filter(PracticeCore::getPracticeUtils()->getPlayerInSession(), static fn (Player $player): bool => $player->isConnected() && $player->spawned) as $player) {
            $session = PracticeCore::getPlayerSession()::getSession($player);
            if ($session->loadedData) {
                $this->updateScoreTag($player);
                if ($tick % 20 === 0) {
                    if ($player->getWorld()  === Server::getInstance()->getWorldManager()->getDefaultWorld()) {
                        ParticleDisplayer::display($player, new FlameParticle());
                    }
                    $this->updateNameTag($player, $session);
                    $this->updateScoreboard($player, $session);
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
            }
        }
    }

    /**
     * @param Player $player
     * @return void
     */
    private function updateScoreTag(Player $player): void
    {
        $ping = $player->getNetworkSession()->getPing();
        $cps = PracticeCore::getClickHandler()->getClicks($player);
        $player->setScoreTag(PracticeConfig::COLOR . $ping . ' §fMS §f| ' . PracticeConfig::COLOR . $cps . ' §fCPS');
    }

    /**
     * @param Player $player
     * @param PlayerSession $session
     * @return void
     */
    private function updateNameTag(Player $player, PlayerSession $session): void
    {
        $Tag = '§b' . $player->getDisplayName();
        if ($session->getCustomTag() !== '') {
            $Tag = '§f[' . $session->getCustomTag() . '§f] §b' . $player->getDisplayName();
        }
        $player->setNameTag($Tag);
    }

    /**
     * @param Player $player
     * @param PlayerSession $session
     * @return void
     */
    private function updateScoreboard(Player $player, PlayerSession $session): void
    {
        if ($session->ScoreboardEnabled) {
            if ($player->getWorld() === Server::getInstance()->getWorldManager()->getDefaultWorld()) {
                PracticeCore::getInstance()->getScoreboardManager()->setLobbyScoreboard($player);
            } elseif ($player->getWorld() === Server::getInstance()->getWorldManager()->getWorldByName(PracticeCore::getArenaFactory()->getBoxingArena())) {
                PracticeCore::getInstance()->getScoreboardManager()->setBoxingScoreboard($player);
            } else {
                PracticeCore::getInstance()->getScoreboardManager()->setArenaScoreboard($player);
            }
        } else {
            PracticeCore::getScoreboardUtils()->remove($player);
        }
    }
}
