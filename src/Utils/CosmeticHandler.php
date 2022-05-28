<?php

declare(strict_types=1);

namespace Kuu\Utils;

use Exception;
use GdImage;
use InvalidArgumentException;
use JsonException;
use Kuu\Loader;
use Kuu\NeptunePlayer;
use pocketmine\entity\Skin;
use pocketmine\player\Player;
use RuntimeException;
use function ord;
use function round;
use function strlen;

class CosmeticHandler
{
    public const BOUNDS_64_64 = 0;
    public const BOUNDS_64_32 = self::BOUNDS_64_64;
    public const BOUNDS_128_128 = 1;

    public string $dataFolder;
    public string $resourcesFolder;
    public string $artifactFolder;
    public string $humanoidFile;
    public string $stevePng;
    public string $saveSkin;
    public array $skinBounds = [];
    public array $cosmeticAvailable = [];
    public array $skin_widght_map = [
        64 * 32 * 4 => 64,
        64 * 64 * 4 => 64,
        128 * 128 * 4 => 128,
        128 * 256 * 4 => 256

    ];
    public array $skin_height_map = [
        64 * 32 * 4 => 32,
        64 * 64 * 4 => 64,
        128 * 128 * 4 => 128,
        128 * 256 * 4 => 128
    ];
    private string $capeFolder;

    /**
     * @throws JsonException
     */
    public function __construct()
    {
        $this->dataFolder = Loader::getInstance()->getDataFolder() . 'cosmetic/';
        $this->saveSkin = $this->dataFolder . 'skin/';
        if (!is_dir($this->dataFolder)) {
            @mkdir($this->dataFolder);
            @mkdir($this->saveSkin);
        }
        $this->resourcesFolder = Loader::getInstance()->getDataFolder() . 'cosmetic/';
        $this->artifactFolder = $this->resourcesFolder . 'artifact/';
        $this->capeFolder = $this->resourcesFolder . 'capes/';
        $this->stevePng = $this->resourcesFolder . 'steve.png';
        $this->humanoidFile = $this->resourcesFolder . 'humanoid.json';
        $cubes = $this->getCubes(json_decode(file_get_contents($this->humanoidFile), true, 512, JSON_THROW_ON_ERROR)['geometry.humanoid']);
        $this->skinBounds[self::BOUNDS_64_64] = $this->getSkinBounds($cubes);
        $this->skinBounds[self::BOUNDS_128_128] = $this->getSkinBounds($cubes, 2.0);
        $checkFileAvailable = [];
        $allFiles = scandir($this->artifactFolder);
        foreach ($allFiles as $allFilesName) {
            if (strpos($allFilesName, '.json')) {
                $checkFileAvailable[] = str_replace('.json', '', $allFilesName);
            }
        }
        foreach ($checkFileAvailable as $value) {
            if (!in_array($value . '.png', $allFiles, true)) {
                unset($checkFileAvailable[array_search($value, $checkFileAvailable, true)]);
            }
        }
        $this->cosmeticAvailable = $checkFileAvailable;
        sort($this->cosmeticAvailable);
    }

    private function getCubes(array $geometryData): ?array
    {
        try {
            $cubes = [];
            foreach ($geometryData['bones'] as $bone) {
                if (!isset($bone['cubes'])) {
                    continue;
                }
                if ($bone['mirror'] ?? false) {
                    throw new InvalidArgumentException('Unsupported geometry data');
                }
                foreach ($bone['cubes'] as $cubeData) {
                    $cube = [];
                    $cube['x'] = $cubeData['size'][0];
                    $cube['y'] = $cubeData['size'][1];
                    $cube['z'] = $cubeData['size'][2];
                    $cube['uvX'] = $cubeData['uv'][0];
                    $cube['uvY'] = $cubeData['uv'][1];
                    $cubes[] = $cube;
                }
            }
            return $cubes;
        } catch (Exception $e) {
            ArenaUtils::getLogger((string)$e);
            return null;
        }
    }

    private function getSkinBounds(array $cubes, float $scale = 1.0): ?array
    {
        try {
            $bounds = [];
            foreach ($cubes as $cube) {
                $x = (int)($scale * $cube['x']);
                $y = (int)($scale * $cube['y']);
                $z = (int)($scale * $cube['z']);
                $uvX = (int)($scale * $cube['uvX']);
                $uvY = (int)($scale * $cube['uvY']);
                $bounds[] = ['min' => ['x' => $uvX + $z, 'y' => $uvY], 'max' => ['x' => $uvX + $z + (2 * $x) - 1, 'y' => $uvY + $z - 1]];
                $bounds[] = ['min' => ['x' => $uvX, 'y' => $uvY + $z], 'max' => ['x' => $uvX + (2 * ($z + $x)) - 1, 'y' => $uvY + $z + $y - 1]];
            }
            return $bounds;
        } catch (Exception $e) {
            ArenaUtils::getLogger((string)$e);
            return null;
        }
    }

    public function getCapes(): array
    {
        $list = array();
        foreach (array_diff(scandir($this->capeFolder)) as $data) {
            $dat = explode('.', $data);
            if ($dat[1] === 'png') {
                $list[] = $dat[0];
            }
        }
        return $list;
    }

