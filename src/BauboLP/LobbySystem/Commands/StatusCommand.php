<?php


namespace BauboLP\LobbySystem\Commands;


use BauboLP\Core\Provider\LanguageProvider;
use BauboLP\Core\Ryzer;
use BauboLP\LobbySystem\LobbySystem;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class StatusCommand extends Command
{

    public function __construct()
    {
        parent::__construct("setstatus", "", "", []);
        $this->setPermission("lobby.status");
        $this->setPermissionMessage(Ryzer::PREFIX.TextFormat::RED."No Permissions!");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if(!$sender instanceof Player) return;
        if(!$this->testPermission($sender)) return;

        if(empty($args[0])) {
            $sender->sendMessage(LobbySystem::Prefix.TextFormat::RED."/setstatus <Status> | /setstatus reset");
            return;
        }

        $status = implode(" ", $args);
        if($args[0] == "reset") {
            $name = $sender->getName();
            $sender->sendMessage(LobbySystem::Prefix.LanguageProvider::getMessageContainer('lobby-status-reset', $sender->getName()));
            Ryzer::getAsyncConnection()->execute("UPDATE `Status` SET status='false' WHERE playername='$name'", "Lobby", null);
        }else {
            if(strlen($status) > 24) {
                $sender->sendMessage(LobbySystem::Prefix.LanguageProvider::getMessageContainer('lobby-status-too-big', $sender->getName(), ['#max' => 24]));
                return;
            }
            $name = $sender->getName();
            $sender->sendMessage(LobbySystem::Prefix.LanguageProvider::getMessageContainer('lobby-status-set', $sender->getName(), ['#status' => $status]));
            Ryzer::getAsyncConnection()->execute("UPDATE `Status` SET status='$status' WHERE playername='$name'", "Lobby", null);
        }
    }
}