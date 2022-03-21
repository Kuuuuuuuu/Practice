<?php

declare(strict_types=1);

namespace Kohaku\Core\Arena;

use InvalidArgumentException;
use JetBrains\PhpStorm\Pure;
use Kohaku\Core\Loader;
use pocketmine\Server;
use pocketmine\world\World;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use ZipArchive;

class MapReset
{

    #[Pure] public static function getInstance(): MapReset
    {
        return new MapReset();
    }

    public function saveMap(World $level)
    {
        $level->save(true);
        $levelPath = Server::getInstance()->getDataPath() . "worlds" . DIRECTORY_SEPARATOR . $level->getFolderName();
        $zipPath = Loader::getInstance()->getDataFolder() . "saves" . DIRECTORY_SEPARATOR . $level->getFolderName() . ".zip";
        $zip = new ZipArchive();
        if (is_file($zipPath)) {
            unlink($zipPath);
        }
        $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(realpath($levelPath)), RecursiveIteratorIterator::LEAVES_ONLY);
        /** @var SplFileInfo $file */
        foreach ($files as $file) {
            if ($file->isFile()) {
                $filePath = $file->getPath() . DIRECTORY_SEPARATOR . $file->getBasename();
                $localPath = substr($filePath, strlen(Server::getInstance()->getDataPath() . "worlds"));
                $zip->addFile($filePath, $localPath);
            }
        }
        $zip->close();
    }

    public function loadMap(string $folderName): ?World
    {
        if (!Server::getInstance()->getWorldManager()->getWorldByName($folderName)) {
            return null;
        }
        if (Server::getInstance()->getWorldManager()->isWorldLoaded($folderName)) {
            Server::getInstance()->getWorldManager()->unloadWorld(Server::getInstance()->getWorldManager()->getWorldByName($folderName));
            $this->deleteDir(Server::getInstance()->getDataPath() . "worlds/$folderName");
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