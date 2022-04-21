<?php

declare(strict_types=1);

namespace Kohaku\Utils;

use Kohaku\Loader;
use pocketmine\player\Player;
use pocketmine\Server;

class PartyInvite
{
    private PartyFactory $party;
    private string $sender;
    private string $target;

    public function __construct(PartyFactory $party, string $sender, string $target)
    {
        $this->party = $party;
        $this->sender = $sender;
        $this->target = $target;
    }

    public function getSender(): string
    {
        return $this->sender;
    }

    public function getTarget(): string
    {
        return $this->target;
    }

    public function isParty(PartyFactory $party): bool
    {
        $party = $party->getName();
        return $party === $this->getParty()->getName();
    }

    public function getParty(): ?PartyFactory
    {
        return $this->party;
    }

    public function isSender(Player $player): bool
    {
        $player = $player->getName();
        return $player === $this->sender;
    }

    public function isTarget(Player $player): bool
    {
        $player = $player->getName();
        return $player === $this->target;
    }

    public function isSenderOnline(): bool
    {
        $player = Server::getInstance()->getPlayerExact($this->sender);
        return $player !== null;
    }

    public function isTargetOnline(): bool
    {
        $player = Server::getInstance()->getPlayerExact($this->target);
        return $player !== null;
    }

    public function accept(): void
    {
        $sender = Server::getInstance()->getPlayerExact($this->sender);
        $target = Server::getInstance()->getPlayerExact($this->target);
        if ($sender !== null) {
            $sender->sendMessage(Loader::getPrefixCore() . '§a' . $target->getDisplayName() . ' accepted your invitation.');
            $target->sendMessage(Loader::getPrefixCore() . '§aInvitation accepted.');
        }
        if ($this->doesPartyExist()) {
            $this->party->addMember($target);
        } else {
            $target->sendMessage(Loader::getPrefixCore() . '§cThat party no longer exists.');
        }
        $this->clear();
    }

    public function doesPartyExist(): bool
    {
        return PartyManager::doesPartyExist($this->party) !== false;
    }

    public function clear(): void
    {
        unset(Loader::getInstance()->PartyInvite[array_search($this, Loader::getInstance()->PartyInvite)]);
    }

    public function decline(): void
    {
        $sender = Server::getInstance()->getPlayerExact($this->sender);
        $target = Server::getInstance()->getPlayerExact($this->target);
        if ($sender !== null) {
            $sender->sendMessage(Loader::getPrefixCore() . '§c' . $target->getDisplayName() . ' declined your invitation.');
            $target->sendMessage(Loader::getPrefixCore() . '§aInvitation declined.');
        }
        $this->clear();
    }
}