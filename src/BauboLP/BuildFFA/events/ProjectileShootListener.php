<?php


namespace BauboLP\BuildFFA\events;


use BauboLP\BuildFFA\provider\GameProvider;
use pocketmine\entity\projectile\Arrow;
use pocketmine\entity\projectile\EnderPearl;
use pocketmine\event\entity\ProjectileLaunchEvent;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\Server;

class ProjectileShootListener implements Listener
{

    public function shoot(ProjectileLaunchEvent $event)
    {
        $entity = $event->getEntity();
        if($entity instanceof EnderPearl || $entity instanceof \ryzerbe\core\entity\EnderPearl) {
            $shooter = $entity->getOwningEntity();
            if($shooter instanceof Player) {
                if(($obj = GameProvider::getBuildFFAPlayer($shooter->getName())) != null) {
                    if($obj->getCooldowns()['ep'] != null) {
                        $event->setCancelled();
                    }else {
                        $obj->addEpCooldown();
                    }
                }
            }
        }else if($entity instanceof Arrow) {
            if($entity->getOwningEntity()->distance(Server::getInstance()->getLevelByName(GameProvider::getMap())->getSafeSpawn()) <= 8) {
                $event->setCancelled();
            }
        }
    }
}