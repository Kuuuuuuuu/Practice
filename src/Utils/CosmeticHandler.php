<?php

declare(strict_types=1);

namespace Nayuki\Utils;

use Exception;
use GdImage;
use InvalidArgumentException;
use JsonException;
use Nayuki\PracticeCore;
use pocketmine\entity\Skin;
use pocketmine\player\Player;
use RuntimeException;
use function strlen;

/** Thanks ZodiaX for this code. modified from https://github.com/ZeqaNetwork/Mineceit/blob/master/src/mineceit/player/cosmetic/CosmeticHandler.php */
final class CosmeticHandler
{
    public const BOUNDS_64_64 = 0;
    public const BOUNDS_128_128 = 1;
    /** @var string */
    public string $dataFolder;
    /** @var string */
    public string $resourcesFolder;
    /** @var string */
    public string $artifactFolder;
    /** @var string */
    public string $humanoidFile;
    /** @var string */
    public string $stevePng;
    /** @var string */
    public string $saveSkin;
    /** @var array */
    public array $skinBounds = [];
    /** @var array */
    public array $cosmeticAvailable = [];
    /** @var array|int[] */
    public array $skin_width_map = [
        64 * 32 * 4 => 64,
        64 * 64 * 4 => 64,
        128 * 128 * 4 => 128,
        128 * 256 * 4 => 256

    ];
    /** @var array|int[] */
    public array $skin_height_map = [
        64 * 32 * 4 => 32,
        64 * 64 * 4 => 64,
        128 * 128 * 4 => 128,
        128 * 256 * 4 => 128
    ];
    /** @var string */
    private string $capeFolder;

    /**
     * @throws JsonException
     */
    public function __construct()
    {
        $this->dataFolder = PracticeCore::getInstance()->getDataFolder() . 'cosmetic/';
        $this->saveSkin = $this->dataFolder . 'skin/';
        if (!is_dir($this->dataFolder)) {
            @mkdir($this->dataFolder);
            @mkdir($this->saveSkin);
        }
        $this->resourcesFolder = PracticeCore::getInstance()->getDataFolder() . 'cosmetic/';
        $this->artifactFolder = $this->resourcesFolder . 'artifact/';
        $this->capeFolder = $this->resourcesFolder . 'capes/';
        $this->stevePng = $this->resourcesFolder . 'steve.png';
        $this->humanoidFile = $this->resourcesFolder . 'humanoid.json';
        $humanoidContent = file_get_contents($this->humanoidFile);
        if ($humanoidContent === false) {
            throw new RuntimeException('Cannot read humanoid file');
        }
        $cubes = $this->getCubes(json_decode($humanoidContent, true, 512, JSON_THROW_ON_ERROR)['geometry.humanoid']);
        if ($cubes === null) {
            throw new RuntimeException('Cannot get cubes');
        }
        $skinBoundsSixtyFour = $this->getSkinBounds($cubes);
        $skinBoundsOneTwoEight = $this->getSkinBounds($cubes, 2.0);
        if ($skinBoundsSixtyFour == null || $skinBoundsOneTwoEight == null) {
            throw new RuntimeException('Cannot get skin bounds');
        }
        $this->skinBounds[self::BOUNDS_64_64] = $skinBoundsSixtyFour;
        $this->skinBounds[self::BOUNDS_128_128] = $skinBoundsOneTwoEight;
        $checkFileAvailable = [];
        $allFiles = scandir($this->artifactFolder);
        if ($allFiles === false) {
            throw new RuntimeException('Cannot read artifact folder');
        }
        foreach ($allFiles as $allFilesName) {
            if (pathinfo($allFilesName, PATHINFO_EXTENSION) === 'json') {
                $checkFileAvailable[] = pathinfo($allFilesName, PATHINFO_FILENAME);
            }
        }
        $checkFileAvailable = array_intersect($checkFileAvailable, array_map(function ($file) {
            return pathinfo($file, PATHINFO_FILENAME);
        }, array_filter($allFiles, function ($file) {
            return pathinfo($file, PATHINFO_EXTENSION) === 'png';
        })));
        $this->cosmeticAvailable = $checkFileAvailable;
        sort($this->cosmeticAvailable);
    }


    /**
     * @param array $geometryData
     * @return array|null
     */
    private function getCubes(array $geometryData): ?array
    {
        $cubes = [];
        foreach ($geometryData['bones'] as $bone) {
            if (($bone['cubes'] ?? null) === null) {
                continue;
            }

            if (($bone['mirror'] ?? false) === true) {
                throw new InvalidArgumentException('Unsupported geometry data');
            }

            foreach ($bone['cubes'] as $cubeData) {
                $cube = [
                    'x' => $cubeData['size'][0],
                    'y' => $cubeData['size'][1],
                    'z' => $cubeData['size'][2],
                    'uvX' => $cubeData['uv'][0],
                    'uvY' => $cubeData['uv'][1],
                ];
                $cubes[] = $cube;
            }
        }
        return $cubes ?: null;
    }

