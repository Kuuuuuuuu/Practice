<?php

namespace Kuu\Arena;

use Kuu\Entity\PracticeBot;
use Kuu\PracticeCore;
use Kuu\PracticePlayer;
use Kuu\Task\PracticeTask;
use Kuu\Utils\Kits\KitManager;
use pocketmine\entity\Location;
use pocketmine\player\GameMode;
use pocketmine\Server;
use pocketmine\world\Position;
use pocketmine\world\World;
use pocketmine\world\WorldException;

class BotDuelFactory extends DuelFactoryBase
{
    private int $time = 903;
    private PracticePlayer $player1;
    private ?PracticeBot $player2;
    private World $level;
    private KitManager $kit;
    private bool $ended = false;
    private string $mode;

    public function __construct(string $name, PracticePlayer $player1, KitManager $kit, string $mode)
    {
        $world = Server::getInstance()->getWorldManager()->getWorldByName($name);
        if ($world === null) {
            throw new WorldException('World does not exist');
        }
        if (PracticeCore::getCoreTask() instanceof PracticeTask) {
            PracticeCore::getCoreTask()?->addDuelTask($name, $this);
        }
        $this->level = $world;
        $this->player1 = $player1;
        $this->player2 = null;
        $this->kit = $kit;
        $this->mode = $mode;
    }

    public function update(): void
    {
        if (!$this->player1->isOnline() || !$this->player1->isDueling()) {
            $this->onEnd();
        }
        if ($this->player2 instanceof PracticeBot) {
            if ($this->player2?->pearlcooldown !== 0) {
                $this->player2->pearlcooldown--;
            }
            if (!$this->player2?->isAlive() || $this->player2?->isClosed()) {
                $this->onEnd($this->player1);
            }
        }
        switch ($this->time) {
            case 903:
                if ($this->player1->isOnline()) {
                    $this->player1->setImmobile();
                    $this->player1->setGamemode(GameMode::SURVIVAL());
                    $this->player1->sendTitle('§d3', '', 1, 3, 1);
                    $this->player1->getArmorInventory()->setContents($this->kit->getArmorItems());
                    $this->player1->getInventory()->setContents($this->kit->getInventoryItems());
                    PracticeCore::getInstance()->getPracticeUtils()->playSound('random.click', $this->player1);
                }
                $this->level->orderChunkPopulation(15 >> 4, 40 >> 4, null)->onCompletion(function (): void {
                    $this->player1->teleport(new Position(15, 4, 40, $this->level));
                }, static function (): void {
                });
                break;
            case 902:
                if ($this->player1->isOnline()) {
                    $this->player1->setCurrentKit(null);
                    $this->player1->sendTitle('§d2', '', 1, 3, 1);
                    PracticeCore::getInstance()->getPracticeUtils()->playSound('random.click', $this->player1);
                }
                break;
            case 901:
                if ($this->player1->isOnline()) {
                    $this->player1->sendTitle('§d1', '', 1, 3, 1);
                    PracticeCore::getInstance()->getPracticeUtils()->playSound('random.click', $this->player1);
                }
                break;
            case 900:
                if ($this->player1->isOnline()) {
                    $this->player1->sendTitle('§dFight!', '', 1, 3, 1);
                    PracticeCore::getInstance()->getPracticeUtils()->playSound('random.anvil_use', $this->player1);
                    $this->player1->setImmobile(false);
                    $this->level->orderChunkPopulation(15 >> 4, 10 >> 4, null)->onCompletion(function (): void {
                        $this->player2 = new PracticeBot(new Location(15, 4, 10, Server::getInstance()->getWorldManager()->getWorldByName($this->level->getFolderName()), 0, 0), $this->player1->getSkin(), null, $this->player1->getName(), $this->mode);
                    }, static function (): void {
                    });
                }
                break;
            case 0:
                $this->onEnd();
                break;
        }
        $this->time--;
    }

    public function onEnd($playerLeft = null): void
    {
        if (!$this->ended) {
            $loserMessage = '';
            $winnerMessage = '';
            if ($playerLeft instanceof PracticePlayer) {
                $winnerMessage = '§aWinner: §f' . ($this->player1->getName() ?? 'None');
                $loserMessage = '§cLoser: §fFistBot';
            } elseif ($playerLeft === null) {
                $winnerMessage = '§aWinner: §fFistBot';
                $loserMessage = '§cLoser: §f' . ($this->player1->getName() ?? 'None');
            }
            if ($this->player1->isOnline()) {
                $this->player1->sendMessage('§f-----------------------');
                $this->player1->sendMessage($winnerMessage);
                $this->player1->sendMessage($loserMessage);
                $this->player1->sendMessage('§f-----------------------');
                $this->player1->setDueling(false);
                $this->player1->setCurrentKit(null);
                $this->player1->teleport(Server::getInstance()->getWorldManager()->getDefaultWorld()?->getSafeSpawn(), 0, 0);
                PracticeCore::getPracticeUtils()->GiveLobbyItem($this->player1);
                PracticeCore::getScoreboardManager()->sb($this->player1);
                $this->player1->setHealth(20);
            }
        }
        $this->ended = true;
        PracticeCore::getDuelManager()->stopMatch($this->level->getFolderName());
    }
}