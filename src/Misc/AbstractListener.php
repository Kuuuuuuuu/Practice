<?php

namespace Nayuki\Misc;

use Nayuki\PracticeCore;
use pocketmine\event\Listener;
use pocketmine\Server;

abstract class AbstractListener implements Listener
{
    public function __construct()
    {
        Server::getInstance()->getPluginManager()->registerEvents($this, PracticeCore::getInstance());
    }
}