    /**
     * @param array $cubes
     * @param float $scale
     * @return array
     */
    private function getSkinBounds(array $cubes, float $scale = 1.0): array
    {
        $bounds = [];
        foreach ($cubes as $cube) {
            $x = (int)($scale * $cube['x']);
            $y = (int)($scale * $cube['y']);
            $z = (int)($scale * $cube['z']);
            $uvX = (int)($scale * $cube['uvX']);
            $uvY = (int)($scale * $cube['uvY']);
            $bounds[] = ['min' => ['x' => $uvX + $z, 'y' => $uvY], 'max' => ['x' => $uvX + $z + (2 << $x) - 1, 'y' => $uvY + $z - 1]];
            $bounds[] = ['min' => ['x' => $uvX, 'y' => $uvY + $z], 'max' => ['x' => $uvX + (2 << ($z + $x)) - 1, 'y' => $uvY + $z + $y - 1]];
        }
        return $bounds;
    }

    /**
     * @return string[]|array
     */
    public function getCapes(): array
    {
        $files = scandir($this->capeFolder, SCANDIR_SORT_NONE);
        if ($files === false) {
            return [];
        }
        $files = array_filter($files, function ($file) {
            return preg_match('/\.png$/', $file);
        });
        return array_map('basename', $files, array_fill(0, count($files), '.png'));
    }

    /**
     * @param string $skin
     * @param string $name
     * @return void
     */
    public function saveSkin(string $skin, string $name): void
    {
        try {
            $path = $this->dataFolder . 'skin/';

            if (!is_dir($path) && !mkdir($path, 0755, true)) {
                throw new RuntimeException(sprintf('Directory "%s" was not created', $path));
            }

            $img = $this->skinDataToImage($skin);
            if ($img) {
                imagepng($img, $path . $name . '.png');
            }
        } catch (Exception $e) {
            error_log($e->getMessage());
        }
    }

