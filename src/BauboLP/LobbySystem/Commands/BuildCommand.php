<?php


namespace BauboLP\LobbySystem\Commands;


use BauboLP\LobbySystem\LobbySystem;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class BuildCommand extends Command
{

    public function __construct()
    {
        parent::__construct('build', "Build-Mode", "", ['']);
        $this->setPermission('lobby.build');
        $this->setPermissionMessage(LobbySystem::Prefix.TextFormat::RED."No Permissions!");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if(!$sender instanceof Player) return;
        if(!$this->testPermission($sender)) return;

         if(($obj = LobbySystem::getPlayerCache($sender->getName())) != null) {
             if($obj->isBuildModeActivated()) {
                 $obj->setBuild(false);
                 $obj->getPlayer()->addTitle(TextFormat::RED."✕ ".TextFormat::GRAY."BuildMode");
             }else {
                 $obj->setBuild(true);
                 $obj->getPlayer()->addTitle(TextFormat::GREEN."✓ ".TextFormat::GRAY."BuildMode");
             }
         }
    }

}