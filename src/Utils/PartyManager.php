<?php

declare(strict_types=1);

namespace Kuu\Utils;

use Kuu\Loader;
use Kuu\NeptunePlayer;
use pocketmine\player\Player;

class PartyManager
{

    public static function createParty(Player $player, string $name, int $capacity): void
    {
        $party = new PartyFactory($name, $player->getName(), [$player], $capacity, false, PartyFactory::IDLE);
        Loader::getInstance()->PartyData[] = $party;
        if ($player instanceof NeptunePlayer) {
            $player->setParty($party);
            $player->setPartyRank(PartyFactory::LEADER);
        }
        $player->sendMessage(Loader::getPrefixCore() . '§aYour party was created.');
    }

    public static function getPartyFromPlayer(Player $player)
    {
        $result = null;
        foreach (Loader::getInstance()->PartyData as $party) {
            if ($party->isMember($player) or $party->isLeader($player)) {
                $result = $party;
            }
        }
        return $result;
    }

    public static function doesPartyExist(PartyFactory $party): bool
    {
        return self::getPartyIndexOf($party) !== -1;
    }

    public static function getPartyIndexOf(PartyFactory $party): bool|int|string
    {
        $index = array_search($party, Loader::getInstance()->PartyData);
        if (is_bool($index) and $index === false) {
            $index = -1;
        }
        return $index;
    }

    public static function invitePlayer(PartyFactory $party, Player $sender, Player $target): void
    {
        $invite = new PartyInvite($party, $sender->getName(), $target->getName());
        Loader::getInstance()->PartyInvite[] = $invite;
        $sender->sendMessage(Loader::getPrefixCore() . '§aYou invited ' . $target->getDisplayName() . ' to your party.');
        $target->sendMessage(Loader::getPrefixCore() . '§a' . $sender->getDisplayName() . ' invited you to their party.');
    }

    public static function getInvite($invite)
    {
        $result = null;
        foreach (Loader::getInstance()->PartyInvite as $invites) {
            if ($invites->getParty()->getName() === $invite) {
                $result = $invites;
            }
        }
        return $result;
    }

    public static function getParty($party)
    {
        $result = null;
        foreach (Loader::getInstance()->PartyData as $parties) {
            if ($parties->getName() === $party) {
                $result = $parties;
            }
        }
        return $result;
    }

    public static function getInvitesFromParty($party): array
    {
        $result = [];
        if (isset($party) and $party instanceof PartyInvite) {
            foreach (Loader::getInstance()->PartyInvite as $invite) {
                if ($invite->isParty($party)) {
                    $result[] = $invite;
                }
            }
        }
        return $result;
    }

    public static function hasInvite($target, PartyFactory $partyA): bool
    {
        $result = false;
        foreach (self::getInvites($target) as $invites) {
            $partyB = $invites->getParty();
            if ($partyA->getName() === $partyB->getName()) {
                $result = true;
            }
        }
        return $result;
    }

    public static function getInvites(Player $player): array
    {
        $result = [];
        foreach (Loader::getInstance()->PartyInvite as $invite) {
            if ($invite->isTarget($player)) {
                $result[] = $invite;
            }
        }
        return $result;
    }
}