    public function saveSkin(string $skin, string $name): void
    {
        try {
            $path = $this->dataFolder;
            if (!file_exists($path . 'skin') && !mkdir($concurrentDirectory = $path . 'skin') && !is_dir($concurrentDirectory)) {
                throw new RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
            }
            $img = $this->skinDataToImage($skin);
            if ($img === null) {
                return;
            }
            imagepng($img, $path . 'skin/' . $name . '.png');
        } catch (Exception $e) {
            ArenaUtils::getLogger((string)$e);
        }
    }

    public function skinDataToImage(string $skinData): ?GdImage
    {
        try {
            $size = strlen($skinData);
            $width = $this->skin_widght_map[$size];
            $height = $this->skin_height_map[$size];
            $skinPos = 0;
            $image = imagecreatetruecolor($width, $height);
            if ($image === false) {
                return null;
            }
            imagefill($image, 0, 0, imagecolorallocatealpha($image, 0, 0, 0, 127));
            for ($y = 0; $y < $height; $y++) {
                for ($x = 0; $x < $width; $x++) {
                    $r = ord($skinData[$skinPos]);
                    $skinPos++;
                    $g = ord($skinData[$skinPos]);
                    $skinPos++;
                    $b = ord($skinData[$skinPos]);
                    $skinPos++;
                    $a = 127 - intdiv(ord($skinData[$skinPos]), 2);
                    $skinPos++;
                    $col = imagecolorallocatealpha($image, $r, $g, $b, $a);
                    imagesetpixel($image, $x, $y, $col);
                }
            }
            imagesavealpha($image, true);
            return $image;
        } catch (Exception $e) {
            ArenaUtils::getLogger((string)$e);
            return null;
        }
    }

    public function setCostume(Player $player, string $stuffName): void
    {
        $imagePath = $this->artifactFolder . $stuffName . '.png';
        $geometryPath = $this->artifactFolder . $stuffName . '.json';
        $skin = $this->loadSkin($imagePath, $geometryPath, $player->getSkin()->getSkinId(), 'geometry.cosmetic/artifact');
        if ($skin !== null) {
            $player->setSkin($skin);
            $player->sendSkin();
        }
    }

    public function loadSkin(string $imagePath, string $geometryPath, string $skinID, string $geometryName): ?Skin
    {
        try {
            $img = @imagecreatefrompng($imagePath);
            $size = getimagesize($imagePath);
            $skinBytes = '';
            for ($y = 0; $y < $size[1]; $y++) {
                for ($x = 0; $x < $size[0]; $x++) {
                    $pixelColor = @imagecolorat($img, $x, $y);
                    $a = ((~($pixelColor >> 24)) << 1) & 0xff;
                    $r = ($pixelColor >> 16) & 0xff;
                    $g = ($pixelColor >> 8) & 0xff;
                    $b = $pixelColor & 0xff;
                    $skinBytes .= chr($r) . chr($g) . chr($b) . chr($a);
                }
            }
            @imagedestroy($img);
            return new Skin($skinID, $skinBytes, '', $geometryName, file_get_contents($geometryPath));
        } catch (Exception $e) {
            ArenaUtils::getLogger((string)$e);
            return null;
        }
    }

    public function setSkin(Player $player, string $stuffName): void
    {
        try {
            if ($player instanceof NeptunePlayer) {
                $imagePath = $this->getSaveSkin($player->getName());
                $skin = $this->loadSkinAndApplyStuff($stuffName, $imagePath, $player->getSkin()->getSkinId());
                $cape = $player->getCape();
                $capeData = ($cape !== '') ? $this->createCape($player->getCape()) : $player->getSkin()->getCapeData();
                $skin = new Skin($skin?->getSkinId() ?? $player->getSkin()->getSkinId(), $skin?->getSkinData() ?? $player->getSkin()->getSkinData(), $capeData, $skin?->getGeometryName() ?? $player->getSkin()->getGeometryName(), $skin?->getGeometryData() ?? $player->getSkin()->getGeometryData());
                $player->setSkin($skin);
                $player->sendSkin();
            }
        } catch (Exception $e) {
            ArenaUtils::getLogger((string)$e);
            return;
        }
    }

    public function getSaveSkin(string $name): string
    {
        return $this->saveSkin . $name . '.png';
    }

    private function loadSkinAndApplyStuff(string $stuffName, string $imagePath, string $skinID): ?Skin
    {
        try {
            $size = getimagesize($imagePath);
            $imagePathh = $this->exportSkinToImage($imagePath, $stuffName, [$size[0], $size[1], 4]);
            $geometryPath = $this->artifactFolder . $stuffName . '.json';
            return $this->loadSkin($imagePathh, $geometryPath, $skinID, 'geometry.cosmetic/artifact');
        } catch (Exception $e) {
            ArenaUtils::getLogger((string)$e);
            return null;
        }
    }

