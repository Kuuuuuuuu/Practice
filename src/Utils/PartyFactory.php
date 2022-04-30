<?php

declare(strict_types=1);

namespace Kuu\Utils;

use Kuu\Loader;
use Kuu\NeptunePlayer;
use pocketmine\player\Player;
use pocketmine\Server;

class PartyFactory
{
    public const LEADER = 'Leader';
    public const MEMBER = 'Member';

    public const IDLE = 0;
    public const DUEL = 1;
    public array $members = [];
    private string $name;
    private string $leader;
    private int $capacity;
    private bool $closed;
    private int $status;

    public function __construct(string $name, string $leader, array $members, int $capacity, bool $closed, int $status)
    {
        $this->name = $name;
        $this->leader = $leader;
        $this->members = $members;
        $this->capacity = $capacity;
        $this->closed = $closed;
        $this->status = $status;
    }

    public function getLeader(): string
    {
        return $this->leader;
    }

    public function setLeader(Player $player): void
    {
        $playern = $player->getName();
        $this->leader = $playern;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getMembers(): array
    {
        return $this->members;
    }

    public function setMembers(array $members): void
    {
        $this->members = $members;
    }

    public function getCapacity(): int
    {
        return $this->capacity;
    }

    public function getMembersOnline(): array
    {
        $online = [];
        foreach ($this->members as $member) {
            $player = Server::getInstance()->getPlayerExact($member->getName());
            if ($player !== null) {
                $online[] = $player->getName();
            }
        }
        return $online;
    }

    public function isClosed(): bool
    {
        return $this->closed === true;
    }

    public function setClosed(): void
    {
        $this->closed = true;
    }

    public function isFull(): bool
    {
        return count($this->members) >= $this->capacity;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setStatus(int $status): void
    {
        $this->status = $status;
    }

    public function isLeader(Player $player): bool
    {
        $playern = $player->getName();
        return $playern === $this->leader;
    }

    public function isMember(Player $player): bool
    {
        $playern = $player->getName();
        return in_array($playern, $this->members, true);
    }

    public function setOpen(): void
    {
        $this->closed = false;
    }

    public function addMember(Player $player): void
    {
        if ($player->getWorld() !== Server::getInstance()->getWorldManager()->getDefaultWorld()) {
            $player->kill();
        }
        $this->sendMessage($player->getDisplayName() . ' has joined the party.');
        $this->members[] = $player;
        if ($player instanceof NeptunePlayer) {
            $player->setParty($this);
            $player->setPartyRank(self::MEMBER);
            $player->sendMessage(Loader::getPrefixCore() . '§aYou joined the party.');
        }
    }

    public function sendMessage(string $message): void
    {
        foreach ($this->members as $member) {
            $member = Server::getInstance()->getPlayerExact((string)$member?->getName());
            if ($member instanceof Player) {
                $member->sendMessage(Loader::getPrefixCore() . $message);
            }
        }
    }

    public function removeMember(Player $player): void
    {
        unset($this->members[array_search($player->getName(), $this->members, true)]);
        $this->sendMessage($player->getDisplayName() . ' has left the party.');
        if ($player instanceof NeptunePlayer) {
            $player->setParty(null);
            $player->setPartyRank(null);
            $player->sendMessage(Loader::getPrefixCore() . '§aYou left the party.');
        }
    }

    public function kickMember(Player $player): void
    {
        unset($this->members[array_search($player->getName(), $this->members, true)]);
        if ($player instanceof NeptunePlayer) {
            $player->setParty(null);
            $player->setPartyRank(null);
            $player->sendMessage(Loader::getPrefixCore() . '§cYou were kicked from the party.');
        }
        $this->sendMessage($player->getDisplayName() . ' was kicked from the party.');
    }

    public function disband(): void
    {
        $leader = Server::getInstance()->getPlayerExact($this->leader);
        if ($leader !== null) {
            $leader->sendMessage(Loader::getPrefixCore() . '§aYou disbanded your party.');
            unset($this->members[array_search($leader->getName(), $this->members, true)]);
            if ($leader instanceof NeptunePlayer) {
                $leader->setParty(null);
                $leader->setPartyRank(null);
            }
        }
        $this->sendMessage($this->leader . ' disbanded the party.');
        foreach ($this->members as $member) {
            $member = Server::getInstance()->getPlayerExact((string)$member?->getName());
            if ($member instanceof NeptunePlayer) {
                $member->setParty(null);
                $member->setPartyRank(null);
            }
        }
        unset(Loader::getInstance()->PartyData[array_search($this, Loader::getInstance()->PartyData, true)]);
        foreach (PartyManager::getInvitesFromParty($this) as $invites) {
            $invites->clear();
        }
    }
}