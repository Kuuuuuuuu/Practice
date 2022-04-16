<?php

declare(strict_types=1);

namespace Kohaku\Utils;

use Kohaku\Loader;
use Kohaku\NeptunePlayer;
use pocketmine\player\Player;

class PartyManager
{

    public static function createParty(Player $player): void
    {
        $capacity = 100;
        $party = new PartyFactory($player->getName(), $player->getName(), [$player], $capacity, false, PartyFactory::IDLE);
        Loader::getInstance()->PartyData[] = $party;
        if ($player instanceof NeptunePlayer) {
            $player->setParty($party);
            $player->setPartyRank(PartyFactory::LEADER);
        }
        $player->sendMessage(Loader::getPrefixCore() . '§aYour party was created.');
    }

    public static function getPartyFromPlayer(?Player $player)
    {
        $result = null;
        if (isset($player) or !is_null($player)) {
            foreach (Loader::getInstance()->PartyData as $party) {
                if ($party->isMember($player->getName()) or $party->isLeader($player->getName())) {
                    $result = $party;
                }
            }
        }
        return $result;
    }

    public static function doesPartyExist(PartyFactory $party): bool
    {
        return self::getPartyIndexOf($party) !== -1;
    }

    public static function getPartyIndexOf(PartyFactory $party): string|int|bool
    {
        $index = array_search($party, Loader::getInstance()->PartyData);
        if (is_bool($index) and $index === false) {
            $index = -1;
        }
        return $index;
    }

    public static function invitePlayer(PartyFactory $party, NeptunePlayer $sender, NeptunePlayer $target): void
    {
        $invite = new PartyInvite($party, $sender->getName(), $target->getName());
        Loader::getInstance()->PartyInvite[] = $invite;
        $sender->sendMessage('§aYou invited ' . $target->getDisplayName() . ' to your party.');
        $target->sendMessage('§a' . $sender->getDisplayName() . ' invited you to their party.');
    }

    public static function getInvite($invite)
    {
        $result = null;
        foreach (Loader::getInstance()->PartyInvite as $invites) {
            $name = $invites->getParty()->getName();
            if ($name === $invite) {
                $result = $invites;
            }
        }
        return $result;
    }

    public static function getParty($party)
    {
        $result = null;
        foreach (Loader::getInstance()->PartyData as $parties) {
            $name = $parties->getName();
            if ($name === $party) {
                $result = $parties;
            }
        }
        return $result;
    }

    public static function getInvitesFromParty($party): array
    {
        $result = [];
        if (isset($party) or !is_null($party)) {
            foreach (Loader::getInstance()->PartyInvite as $invite) {
                if ($invite->isParty($party)) {
                    $result[] = $invite;
                }
            }
        }
        return $result;
    }

    public static function hasInvite($target, ?PartyFactory $partyA): bool
    {
        $result = false;
        foreach (self::getInvites($target) as $invites) {
            $partyB = $invites->getParty();
            if ($partyA !== null) {
                if ($partyA->getName() === $partyB->getName()) {
                    $result = true;
                }
            }
        }
        return $result;
    }

    public static function getInvites(?Player $player): array
    {
        $result = [];
        if (isset($player) or !is_null($player)) {
            foreach (Loader::getInstance()->PartyInvite as $invite) {
                if ($invite->isTarget($player->getName())) {
                    $result[] = $invite;
                }
            }
        }
        return $result;
    }
}