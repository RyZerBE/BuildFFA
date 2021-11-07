<?php


namespace BauboLP\LobbySystem\Tasks;


use BauboLP\LobbySystem\LobbySystem;
use BauboLP\LobbySystem\Provider\AnimationProvider;
use pocketmine\block\Block;
use pocketmine\scheduler\Task;
use pocketmine\Server;

class DJTask extends Task
{

    public function onRun(int $currentTick)
    {
        foreach (Server::getInstance()->getOnlinePlayers() as $player) {
            if (($obj = LobbySystem::getPlayerCache($player->getName())) != null) {
                if (!$obj->isFlyActivated() && $player->getGamemode() != 1) {
                    if ($player->isFlying()) {
                        $player->playSound('mob.enderdragon.growl', 5, 1.0, [$player]);
                        $player->knockBack($player, 0, $player->getDirectionVector()->getX(), $player->getDirectionVector()->getZ(), 2.4);
                        $player->setFlying(false);
                        $player->setAllowFlight(false);
                        AnimationProvider::$djCooldown[] = $player->getName();
                    }
                }
            }
            if ($player->getLevel()->getBlock($player->getSide(0))->getId() != Block::AIR && in_array($player->getName(), AnimationProvider::$djCooldown)) {
                if (($obj = LobbySystem::getPlayerCache($player->getName())) != null) {
                    if (!$obj->isInGame()) {
                        $player->setAllowFlight(true);
                    }
                }
                unset(AnimationProvider::$djCooldown[array_search($player->getName(), AnimationProvider::$djCooldown)]);
            }
        }
    }
}