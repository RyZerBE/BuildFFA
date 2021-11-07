<?php


namespace BauboLP\LobbySystem\Tasks;


use BauboLP\Cloud\Bungee\BungeeAPI;
use BauboLP\LobbySystem\LobbySystem;
use pocketmine\scheduler\Task;
use pocketmine\Server;

class ScoreboardTask extends Task
{

    public function onRun(int $currentTick)
    {
        if(BungeeAPI::getRandomPlayer() == null) return;

        foreach (Server::getInstance()->getOnlinePlayers() as $player) {
            if(($obj = LobbySystem::getPlayerCache($player->getName())) != null) {
                $obj->updateScoreboard();
            }
        }
    }
}