<?php

declare(strict_types=1);

namespace Nayuki\Players;

use Nayuki\Game\Duel\Duel;
use Nayuki\Game\Duel\DuelBot;
use Nayuki\Game\Kits\Kit;
use Nayuki\PracticeConfig;
use Nayuki\PracticeCore;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

final class PlayerSession
{
    /** @var bool */
    public bool $ScoreboardEnabled = true;
    /** @var bool */
    public bool $CpsCounterEnabled = true;
    /** @var bool */
    public bool $isLightningKill = true;
    /** @var string|null */
    public ?string $Scoreboard = null;

    /** @var int */
    public int $kills = 0;
    /** @var int */
    public int $deaths = 0;
    /** @var int */
    public int $killStreak = 0;
    /** @var int */
    public int $CombatTime = 0;
    /** @var int */
    public int $BoxingPoint = 0;

    /** @var bool */
    public bool $loadedData = false;
    /** @var bool */
    public bool $isDueling = false;
    /** @var Kit|null */
    public ?Kit $DuelKit = null;
    /** @var Duel|DuelBot|null */
    public Duel|DuelBot|null $DuelClass = null;
    /** @var bool */
    public bool $isQueueing = false;
    /** @var bool */
    public bool $isCombat = false;
    /** @var bool */
    public bool $spectating = false;
    /** @var Duel|DuelBot|null */
    public Duel|DuelBot|null $spectatingDuel = null;

    /** @var string */
    public string $cape = '';
    /** @var string */
    public string $artifact = '';
    /** @var array */
    public array $purchasedArtifacts = [];
    /** @var int */
    public int $coins = 0;

    /** @var string|null */
    private ?string $Opponent = null;
    /** @var string */
    private string $customTag = '§aMember';
    /** @var Player */
    private Player $player;

    public function __construct(Player $player)
    {
        $this->player = $player;
    }

    /**
     * @return Player
     */
    public function getPlayer(): Player
    {
        return $this->player;
    }

    /**
     * @param array $data
     * @return void
     */
    public function loadData(array $data): void
    {
        foreach ($data as $key => $value) {
            switch ($key) {
                case 'scoreboard':
                    $this->ScoreboardEnabled = (bool)$value;
                    break;
                case 'cps':
                    $this->CpsCounterEnabled = (bool)$value;
                    break;
                case 'kills':
                    $this->kills = (int)$value;
                    break;
                case 'deaths':
                    $this->deaths = (int)$value;
                    break;
                case 'tag':
                    $this->customTag = (string)$value;
                    break;
                case 'killStreak':
                    $this->killStreak = (int)$value;
                    break;
                case 'cape':
                    $this->cape = (string)$value;
                    break;
                case 'artifact':
                    $this->artifact = (string)$value;
                    break;
                case 'purchasedArtifacts':
                    $this->purchasedArtifacts = (array)$value;
                    break;
                case 'coins':
                    $this->coins = (int)$value;
                    break;
                case 'lightning':
                    $this->isLightningKill = (bool)$value;
                    break;
            }
        }
        $this->loadedData = true;
    }

    /**
     * @return int
     */
    public function getStreak(): int
    {
        return $this->killStreak;
    }

    /**
     * @return int
     */
    public function getKills(): int
    {
        return $this->kills;
    }

    /**
     * @return int
     */
    public function getDeaths(): int
    {
        return $this->deaths;
    }

    /**
     * @return float|int
     */
    public function getKdr(): float|int
    {
        return ($this->deaths > 0) ? ($this->kills / $this->deaths) : 1;
    }

    /**
     * @return void
     */
    public function updateScoreTag(): void
    {
        $ping = $this->player->getNetworkSession()->getPing();
        $cps = PracticeCore::getClickHandler()->getClicks($this->player);
        $this->player->setScoreTag(PracticeConfig::COLOR . $ping . ' ' . TextFormat::WHITE . 'MS §f| ' . PracticeConfig::COLOR . $cps . ' ' . TextFormat::WHITE . 'CPS');
    }

    /**
     * @return void
     */
    public function updateNameTag(): void
    {
        $name = TextFormat::LIGHT_PURPLE . $this->player->getDisplayName();
        $tag = $this->getCustomTag();
        if ($tag !== '') {
            $name = $this->getCustomTag() . TextFormat::LIGHT_PURPLE . ' ' . $name;
        }
        $this->player->setNameTag($name);
    }

    /**
     * @return string
     */
    public function getCustomTag(): string
    {
        return $this->customTag;
    }

    /**
     * @param string $tag
     * @return void
     */
    public function setCustomTag(string $tag): void
    {
        $this->customTag = $tag;
    }

    /**
     * @return void
     */
    public function updateScoreboard(): void
    {
        $player = $this->player;
        $scoreboardUtils = PracticeCore::getScoreboardUtils();
        $scoreboardManager = PracticeCore::getInstance()->getScoreboardManager();
        $defaultWorld = Server::getInstance()->getWorldManager()->getDefaultWorld();
        $duelKitName = $this->DuelKit !== null ? strtolower($this->DuelKit->getName()) : '';

        if (!$this->ScoreboardEnabled) {
            $scoreboardUtils->remove($player);
            return;
        }

        switch (true) {
            case !$this->isDueling:
                if ($this->player->getWorld() === $defaultWorld) {
                    $scoreboardManager->setLobbyScoreboard($player);
                } elseif ($this->spectating && $this->spectatingDuel) {
                    $scoreboardManager->setSpectatorScoreboard($player);
                } else {
                    $scoreboardManager->setArenaScoreboard($player, true);
                }
                break;
            case $duelKitName === 'boxing':
                $scoreboardManager->Boxing($player);
                break;
            default:
                $scoreboardManager->setArenaScoreboard($player, true);
                break;
        }
    }


    /**
     * @return string|null
     */
    public function getOpponent(): ?string
    {
        return $this->Opponent;
    }

    /**
     * @param string|null $name
     * @return void
     */
    public function setOpponent(?string $name): void
    {
        $this->Opponent = $name;
    }
}
