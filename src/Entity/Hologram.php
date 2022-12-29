<?php

declare(strict_types=1);

namespace Nayuki\Entity;

use Nayuki\PracticeCore;
use pocketmine\entity\Entity;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Location;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\Server;

use function is_array;

class Hologram extends Entity
{
    /** @var int */
    private int $CountdownSeconds = 1;
    /** @var string */
    private string $type = 'Unknown';
    /** @var float */
    private float $height = 0.1;
    /** @var float */
    private float $width = 0.1;
    /** @var int */
    private int $tick = 0;
    private string $subtitle = '';

    public function __construct(Location $location, CompoundTag $nbt)
    {
        parent::__construct($location, $nbt);
        $this->forceMovementUpdate = false;
        $this->gravity = 0;
        $this->setScale(0.1);
        $this->setNameTagAlwaysVisible();
        $this->loadFromNBT($nbt);
        $this->getNetworkProperties()->setFloat(EntityMetadataProperties::BOUNDING_BOX_HEIGHT, 0);
        $this->getNetworkProperties()->setFloat(EntityMetadataProperties::BOUNDING_BOX_HEIGHT, 0);
    }

    /**
     * @param CompoundTag $nbt
     * @return void
     */
    private function loadFromNBT(CompoundTag $nbt): void
    {
        $this->type = $nbt->getString('type');
    }

    /**
     * @return string
     */
    public static function getNetworkTypeId(): string
    {
        return EntityIds::PLAYER;
    }

    /**
     * @param int $currentTick
     * @return bool
     */
    public function onUpdate(int $currentTick): bool
    {
        $this->tick++;
        if ($this->tick % 20 === 0) {
            $this->CountdownSeconds--;
            $this->setNameTag($this->subtitle . "\n§eUpdate In: §f" . $this->CountdownSeconds);
            if ($this->CountdownSeconds < 1) {
                $this->subtitle = $this->getSubtitleType();
                $this->CountdownSeconds = 30;
            }
        }
        return parent::onUpdate($currentTick);
    }

    /**
     * @return string
     */
    private function getSubtitleType(): string
    {
        $subtitle = '';
        $array = [];
        $pos = 0;
        $files = scandir(PracticeCore::getPlayerDataPath());
        if (is_array($files)) {
            foreach ($files as $file) {
                if (str_contains($file, '.yml')) {
                    $name = str_replace('.yml', '', $file);
                    if (Server::getInstance()->getPlayerExact($name) !== null) {
                        continue;
                    }
                    $parsed = yaml_parse_file(PracticeCore::getPlayerDataPath() . $name . '.yml');
                    if (is_array($parsed)) {
                        $array[$name] = $parsed[$this->type];
                    }
                }
            }
        }
        foreach (PracticeCore::getPracticeUtils()->getPlayerInSession() as $player) {
            if ($this->type === 'kills') {
                $array[$player->getName()] = PracticeCore::getSessionManager()::getSession($player)->getKills();
                $subtitle .= "§b§lTop Kills\n";
            } elseif ($this->type === 'deaths') {
                $array[$player->getName()] = PracticeCore::getSessionManager()::getSession($player)->getDeaths();
                $subtitle .= "§b§lTop Deaths\n";
            }
        }
        arsort($array);
        foreach ($array as $name => $kills) {
            if ($pos > 9) {
                break;
            }
            if ($pos < 3) {
                $subtitle .= '§6[' . ($pos + 1) . '] §r§a' . $name . ' §e' . $kills . "\n";
            } else {
                $subtitle .= '§7[' . ($pos + 1) . '] §r§a' . $name . ' §f' . $kills . "\n";
            }
            $pos++;
        }
        return $subtitle;
    }

    /**
     * @return EntitySizeInfo
     */
    public function getInitialSizeInfo(): EntitySizeInfo
    {
        return new EntitySizeInfo($this->height, $this->width);
    }

    /**
     * @return CompoundTag
     */
    public function saveNBT(): CompoundTag
    {
        $nbt = parent::saveNBT();
        $nbt->setString('type', $this->type);
        return $nbt;
    }
}
