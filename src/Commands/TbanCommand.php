<?php /** @noinspection PhpParamsInspection */
/** @noinspection PhpArrayToStringConversionInspection */

/** @noinspection PhpPropertyOnlyWrittenInspection */

declare(strict_types=1);

namespace Kohaku\Commands;

use Kohaku\Loader;
use Kohaku\Utils\DiscordUtils\DiscordWebhook;
use Kohaku\Utils\DiscordUtils\DiscordWebhookUtils;
use Kohaku\Utils\Forms\CustomForm;
use Kohaku\Utils\Forms\SimpleForm;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\permission\DefaultPermissions;
use pocketmine\player\Player;
use pocketmine\Server;

class TbanCommand extends Command
{

    public function __construct()
    {
        parent::__construct(
            "tban",
            "Tempban a player",
            "/tban <player> or /tban",
            ["tempban", "tb"]
        );
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if ($sender instanceof Player) {
            if ($sender->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
                if ($args == null) {
                    $this->openPlayerListUI($sender);
                } else {
                    Loader::getInstance()->targetPlayer[$sender->getName()] = $args[0];
                    $this->openTbanUI($sender);
                }
            } else {
                $sender->sendMessage(Loader::getPrefixCore() . "§cYou cannot execute this command.");
            }
        } else {
            $sender->sendMessage(Loader::getPrefixCore() . "§cYou can only use this command in-game!");
        }
    }

    public function openPlayerListUI($player): bool
    {
        $form = new SimpleForm(function (Player $player, $data = null) {
            $target = $data;
            if ($target === null) {
                return true;
            }
            Loader::getInstance()->targetPlayer[$player->getName()] = $target;
            $this->openTbanUI($player);
            return true;
        });
        $form->setTitle(Loader::getInstance()->MessageData["PlayerListTitle"]);
        $form->setContent(Loader::getInstance()->MessageData["PlayerListContent"]);
        foreach (Server::getInstance()->getOnlinePlayers() as $online) {
            $form->addButton($online->getName(), -1, "", $online->getName());
        }
        $player->sendForm($form);
        return true;
    }

    public function openTbanUI($player): bool
    {
        $form = new CustomForm(function (Player $player, array $data = null) {
            $result = $data;
            if ($result === null) {
                return true;
            }
            if (isset(Loader::getInstance()->targetPlayer[$player->getName()])) {
                if (Loader::getInstance()->targetPlayer[$player->getName()] == $player->getName()) {
                    $player->sendMessage(Loader::getInstance()->MessageData["BanMyself"]);
                    return true;
                }
                $now = time();
                $day = ($data[1] * 86400);
                $hour = ($data[2] * 3600);
                if ($data[3] > 1) {
                    $min = ($data[3] * 60);
                } else {
                    $min = 60;
                }
                $banTime = $now + $day + $hour + $min;
                $banInfo = Loader::getInstance()->BanData->prepare("INSERT OR REPLACE INTO banPlayers (player, banTime, reason, staff) VALUES (:player, :banTime, :reason, :staff);");
                $banInfo->bindValue(":player", Loader::getInstance()->targetPlayer[$player->getName()]);
                $banInfo->bindValue(":banTime", $banTime);
                $banInfo->bindValue(":reason", $data[4]);
                $banInfo->bindValue(":staff", $player->getName());
                $banInfo->execute();
                $target = Server::getInstance()->getPlayerExact(Loader::getInstance()->targetPlayer[$player->getName()]);
                if ($target instanceof Player) {
                    $target->kick(str_replace(["{day}", "{hour}", "{minute}", "{reason}", "{staff}"], [$data[1], $data[2], $data[3], $data[4], $player->getName()], Loader::getInstance()->MessageData["KickBanMessage"]));
                }
                $web = new DiscordWebhook(Loader::getInstance()->getConfig()->get("api"));
                $msg = new DiscordWebhookUtils();
                $msg2 = str_replace(["@here", "@everyone"], "", $data[4]);
                $msg->setContent(">>> " . $player->getName() . " has banned " . Loader::getInstance()->targetPlayer[$player->getName()] . " for " . $data[1] . " days, " . $data[2] . " hours, " . $data[3] . " minutes. Reason: " . $msg2);
                $web->send($msg);
                Server::getInstance()->broadcastMessage(str_replace(["{player}", "{day}", "{hour}", "{minute}", "{reason}", "{staff}"], [Loader::getInstance()->targetPlayer[$player->getName()], $data[1], $data[2], $data[3], $data[4], $player->getName()], Loader::getInstance()->MessageData["BroadcastBanMessage"]));
                unset(Loader::getInstance()->targetPlayer[$player->getName()]);

            }
            return true;
        });
        $list[] = Loader::getInstance()->targetPlayer[$player->getName()];
        $form->setTitle("§dNeptune §eBanSystem");
        $form->addDropdown("\nTarget", $list);
        $form->addSlider("Day/s", 0, 30, 1);
        $form->addSlider("Hour/s", 0, 24, 1);
        $form->addSlider("Minute/s", 0, 60, 1);
        $form->addInput("Reason");
        $player->sendForm($form);
        return true;
    }
}