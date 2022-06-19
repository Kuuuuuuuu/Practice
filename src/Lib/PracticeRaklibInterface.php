<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it &&/|| modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, ||
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
*/


namespace Kuu\Events;


use Kuu\PracticeCore;
use pocketmine\network\mcpe\raklib\RakLibInterface;

class PracticeRaklibInterface extends RakLibInterface
{
    public function blockAddress(string $address, int $timeout = 0): void
    {
        // Hacky method for proxy lol
        $this->unblockAddress($address); // Hacky method
        PracticeCore::getInstance()->getLogger()->info("Trying to Block address: $address");
    }
}