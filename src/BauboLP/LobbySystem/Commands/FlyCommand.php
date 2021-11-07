<?php


namespace BauboLP\LobbySystem\Commands;


use BauboLP\LobbySystem\LobbySystem;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class FlyCommand extends Command
{

    public function __construct()
    {
        parent::__construct("fly", "Activate/Disable your Fly-Mode", "", ['']);
        $this->setPermission("lobby.fly");
        $this->setPermissionMessage(LobbySystem::Prefix.TextFormat::RED."You cannot use this command. Shop: shop.ryzer.be");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if(!$sender instanceof Player) return;
        if(!$this->testPermission($sender)) return;

        if(($obj = LobbySystem::getPlayerCache($sender->getName())) != null) {
            if($obj->playingJumpAndRun() || $obj->isInGame()) {
                return;
            }
            if($obj->isFlyActivated()) {
                $obj->getPlayer()->setFlying(false);
                $obj->getPlayer()->setAllowFlight(false);
                $obj->setFly(false);
                $obj->getPlayer()->sendTitle(TextFormat::RED."✕ ".TextFormat::GRAY."FlyMode", "", 20, 20, 20);
            }else {
                $obj->getPlayer()->setAllowFlight(true);
                $obj->setFly(true);
                $obj->getPlayer()->sendTitle(TextFormat::GREEN."✓ ".TextFormat::GRAY."FlyMode", "", 20, 20, 20);
            }
        }
    }
}