<?php

declare(strict_types=1);

namespace Nayuki\Entities;

use Nayuki\PracticeCore;
use pocketmine\entity\Entity;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Location;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use function array_key_exists;
use function count;
use function is_array;

final class Hologram extends Entity
{
    /** @var int */
    private int $CountdownSeconds = 1;
    /** @var string|null */
    private ?string $type = null;
    /** @var float */
    private float $height = 0.1;
    /** @var float */
    private float $width = 0.1;
    /** @var int */
    private int $tick = 0;
    /** @var string */
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
                $this->CountdownSeconds = 15;
            }
        }
        return parent::onUpdate($currentTick);
    }

    /**
     * @return string
     */
    private function getSubtitleType(): string
    {
        if ($this->type !== null) {
            $subtitle = ($this->type === 'kills') ? "§b§lTop Kills\n" : "§b§lTop Deaths\n";
            $array = [];
            $files = scandir(PracticeCore::getPlayerDataPath());
            if ($files === false) {
                return 'Error Loading Data';
            }
            foreach ($files as $file) {
                if (str_ends_with($file, '.yml')) {
                    $name = substr($file, 0, -4);
                    $parsed = yaml_parse_file(PracticeCore::getPlayerDataPath() . $file);
                    if (is_array($parsed) && array_key_exists($this->type, $parsed)) {
                        $array[$name] = $parsed[$this->type];
                    } else {
                        return 'Error Loading Data';
                    }
                }
            }
            foreach (PracticeCore::getSessionManager()->getSessions() as $session) {
                $player = $session->getPlayer();
                $array[$player->getName()] = ($this->type === 'kills') ? $session->getKills() : $session->getDeaths();
            }
            arsort($array);
            for ($pos = 0; $pos < 10 && $pos < count($array); $pos++) {
                $name = array_keys($array)[$pos];
                $kills = (int)$array[$name];
                $prefix = ($pos < 3) ? '§6[' . ($pos + 1) . '] §r§a' : '§7[' . ($pos + 1) . '] §r§a';
                $suffix = ($pos < 3) ? ' §e' : ' §f';
                $subtitle .= sprintf('%s%s%s%s' . "\n", $prefix, $name, $suffix, $kills);
            }
            return $subtitle;
        }
        return '';
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
        if ($this->type === null) {
            return $nbt;
        }
        $nbt->setString('type', $this->type);
        return $nbt;
    }

    /**
     * @return float
     */
    protected function getInitialDragMultiplier(): float
    {
        return 0.02;
    }

    /**
     * @return float
     */
    protected function getInitialGravity(): float
    {
        return 0.08;
    }
}
