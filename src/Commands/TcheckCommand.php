<?php

declare(strict_types=1);

namespace Kuu\Commands;

use Kuu\Loader;
use Kuu\Utils\DiscordUtils\DiscordWebhook;
use Kuu\Utils\DiscordUtils\DiscordWebhookUtils;
use Kuu\Utils\Forms\SimpleForm;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\permission\DefaultPermissions;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class TcheckCommand extends Command
{

    public function __construct()
    {
        parent::__construct(
            'tcheck',
            'Unban a player',
            '/tcheck'
        );
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if ($sender instanceof Player) {
            if ($sender->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
                $this->openTcheckUI($sender);
            } else {
                $sender->sendMessage(Loader::getPrefixCore() . '§cYou cannot execute this command.');
            }
        } else {
            $sender->sendMessage(Loader::getPrefixCore() . '§cYou can only use this command in-game!');
        }
    }

    public function openTcheckUI($player): bool
    {
        $form = new SimpleForm(function (Player $player, $data = null) {
            if ($data === null) {
                return true;
            }
            Loader::getInstance()->targetPlayer[$player->getName()] = $data;
            $this->openInfoUI($player);
            return true;
        });
        $banInfo = Loader::getInstance()->BanData->query('SELECT * FROM banPlayers;');
        $array = $banInfo->fetchArray(SQLITE3_ASSOC);
        if (empty($array)) {
            $player->sendMessage(Loader::getInstance()->MessageData['NoBanPlayers']);
            return true;
        }
        $form->setTitle(Loader::getInstance()->MessageData['BanListTitle']);
        $form->setContent(Loader::getInstance()->MessageData['BanListContent']);
        $banInfo = Loader::getInstance()->BanData->query('SELECT * FROM banPlayers;');
        $i = -1;
        while ($resultArr = $banInfo->fetchArray(SQLITE3_ASSOC)) {
            $banPlayer = $resultArr['player'];
            $form->addButton(TextFormat::BOLD . "$banPlayer", -1, '', $banPlayer);
            $i = $i + 1;
        }
        $player->sendForm($form);
        return true;
    }

    public function openInfoUI($player): bool
    {
        $form = new SimpleForm(function (Player $player, int $data = null) {
            $result = $data;
            if ($result === null) {
                return true;
            }
            if ($result == 0) {
                $banplayer = Loader::getInstance()->targetPlayer[$player->getName() ?? null];
                $banInfo = Loader::getInstance()->BanData->query("SELECT * FROM banPlayers WHERE player = '$banplayer';");
                $array = $banInfo->fetchArray(SQLITE3_ASSOC);
                if (!empty($array)) {
                    Loader::getInstance()->BanData->query("DELETE FROM banPlayers WHERE player = '$banplayer';");
                    $player->sendMessage(str_replace(['{player}'], [$banplayer], Loader::getInstance()->MessageData['UnBanPlayer']));
                    $web = new DiscordWebhook(Loader::getInstance()->getConfig()->get('api'));
                    $msg = new DiscordWebhookUtils();
                    $msg->setContent('>>> ' . str_replace(['{player}'], [$banplayer], Loader::getInstance()->MessageData['UnBanPlayer']));
                    $web->send($msg);
                }
                unset(Loader::getInstance()->targetPlayer[$player->getName()]);
            }
            return true;
        });
        $banPlayer = Loader::getInstance()->targetPlayer[$player->getName()];
        $banInfo = Loader::getInstance()->BanData->query("SELECT * FROM banPlayers WHERE player = '$banPlayer';");
        $array = $banInfo->fetchArray(SQLITE3_ASSOC);
        if (!empty($array)) {
            $banTime = $array['banTime'];
            $reason = $array['reason'];
            $staff = $array['staff'];
            $now = time();
            if ($banTime < $now) {
                $banplayer = Loader::getInstance()->targetPlayer[$player->getName()];
                $banInfo = Loader::getInstance()->BanData->query("SELECT * FROM banPlayers WHERE player = '$banplayer';");
                $array = $banInfo->fetchArray(SQLITE3_ASSOC);
                if (!empty($array)) {
                    Loader::getInstance()->BanData->query("DELETE FROM banPlayers WHERE player = '$banplayer';");
                    $player->sendMessage(str_replace(['{player}'], [$banplayer], Loader::getInstance()->MessageData['AutoUnBanPlayer']));
                }
                unset(Loader::getInstance()->targetPlayer[$player->getName()]);
                return true;
            }
            $remainingTime = $banTime - $now;
            $day = floor($remainingTime / 86400);
            $hourSeconds = $remainingTime % 86400;
            $hour = floor($hourSeconds / 3600);
            $minuteSec = $hourSeconds % 3600;
            $minute = floor($minuteSec / 60);
            $remainingSec = $minuteSec % 60;
            $second = ceil($remainingSec);
        }
        $form->setTitle(TextFormat::BOLD . $banPlayer);
        $form->setContent(str_replace(['{day}', '{hour}', '{minute}', '{second}', '{reason}', '{staff}'], [$day, $hour, $minute, $second, $reason, $staff], Loader::getInstance()->MessageData['InfoUIContent']));
        $form->addButton(Loader::getInstance()->MessageData['InfoUIUnBanButton']);
        $player->sendForm($form);
        return true;
    }
}