<?php

namespace Kohaku\Core\Utils;

use JetBrains\PhpStorm\Pure;
use Kohaku\Core\Loader;

class CapeUtils
{

    #[Pure] public static function getInstance(): CapeUtils
    {
        return new CapeUtils();
    }

    public function createCape($capeName): string
    {
        $path = Loader::getInstance()->getDataFolder() . "$capeName.png";
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
    }

    public function getCapes(): array
    {
        $list = array();
        foreach (array_diff(scandir(Loader::getInstance()->getDataFolder()), ["..", "."]) as $data) {
            $dat = explode(".", $data);
            if ($dat[1] == "png") {
                $list[] = $dat[0];
            }
        }
        return $list;
    }
}