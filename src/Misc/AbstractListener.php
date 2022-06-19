<?php

namespace Kuu\Misc;

use Kuu\PracticeCore;
use pocketmine\event\Listener;
use pocketmine\Server;

abstract class AbstractListener implements Listener
{
    public function __construct()
    {
        Server::getInstance()->getPluginManager()->registerEvents($this, PracticeCore::getInstance());
    }
}