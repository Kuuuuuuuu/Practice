<?php

namespace Kuu\Misc;

use Kuu\PracticeCore;
use pocketmine\network\mcpe\raklib\RakLibInterface;

class PracticeRaklibInterface extends RakLibInterface
{
    public function blockAddress(string $address, int $timeout = 0): void
    {
        // Hacky method for proxy lol
        $this->unblockAddress($address); // Hacky method
        PracticeCore::getInstance()->getLogger()->info("trying to Block address: $address");
    }
}