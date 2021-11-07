<?php


namespace BauboLP\LobbySystem\Commands;


use BauboLP\LobbySystem\LobbySystem;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class SpawnCommand extends Command
{

    public function __construct()
    {
        parent::__construct('spawn', "LobbySystem Command", "", ['']);
        $this->setPermission('lobby.spawn');
        $this->setPermissionMessage(LobbySystem::Prefix.TextFormat::RED.'No Permissions!');
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if(!$sender instanceof Player) return;
        if(!$this->testPermission($sender) || empty($args[0])) {
            $sender->teleport($sender->getServer()->getDefaultLevel()->getSafeSpawn()->add(0, 1));
            return;
        }

        if(empty($args[0]) || empty($args[1])) {
            $sender->sendMessage(LobbySystem::Prefix.TextFormat::RED."/spawn <add|remove> <Game>");
            return;
        }

        $game = $args[1];
        $vector3 = $sender->asVector3();

        switch ($args[0]) {
            case "add":
                LobbySystem::getConfigProvider()->setGameSpawn($game, $vector3);
                $sender->sendMessage(LobbySystem::Prefix.TextFormat::GREEN."Der Spawn für das Game ".TextFormat::AQUA.$game.TextFormat::GREEN." wurde gesetzt.");
                break;
            case "remove":
                LobbySystem::getConfigProvider()->removeGameSpawn($game);
                $sender->sendMessage(LobbySystem::Prefix.TextFormat::GREEN."Der Spawn für das Game ".TextFormat::AQUA.$game.TextFormat::GREEN." wurde entfernt.");
                break;
        }
    }
}