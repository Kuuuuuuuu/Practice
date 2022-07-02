<?php

declare(strict_types=1);

namespace Kuu\Commands;

use Kuu\Lib\FormAPI\SimpleForm;
use Kuu\PracticeConfig;
use Kuu\PracticeCore;
use Kuu\Utils\Discord\DiscordWebhook;
use Kuu\Utils\Discord\DiscordWebhookUtils;
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

    public function execute(CommandSender $sender, string $commandLabel, ?array $args): void
    {
        if ($sender instanceof Player) {
            if ($sender->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
                $this->openTcheckUI($sender);
            } else {
                $sender->sendMessage(PracticeCore::getPrefixCore() . '§cYou cannot execute this command.');
            }
        } else {
            $sender->sendMessage(PracticeCore::getPrefixCore() . '§cYou can only use this command in-game!');
        }
    }

    public function openTcheckUI($player): bool
    {
        $form = new SimpleForm(function (Player $player, $data = null) {
            if ($data === null) {
                return true;
            }
            PracticeCore::getCaches()->targetPlayer[$player->getName()] = $data;
            $this->openInfoUI($player);
            return true;
        });
        $banInfo = PracticeCore::getInstance()->BanData->query('SELECT * FROM banPlayers;');
        $array = $banInfo->fetchArray(SQLITE3_ASSOC);
        if (empty($array)) {
            $player->sendMessage(PracticeCore::getPrefixCore() . '§aNo ban players');
            return true;
        }
        $form->setTitle(PracticeConfig::Server_Name . '§eBanSystem');
        $form->setContent('§c§lChoose player');
        $banInfo = PracticeCore::getInstance()->BanData->query('SELECT * FROM banPlayers;');
        while ($resultArr = $banInfo->fetchArray(SQLITE3_ASSOC)) {
            $banPlayer = $resultArr['player'];
            $form->addButton(TextFormat::BOLD . $banPlayer, -1, '', $banPlayer);
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
            if ($result === 0) {
                $banplayer = PracticeCore::getCaches()->targetPlayer[$player->getName()];
                $banInfo = PracticeCore::getInstance()->BanData->query("SELECT * FROM banPlayers WHERE player = '$banplayer';");
                $array = $banInfo->fetchArray(SQLITE3_ASSOC);
                if (!empty($array)) {
                    PracticeCore::getInstance()->BanData->query("DELETE FROM banPlayers WHERE player = '$banplayer';");
                    $player->sendMessage(str_replace(['{player}'], [$banplayer], PracticeCore::getPrefixCore() . '§b{player} §ahas been unban'));
                    $web = new DiscordWebhook(PracticeCore::getInstance()->getConfig()->get('Webhook'));
                    $msg = new DiscordWebhookUtils();
                    $msg->setContent('>>> ' . str_replace(['{player}'], [$banplayer], PracticeCore::getPrefixCore() . '§a{player} has been unban'));
                    $web->send($msg);
                }
                unset(PracticeCore::getCaches()->targetPlayer[$player->getName()]);
            }
            return true;
        });
        $banPlayer = PracticeCore::getCaches()->targetPlayer[$player->getName()];
        $banInfo = PracticeCore::getInstance()->BanData->query("SELECT * FROM banPlayers WHERE player = '$banPlayer';");
        $array = $banInfo->fetchArray(SQLITE3_ASSOC);
        if (!empty($array)) {
            $banTime = $array['banTime'];
            $reason = $array['reason'];
            $staff = $array['staff'];
            $now = time();
            if ($banTime < $now) {
                $banplayer = PracticeCore::getCaches()->targetPlayer[$player->getName()];
                $banInfo = PracticeCore::getInstance()->BanData->query("SELECT * FROM banPlayers WHERE player = '$banplayer';");
                $array = $banInfo->fetchArray(SQLITE3_ASSOC);
                if (!empty($array)) {
                    PracticeCore::getInstance()->BanData->query("DELETE FROM banPlayers WHERE player = '$banplayer';");
                    $player->sendMessage(str_replace(['{player}'], [$banplayer], PracticeCore::getPrefixCore() . '§a{player} Has Auto Unban Already'));
                }
                unset(PracticeCore::getCaches()->targetPlayer[$player->getName()]);
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
        $form->setContent(str_replace(['{day}', '{hour}', '{minute}', '{second}', '{reason}', '{staff}'], [$day, $hour, $minute, $second, $reason, $staff], '§bInformation: nDay: §a{day} n§bHour: §a{hour} n§bMinute: §a{minute} n§bSecond: §a{second} n§bReason: §a{reason}'));
        $form->addButton('§aUnban');
        $player->sendForm($form);
        return true;
    }
}