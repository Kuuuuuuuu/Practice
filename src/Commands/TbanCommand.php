<?php

declare(strict_types=1);

namespace Nayuki\Commands;

use Nayuki\PracticeConfig;
use Nayuki\PracticeCore;
use Nayuki\Utils\Forms\CustomForm;
use Nayuki\Utils\Forms\SimpleForm;
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
            'tban',
            'Tempban a player',
            '/tban <player> || /tban',
            ['tempban', 'tb']
        );
        $this->setPermission('tban.command');
    }

    public function execute(CommandSender $sender, string $commandLabel, ?array $args): void
    {
        if ($sender instanceof Player) {
            if ($sender->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
                if (isset($args[0])) {
                    PracticeCore::getInstance()->targetPlayer[$sender->getName()] = $args[0];
                    $this->openTbanUI($sender);
                } else {
                    $this->openPlayerListUI($sender);
                }
            } else {
                $sender->sendMessage(PracticeCore::getPrefixCore() . '§cYou cannot execute this command.');
            }
        } else {
            $sender->sendMessage(PracticeCore::getPrefixCore() . '§cYou can only use this command in-Game!');
        }
    }

    public function openTbanUI(Player $player): bool
    {
        $form = new CustomForm(function (Player $player, array $data = null) {
            $result = $data;
            if ($result === null) {
                return true;
            }
            if (isset(PracticeCore::getInstance()->targetPlayer[$player->getName()])) {
                if (PracticeCore::getInstance()->targetPlayer[$player->getName()] === $player->getName()) {
                    $player->sendMessage(PracticeCore::getPrefixCore() . "§cYou can't ban yourself");
                    return true;
                }
                $now = time();
                /** @phpstan-ignore-next-line */
                $day = ($data[1] * 86400);
                /** @phpstan-ignore-next-line */
                $hour = ($data[2] * 3600);
                /** @phpstan-ignore-next-line */
                if ($data[3] > 1) {
                    $min = ($data[3] * 60);
                } else {
                    $min = 60;
                }
                $banTime = $now + $day + $hour + $min;
                $banInfo = PracticeCore::getInstance()->BanDatabase->prepare('INSERT OR REPLACE INTO banPlayers (player, banTime, reason, staff) VALUES (:player, :banTime, :reason, :staff);');
                /** @phpstan-ignore-next-line */
                $banInfo->bindValue(':player', PracticeCore::getInstance()->targetPlayer[$player->getName()]);
                /** @phpstan-ignore-next-line */
                $banInfo->bindValue(':banTime', $banTime);
                /** @phpstan-ignore-next-line */
                $banInfo->bindValue(':reason', $data[4]);
                /** @phpstan-ignore-next-line */
                $banInfo->bindValue(':staff', $player->getName());
                /** @phpstan-ignore-next-line */
                $banInfo->execute();
                $target = Server::getInstance()->getPlayerExact(PracticeCore::getInstance()->targetPlayer[$player->getName()]);
                if ($target instanceof Player) {
                    $target->kick(str_replace(['{day}', '{hour}', '{minute}', '{reason}', '{staff}'], [$data[1], $data[2], $data[3], $data[4], $player->getName()], "§cYou Are Banned\n§6Reason : §f{reason}\n§6Unban At §f: §e{day} D §f| §e{hour} H §f| §e{minute} M"));
                }
                Server::getInstance()->broadcastMessage(str_replace(['{player}', '{day}', '{hour}', '{minute}', '{reason}', '{staff}'], [PracticeCore::getInstance()->targetPlayer[$player->getName()], $data[1], $data[2], $data[3], $data[4], $player->getName()], "§f––––––––––––––––––––––––\n§ePlayer §f: §c{player}\n§eHas banned: §c{day}§eD §f| §c{hour}§eH §f| §c{minute}§eM\n§eReason: §c{reason}\n§f––––––––––––––––––––––––§f"));
                unset(PracticeCore::getInstance()->targetPlayer[$player->getName()]);
            }
            return true;
        });
        $list[] = PracticeCore::getInstance()->targetPlayer[$player->getName()];
        $form->setTitle(PracticeConfig::Server_Name . '§eBanSystem');
        $form->addDropdown("\nTarget", $list);
        $form->addSlider('Day/s', 0, 30, 1);
        $form->addSlider('Hour/s', 0, 24, 1);
        $form->addSlider('Minute/s', 0, 60, 1);
        $form->addInput('Reason');
        $player->sendForm($form);
        return true;
    }

    public function openPlayerListUI(Player $player): bool
    {
        $form = new SimpleForm(function (Player $player, $data = null) {
            $target = $data;
            if ($target === null) {
                return true;
            }
            PracticeCore::getInstance()->targetPlayer[$player->getName()] = $target;
            $this->openTbanUI($player);
            return true;
        });
        $form->setTitle(PracticeConfig::Server_Name . '§eBanSystem');
        $form->setContent('§c§lChoose Player');
        foreach (PracticeCore::getSessionManager()->getSessions() as $session) {
            $online = $session->getPlayer();
            $form->addButton($online->getName(), -1, '', $online->getName());
        }
        $player->sendForm($form);
        return true;
    }
}
