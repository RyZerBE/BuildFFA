<?php


namespace BauboLP\LobbySystem\Tasks;


use BauboLP\Core\Provider\LanguageProvider;
use BauboLP\LobbySystem\LobbySystem;
use BauboLP\LobbySystem\Provider\AnimationProvider;
use pocketmine\scheduler\Task;
use pocketmine\Server;

class WingTask extends Task
{

    public function onRun(int $currentTick)
    {
        if(AnimationProvider::$addonBlocker == true) return;
        foreach (Server::getInstance()->getOnlinePlayers() as $player) {
            if (($obj = LobbySystem::getPlayerCache($player->getName())) != null) {
                if ($obj->isAddonsActivated()) {
                    if ($obj->getWings() != null && $obj->getWing() != "") {
                        $wing = $obj->getWingObject();
                        if ($wing != null) {
                            $wing->draw($player->asPosition(), $player->yaw);
                        }
                    }
                }
            }
        }
    }
}