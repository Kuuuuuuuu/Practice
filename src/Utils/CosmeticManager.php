<?php

declare(strict_types=1);

namespace Kuu\Utils;

use Exception;
use GdImage;
use Kuu\PracticeCore;
use Kuu\PracticePlayer;
use pocketmine\entity\Skin;
use pocketmine\player\Player;
use pocketmine\Server;
use RuntimeException;
use function ord;
use function strlen;

class CosmeticManager
{
    public string $dataFolder;
    public string $resourcesFolder;
    public string $artifactFolder;
    public string $saveSkin;
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

    public function __construct()
    {
        $this->dataFolder = PracticeCore::getInstance()->getDataFolder() . 'cosmetic/';
        $this->saveSkin = $this->dataFolder . 'skin/';
        if (!is_dir($this->dataFolder)) {
            mkdir($this->dataFolder);
            mkdir($this->saveSkin);
        }
        $this->resourcesFolder = PracticeCore::getInstance()->getDataFolder() . 'cosmetic/';
        $this->artifactFolder = $this->resourcesFolder . 'artifact/';
        $this->capeFolder = $this->resourcesFolder . 'capes/';
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

    public function getCapes(): array
    {
        $list = [];
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
            Server::getInstance()->getLogger()->error((string)$e);
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
            Server::getInstance()->getLogger()->error((string)$e);
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
            $img = imagecreatefrompng($imagePath);
            $size = getimagesize($imagePath);
            $skinBytes = '';
            for ($y = 0; $y < $size[1]; $y++) {
                for ($x = 0; $x < $size[0]; $x++) {
                    $pixelColor = imagecolorat($img, $x, $y);
                    $a = ((~($pixelColor >> 24)) << 1) & 0xff;
                    $r = ($pixelColor >> 16) & 0xff;
                    $g = ($pixelColor >> 8) & 0xff;
                    $b = $pixelColor & 0xff;
                    $skinBytes .= chr($r) . chr($g) . chr($b) . chr($a);
                }
            }
            imagedestroy($img);
            return new Skin($skinID, $skinBytes, '', $geometryName, file_get_contents($geometryPath));
        } catch (Exception $e) {
            Server::getInstance()->getLogger()->error((string)$e);
            return null;
        }
    }

    public function setSkin(Player $player, string $stuffName): void
    {
        try {
            if ($player instanceof PracticePlayer) {
                $imagePath = $this->getSaveSkin($player->getName());
                $skin = $this->loadSkinAndApplyStuff($stuffName, $imagePath, $player->getSkin()->getSkinId());
                $cape = $player->getCape();
                $capeData = ($cape !== '') ? $this->createCape($player->getCape()) : $player->getSkin()->getCapeData();
                $skin = new Skin($skin?->getSkinId() ?? $player->getSkin()->getSkinId(), $skin?->getSkinData() ?? $player->getSkin()->getSkinData(), $capeData, $skin?->getGeometryName() ?? $player->getSkin()->getGeometryName(), $skin?->getGeometryData() ?? $player->getSkin()->getGeometryData());
                $player->setSkin($skin);
                $player->sendSkin();
            }
        } catch (Exception $e) {
            Server::getInstance()->getLogger()->error((string)$e);
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
            Server::getInstance()->getLogger()->error((string)$e);
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
            Server::getInstance()->getLogger()->error((string)$e);
            return null;
        }
    }

    private function resizeImage($file, $w, $h): GdImage|bool|null
    {
        try {
            [$width, $height] = getimagesize($file);
            $r = $width / $height;
            if ($w / $h > $r) {
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
            Server::getInstance()->getLogger()->error((string)$e);
            return null;
        }
    }

    public function createCape($capeName): ?string
    {
        try {
            $path = PracticeCore::getInstance()->getDataFolder() . 'cosmetic/capes/' . "$capeName.png";
            $img = imagecreatefrompng($path);
            $bytes = '';
            $l = (int)getimagesize($path)[1];
            for ($y = 0; $y < $l; $y++) {
                for ($x = 0; $x < 64; $x++) {
                    $rgba = imagecolorat($img, $x, $y);
                    $a = ((~($rgba >> 24)) << 1) & 0xff;
                    $r = ($rgba >> 16) & 0xff;
                    $g = ($rgba >> 8) & 0xff;
                    $b = $rgba & 0xff;
                    $bytes .= chr($r) . chr($g) . chr($b) . chr($a);
                }
            }
            imagedestroy($img);
            return $bytes;
        } catch (Exception $e) {
            Server::getInstance()->getLogger()->error((string)$e);
            return null;
        }
    }
}