    /**
     * @param string $skinData
     * @return GdImage|null
     */
    public function skinDataToImage(string $skinData): ?GdImage
    {
        $size = strlen($skinData);
        $width = $this->skin_width_map[$size];
        $height = $this->skin_height_map[$size];
        $position = 0;
        $image = imagecreatetruecolor($width, $height);

        if ($image === false) {
            return null;
        }

        $alpha = imagecolorallocatealpha($image, 0, 0, 0, 127);

        if ($alpha === false) {
            return null;
        }

        imagefill($image, 0, 0, $alpha);
        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $r = ord($skinData[$position]);
                $position++;
                $g = ord($skinData[$position]);
                $position++;
                $b = ord($skinData[$position]);
                $position++;
                $a = 127 - intdiv(ord($skinData[$position]), 2);
                $position++;
                $col = imagecolorallocatealpha($image, $r, $g, $b, $a);

                if ($col === false) {
                    return null;
                }

                imagesetpixel($image, $x, $y, $col);
            }
        }
        imagesavealpha($image, true);
        imagedestroy($image);
        return $image;
    }

    /**
     * @param Player $player
     * @param string $stuffName
     * @return void
     * @throws JsonException
     */
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

    /**
     * @param string $imagePath
     * @param string $geometryPath
     * @param string $skinID
     * @param string $geometryName
     * @return Skin|null
     * @throws JsonException
     */
    public function loadSkin(string $imagePath, string $geometryPath, string $skinID, string $geometryName): ?Skin
    {
        $img = imagecreatefrompng($imagePath);

        if ($img === false) {
            return null;
        }

        $size = getimagesize($imagePath);

        if ($size === false) {
            return null;
        }

        $skinBytes = '';
        for ($y = 0; $y < $size[1]; $y++) {
            for ($x = 0; $x < $size[0]; $x++) {
                $colorIndex = imagecolorat($img, $x, $y);

                if ($colorIndex === false) {
                    return null;
                }

                $color = imagecolorsforindex($img, $colorIndex);
                $alpha = $color['alpha'];

                if (!is_integer($alpha)) {
                    return null;
                }

                $a = ((~$alpha) << 1) & 0xff;
                $skinBytes .= pack('C4', $color['red'], $color['green'], $color['blue'], $a);
            }
        }
        imagedestroy($img);
        $geometryData = file_get_contents($geometryPath);
        if ($geometryData === false) {
            return null;
        }
        return new Skin($skinID, $skinBytes, '', $geometryName, $geometryData);
    }

    /**
     * @param Player $player
     * @param string $stuffName
     * @return void
     */
    public function setSkin(Player $player, string $stuffName): void
    {
        try {
            $session = PracticeCore::getSessionManager()->getSession($player);
            $imagePath = $this->getSaveSkin($player->getName());
            $skin = $this->loadSkinAndApplyStuff($stuffName, $imagePath, $player->getSkin()->getSkinId());
            $cape = $session->cape;
            $capeData = ($cape !== '') ? $this->createCape($cape) : $player->getSkin()->getCapeData();
            $skin = new Skin($skin?->getSkinId() ?? $player->getSkin()->getSkinId(), $skin?->getSkinData() ?? $player->getSkin()->getSkinData(), $capeData, $skin?->getGeometryName() ?? $player->getSkin()->getGeometryName(), $skin?->getGeometryData() ?? $player->getSkin()->getGeometryData());
            $player->setSkin($skin);
            $player->sendSkin();
        } catch (Exception) {
            return;
        }
    }

    /**
     * @param string $name
     * @return string
     */
    public function getSaveSkin(string $name): string
    {
        return $this->saveSkin . $name . '.png';
    }

    /**
     * @param string $stuffName
     * @param string $imagePath
     * @param string $skinID
     * @return Skin|null
     * @throws JsonException
     */
    private function loadSkinAndApplyStuff(string $stuffName, string $imagePath, string $skinID): ?Skin
    {
        $size = getimagesize($imagePath);

        if ($size === false) {
            return null;
        }

        $imagePathh = $this->exportSkinToImage($imagePath, $stuffName, [$size[0], $size[1], 4]);

        if ($imagePathh === false) {
            return null;
        }

        $geometryPath = $this->artifactFolder . $stuffName . '.json';
        return $this->loadSkin($imagePathh, $geometryPath, $skinID, 'geometry.cosmetic/artifact');
    }

    /**
     * @param string $skinPath
     * @param string $stuffName
     * @param array $size
     * @return string|false
     */
    private function exportSkinToImage(string $skinPath, string $stuffName, array $size): string|false
    {
        $path = $this->artifactFolder;
        $down = imagecreatefrompng($skinPath);

        if ($down === false) {
            return false;
        }

        if ($size[0] * $size[1] * $size[2] === 65536) {
            $upper = $this->resizeImage($path . $stuffName . '.png', 128, 128);
        } else {
            $upper = $this->resizeImage($path . $stuffName . '.png', 64, 64);
        }

        if ($upper === null) {
            return false;
        }

        $alpha = imagecolorallocatealpha($upper, 0, 0, 0, 127);

        if ($alpha === false) {
            return false;
        }

        imagecolortransparent($upper, $alpha);
        imagealphablending($down, true);
        imagesavealpha($down, true);
        imagecopymerge($down, $upper, 0, 0, 0, 0, $size[0], $size[1], 100);
        imagepng($down, $this->dataFolder . 'temp.png', 2);
        return $this->dataFolder . 'temp.png';
    }

    /**
     * @param string $file
     * @param int $w
     * @param int $h
     * @return GdImage|null
     */
    private function resizeImage(string $file, int $w, int $h): ?GdImage
    {
        $size = getimagesize($file);

        if ($size === false) {
            return null;
        }

        [$width, $height] = $size;
        $ratio = $width / $height;
        if ($w / $h > $ratio) {
            $newWidth = $h * $ratio;
            $newHeight = $h;
        } else {
            $newHeight = $w / $ratio;
            $newWidth = $w;
        }
        $src = imagecreatefrompng($file);

        if ($src === false) {
            return null;
        }

        $dst = imagecreatetruecolor($w, $h);

        if ($dst === false) {
            return null;

        }
        $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);

        if ($transparent === false) {
            return null;
        }

        imagefill($dst, 0, 0, $transparent);
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, (int)$newWidth, (int)$newHeight, $width, $height);
        imagedestroy($src);
        return $dst;
    }

    /**
     * @param string $capeName
     * @return string
     */
    public function createCape(string $capeName): string
    {
        $path = PracticeCore::getInstance()->getDataFolder() . 'cosmetic/capes/' . "$capeName.png";
        $size = getimagesize($path);

        if ($size === false) {
            return '';
        }

        $src = imagecreatefrompng($path);

        if ($src === false) {
            return '';
        }

        $img = imagecreatetruecolor($size[0], $size[1]);

        if ($img === false) {
            return '';
        }

        imagealphablending($img, false);
        imagesavealpha($img, true);
        imagecopy($img, $src, 0, 0, 0, 0, $size[0], $size[1]);
        $bytes = '';
        foreach (range(0, $size[1] - 1) as $y) {
            foreach (range(0, $size[0] - 1) as $x) {
                $rgba = imagecolorat($img, (int)$x, (int)$y);
                $a = (127 - (($rgba >> 24) & 0xFF)) * 2;
                $r = ($rgba >> 16) & 0xFF;
                $g = ($rgba >> 8) & 0xFF;
                $b = $rgba & 0xFF;
                $bytes .= pack('C*', $r, $g, $b, $a);
            }
        }
        imagedestroy($img);
        imagedestroy($src);
        return $bytes;
    }
}
