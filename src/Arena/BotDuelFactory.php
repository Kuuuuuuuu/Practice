<?php

namespace Kuu\Arena;

use Kuu\Entity\FistBot;
use Kuu\Loader;
use Kuu\NeptunePlayer;
use Kuu\Task\NeptuneTask;
use pocketmine\entity\Location;
use pocketmine\player\GameMode;
use pocketmine\Server;
use pocketmine\world\Position;
use pocketmine\world\World;
use pocketmine\world\WorldException;

class BotDuelFactory
{
    private int $time = 903;
    private NeptunePlayer $player1;
    private ?FistBot $player2;
    private World $level;
    private bool $ended = false;

    public function __construct(string $name, NeptunePlayer $player1)
    {
        $world = Server::getInstance()->getWorldManager()->getWorldByName($name);
        if ($world === null) {
            throw new WorldException('World does not exist');
        }
        if (Loader::getCoreTask() instanceof NeptuneTask) {
            Loader::getCoreTask()?->addBotDuelTask($name, $this);
        }
        $this->level = $world;
        $this->player1 = $player1;
        $this->player2 = null;
    }

    public function update(): void
    {
        if (!$this->player1->isOnline() || !$this->player1->isDueling()) {
            $this->onEnd();
        }
        if ($this->player2 instanceof FistBot) {
            if (!$this->player2->isAlive() || $this->player2->isClosed()) {
                $this->onEnd($this->player1);
            }
        }
        switch ($this->time) {
            case 903:
                if ($this->player1->isOnline()) {
                    $this->player1->setImmobile();
                    $this->player1->setGamemode(GameMode::SURVIVAL());
                    $this->player1->sendTitle('§d3', '', 1, 3, 1);
                    Loader::getInstance()->getArenaUtils()->playSound('random.click', $this->player1);
                }
                $this->level->orderChunkPopulation(15 >> 4, 40 >> 4, null)->onCompletion(function (): void {
                    $this->player1->teleport(new Position(15, 4, 40, $this->level));
                }, static function (): void {
                });
                $this->level->orderChunkPopulation(15 >> 4, 10 >> 4, null)->onCompletion(function (): void {
                    $this->player2 = new FistBot(new Location(15, 4, 10, Server::getInstance()->getWorldManager()->getWorldByName($this->level->getFolderName()), 0, 0), $this->player1->getSkin(), null, $this->player1->getName());
                }, static function (): void {
                });
                break;
            case 902:
                if ($this->player1->isOnline()) {
                    $this->player1->setCurrentKit(null);
                    $this->player1->sendTitle('§d2', '', 1, 3, 1);
                    Loader::getInstance()->getArenaUtils()->playSound('random.click', $this->player1);
                }
                break;
            case 901:
                if ($this->player1->isOnline()) {
                    $this->player1->sendTitle('§d1', '', 1, 3, 1);
                    Loader::getInstance()->getArenaUtils()->playSound('random.click', $this->player1);
                }
                break;
            case 900:
                if ($this->player1->isOnline()) {
                    $this->player1->sendTitle('§dFight!', '', 1, 3, 1);
                    Loader::getInstance()->getArenaUtils()->playSound('random.anvil_use', $this->player1);
                }
                foreach ($this->getPlayers() as $p) {
                    $p->setImmobile(false);
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
            if ($this->player1->isOnline()) {
                $this->player1->sendMessage('§f-----------------------');
            }
            if ($playerLeft instanceof NeptunePlayer) {
                $winnerMessage = '§aWinner: §f' . ($this->player1->getName() ?? 'None');
                $loserMessage = '§cLoser: §fFistBot';
            } else if ($playerLeft === null) {
                $winnerMessage = '§aWinner: §fFistBot';
                $loserMessage = '§cLoser: §f' . ($this->player1->getName() ?? 'None');
            }
            if ($this->player2->isAlive() || !$this->player2->isClosed()) {
                $this->player2->close();
            }
            if ($this->player1->isOnline()) {
                $this->player1->sendMessage($winnerMessage);
                $this->player1->sendMessage($loserMessage);
                $this->player1->sendMessage('§f-----------------------');
                $this->player1->setDueling(false);
                $this->player1->setCurrentKit(null);
                $this->player1->teleport(Server::getInstance()->getWorldManager()->getDefaultWorld()?->getSafeSpawn(), 0, 0);
                Loader::getArenaUtils()->GiveItem($this->player1);
                Loader::getScoreboardManager()->sb($this->player1);
                $this->player1->setHealth(20);
            }
        }
        $this->ended = true;
        Loader::getBotDuelManager()->stopMatch($this->level->getFolderName());
    }

    public function getPlayers(): array
    {
        return [$this->player1, $this->player2];
    }
}