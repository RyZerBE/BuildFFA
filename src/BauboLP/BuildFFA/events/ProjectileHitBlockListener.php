<?php


namespace BauboLP\BuildFFA\events;


use pocketmine\entity\projectile\Arrow;
use pocketmine\event\entity\ProjectileHitBlockEvent;
use pocketmine\event\Listener;

class ProjectileHitBlockListener implements Listener
{

    public function hitBlock(ProjectileHitBlockEvent $event)
    {
        $entity = $event->getEntity();
        if ($entity instanceof Arrow || $entity instanceof \ryzerbe\core\entity\Arrow) {
            $entity->kill();
        }
    }
}