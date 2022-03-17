<?php

declare(strict_types=1);

namespace Kohaku\Core\Task;

use Kohaku\Core\Loader;
use pocketmine\scheduler\Task;

class BroadcastTask extends Task
{

    private int $line = -1;

    public function onRun(): void
    {
        $cast = [Loader::getPrefixCore() . "§eติดตามข่าวสารเซิฟได้ที่ Omlet Arcade. notkungz1", Loader::getPrefixCore() . "§eเข้า Discord ได้ที่: §bhttps://discord.gg/pPUEYm9N9P"];
        $this->line++;
        $msg = $cast[$this->line];
        foreach (Loader::getInstance()->getServer()->getOnlinePlayers() as $online) {
            $online->sendMessage($msg);
        }
        if ($this->line === count($cast) - 1) {
            $this->line = -1;
        }
    }
}
