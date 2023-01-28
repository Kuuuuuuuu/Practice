<?php

namespace Nayuki\Task;

use Nayuki\PracticeCore;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use function count;

class BroadcastTask extends Task
{
    private int $line = -1;

    public function onRun(): void
    {
        $cast = [
            PracticeCore::getInstance()->getPrefixCore() . '§eCheck out our Update at Omlet Arcade. notkungz1 !',
            PracticeCore::getInstance()->getPrefixCore() . '§eติดตามข่าวสารเซิฟได้ที่ Omlet Arcade. notkungz1',
            PracticeCore::getInstance()->getPrefixCore() . '§eวิธีกลับ Lobby ใช้คำสั่ง /hub',
        ];
        $this->line++;
        $msg = $cast[$this->line];
        Server::getInstance()->broadcastMessage($msg);
        if ($this->line === count($cast) - 1) {
            $this->line = -1;
        }
    }
}
