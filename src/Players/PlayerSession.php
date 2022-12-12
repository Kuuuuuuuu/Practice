<?php

declare(strict_types=1);

namespace Nayuki\Players;

use Nayuki\Game\Kits\Kit;
use Nayuki\PracticeCore;
use pocketmine\player\Player;

class PlayerSession
{
    /** @var bool */
    public bool $ScoreboardEnabled = true;
    /** @var bool */
    public bool $CpsCounterEnabled = true;
    /** @var bool */
    public bool $SmoothPearlEnabled = true;
    /** @var string|null */
    public ?string $Scoreboard = null;
    /** @var int */
    public int $CombatTime = 0;
    /** @var int */
    public int $BoxingPoint = 0;
    /** @var int */
    public int $PearlCooldown = 0;
    /** @var bool */
    public bool $loadedData = false;
    /** @var int */
    public int $kills = 0;
    /** @var int */
    public int $deaths = 0;
    /** @var int */
    public int $killStreak = 0;
    /** @var bool */
    public bool $isDueling = false;
    /** @var Kit|null */
    public ?Kit $DuelKit = null;
    /** @var bool */
    public bool $isQueueing = false;
    /** @var string|null */
    private ?string $Opponent = null;
    /** @var bool */
    private bool $isCombat = false;
    /** @var string */
    private string $customTag = '';

    /**
     * @param Player $player
     * @return PlayerSession
     */
    public static function getSession(Player $player): PlayerSession
    {
        return PracticeCore::getCaches()->PlayerSession[$player->getName()] ??= new self();
    }

    /**
     * @param Player $player
     * @return void
     */
    public static function removeSession(Player $player): void
    {
        unset(PracticeCore::getCaches()->PlayerSession[$player->getName()]);
    }

    /**
     * @param array $data
     * @return void
     */
    public function loadData(array $data): void
    {
        if (isset($data['scoreboard'])) {
            $this->ScoreboardEnabled = (bool)$data['scoreboard'];
        }
        if (isset($data['cps'])) {
            $this->CpsCounterEnabled = (bool)$data['cps'];
        }
        if (isset($data['smoothpearl'])) {
            $this->SmoothPearlEnabled = (bool)$data['smoothpearl'];
        }
        if (isset($data['kills'])) {
            $this->kills = (int)$data['kills'];
        }
        if (isset($data['deaths'])) {
            $this->deaths = (int)$data['deaths'];
        }
        if (isset($data['tag'])) {
            $this->customTag = (string)$data['tag'];
        }
        if (isset($data['killStreak'])) {
            $this->killStreak = (int)$data['killStreak'];
        }
        $this->loadedData = true;
    }

    /**
     * @param bool $bool
     * @return void
     */
    public function setCombat(bool $bool): void
    {
        if (!$bool && $this->CombatTime > 0) {
            $this->CombatTime = 1;
        } else {
            $this->isCombat = $bool;
            $this->CombatTime = 10;
        }
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
     * @return bool
     */
    public function isCombat(): bool
    {
        return $this->isCombat;
    }

    /**
     * @return int
     */
    public function getStreak(): int
    {
        return $this->killStreak;
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
        if ($this->deaths > 0) {
            return $this->kills / $this->deaths;
        }
        return 1;
    }
}