    private function exportSkinToImage($skinPath, string $stuffName, array $size): ?string
    {
        try {
            $path = $this->artifactFolder;
            $down = imagecreatefrompng($skinPath);
            if ($size[0] * $size[1] * $size[2] === 65536) {
                $upper = $this->resizeImage($path . $stuffName . '.png', 128, 128);
            } else {
                $upper = $this->resizeImage($path . $stuffName . '.png', 64, 64);
            }
            imagecolortransparent($upper, imagecolorallocatealpha($upper, 0, 0, 0, 127));
            imagealphablending($down, true);
            imagesavealpha($down, true);
            imagecopymerge($down, $upper, 0, 0, 0, 0, $size[0], $size[1], 100);
            imagepng($down, $this->dataFolder . 'temp.png');
            return $this->dataFolder . 'temp.png';
        } catch (Exception $e) {
            ArenaUtils::getLogger((string)$e);
            return null;
        }
    }

    private function resizeImage($file, $w, $h, $crop = false): GdImage|bool|null
    {
        try {
            [$width, $height] = getimagesize($file);
            $r = $width / $height;
            if ($crop) {
                if ($width > $height) {
                    $width = ceil($width - ($width * abs($r - $w / $h)));
                } else {
                    $height = ceil($height - ($height * abs($r - $w / $h)));
                }
                $newwidth = $w;
                $newheight = $h;
            } elseif ($w / $h > $r) {
                $newwidth = $h * $r;
                $newheight = $h;
            } else {
                $newheight = $w / $r;
                $newwidth = $w;
            }
            $src = imagecreatefrompng($file);
            $dst = imagecreatetruecolor($w, $h);
            imagecolortransparent($dst, imagecolorallocatealpha($dst, 0, 0, 0, 127));
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
            imagecopyresampled($dst, $src, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
            return $dst;
        } catch (Exception $e) {
            ArenaUtils::getLogger((string)$e);
            return null;
        }
    }

    public function createCape($capeName): ?string
    {
        try {
            $path = Loader::getInstance()->getDataFolder() . 'cosmetic/capes/' . "$capeName.png";
            $img = @imagecreatefrompng($path);
            $bytes = '';
            $l = (int)@getimagesize($path)[1];
            for ($y = 0; $y < $l; $y++) {
                for ($x = 0; $x < 64; $x++) {
                    $rgba = @imagecolorat($img, $x, $y);
                    $a = ((~($rgba >> 24)) << 1) & 0xff;
                    $r = ($rgba >> 16) & 0xff;
                    $g = ($rgba >> 8) & 0xff;
                    $b = $rgba & 0xff;
                    $bytes .= chr($r) . chr($g) . chr($b) . chr($a);
                }
            }
            @imagedestroy($img);
            return $bytes;
        } catch (Exception $e) {
            ArenaUtils::getLogger((string)$e);
            return null;
        }
    }

    public function resetSkin(Player $player): void
    {
        try {
            $name = $player->getName();
            $imagePath = $this->getSaveSkin($name);
            $skin = $this->loadSkin($imagePath, $this->resourcesFolder . 'steve.json', $player->getSkin()->getSkinId(), 'geometry.humanoid.customSlim');
            if ($skin !== null) {
                $skin = new Skin($skin->getSkinId() ?? $player->getSkin()->getSkinId(), $skin->getSkinData() ?? $player->getSkin()->getSkinData(), '', $skin->getGeometryName() ?? $player->getSkin()->getGeometryName(), $player->getSkin()->getGeometryData());
                $player->setSkin($skin);
                $player->sendSkin();
            }
        } catch (Exception $e) {
            ArenaUtils::getLogger((string)$e);
        }
    }

    public function getSkinTransparencyPercentage(string $skinData): ?int
    {
        try {
            switch (strlen($skinData)) {
                case 8192:
                    $maxX = 64;
                    $maxY = 32;
                    $bounds = $this->skinBounds[self::BOUNDS_64_32];
                    break;
                case 16384:
                    $maxX = 64;
                    $maxY = 64;
                    $bounds = $this->skinBounds[self::BOUNDS_64_64];
                    break;
                case 65536:
                    $maxX = 128;
                    $maxY = 128;
                    $bounds = $this->skinBounds[self::BOUNDS_128_128];
                    break;
                default:
                    throw new InvalidArgumentException('Inappropriate skin data length: ' . strlen($skinData));
            }
            $transparentPixels = $pixels = 0;
            foreach ($bounds as $bound) {
                if ($bound['max']['x'] > $maxX || $bound['max']['y'] > $maxY) {
                    continue;
                }
                for ($y = $bound['min']['y']; $y <= $bound['max']['y']; $y++) {
                    for ($x = $bound['min']['x']; $x <= $bound['max']['x']; $x++) {
                        $key = (($maxX * $y) + $x) * 4;
                        $a = ord($skinData[$key + 3]);
                        if ($a < 127) {
                            ++$transparentPixels;
                        }
                        ++$pixels;
                    }
                }
            }
            return (int)round($transparentPixels * 100 / max(1, $pixels));
        } catch (Exception $e) {
            ArenaUtils::getLogger((string)$e);
            return null;
        }
    }
}