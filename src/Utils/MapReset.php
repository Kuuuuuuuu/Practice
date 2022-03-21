<?php

declare(strict_types=1);

namespace Kohaku\Core\Utils;

use InvalidArgumentException;
use Kohaku\Core\Loader;
use pocketmine\Server;
use pocketmine\world\World;
use ZipArchive;

class MapReset
{

    public function saveMap(World $world): bool
    {
        $worldPath = Server::getInstance()->getDataPath() . "worlds" . DIRECTORY_SEPARATOR . $world->getFolderName();
        $zipPath = Loader::getInstance()->getDataFolder() . "Maps" . DIRECTORY_SEPARATOR . $world->getFolderName() . ".zip";
        $zip = new ZipArchive();
        if (file_exists($zipPath)) {
            Loader::getInstance()->getLogger()->notice("File already exists, proceed to overwrite: $zipPath");
            unlink($zipPath);
        }
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            $dir = opendir($worldPath);
            while ($file = readdir($dir)) {
                if (is_file($worldPath . $file)) {
                    $zip->addFile($worldPath . $file, $file);
                }
            }
            $zip->close();
            return true;
        }
        $zip->close();
        return false;
    }

    public function loadMap(string $folderName): ?World
    {
        if (!Server::getInstance()->getWorldManager()->getWorldByName($folderName)) {
            return null;
        }
        if (Server::getInstance()->getWorldManager()->isWorldLoaded($folderName)) {
            Server::getInstance()->getWorldManager()->unloadWorld(Server::getInstance()->getWorldManager()->getWorldByName($folderName));
            $this->deleteDir(Server::getInstance()->getDataPath() . "worlds" . DIRECTORY_SEPARATOR . $folderName);
        }
        $zipPath = Loader::getInstance()->getDataFolder() . "Maps" . DIRECTORY_SEPARATOR . $folderName . ".zip";
        if (!file_exists($zipPath)) {
            Server::getInstance()->getLogger()->error("Could not reload map ($folderName). File wasn't found");
            return null;
        }
        $zipArchive = new ZipArchive();
        $zipArchive->open($zipPath);
        $zipArchive->extractTo(Server::getInstance()->getDataPath() . "worlds");
        $zipArchive->close();
        Server::getInstance()->getWorldManager()->loadWorld($folderName);
        return Server::getInstance()->getWorldManager()->getWorldByName($folderName);
    }

    public function deleteDir($dirPath)
    {
        if (!is_dir($dirPath)) {
            throw new InvalidArgumentException("$dirPath must be a directory");
        }
        if (!str_ends_with($dirPath, '/')) {
            $dirPath .= '/';
        }
        $files = glob($dirPath . '*', GLOB_MARK);
        foreach ($files as $file) {
            if (is_dir($file)) {
                self::deleteDir($file);
            } else {
                unlink($file);
            }
        }
        rmdir($dirPath);
    }
}