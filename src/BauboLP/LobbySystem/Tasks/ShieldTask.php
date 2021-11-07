<?php


namespace BauboLP\LobbySystem\Tasks;


use BauboLP\Core\Player\RyzerPlayerProvider;
use BauboLP\Core\Provider\RankProvider;
use BauboLP\LobbySystem\LobbySystem;
use pocketmine\scheduler\Task;
use pocketmine\Server;

class ShieldTask extends Task
{

    public function onRun(int $currentTick)
    {
        $shieldPlayers = array();
        $server = Server::getInstance();
        foreach ($server->getOnlinePlayers() as $player) {
            if (($obj = LobbySystem::getPlayerCache($player->getName())) != null) {
                if ($obj->isShield() && !$obj->isInGame() && $obj->isAddonsActivated()) {
                    $shieldPlayers[] = $player->getName();
                }
            }
        }

        foreach ($shieldPlayers as $shieldPlayer) {
            foreach ($server->getOnlinePlayers() as $player) {
                if (($sp = $server->getPlayerExact($shieldPlayer)) != null && ($obj = LobbySystem::getPlayerCache($player->getName())) != null) {
                  if(($corePlayer = RyzerPlayerProvider::getRyzerPlayer($player->getName())) != null && ($corePlayer2 = RyzerPlayerProvider::getRyzerPlayer($shieldPlayer)) != null)
                    if ($sp->distance($player) <= 5 && !$obj->isInGame() && RankProvider::getRankJoinPower($corePlayer->getRank()) < RankProvider::getRankJoinPower($corePlayer2->getRank())) {
                        $player->knockBack($sp, 0, $player->getX() - $sp->getX(), $player->getZ() - $sp->getZ(), 1.7);
                    }
                }
            }
        }
    }
}