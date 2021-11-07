<?php


namespace BauboLP\LobbySystem\Events;


use BauboLP\NPC\entitys\NPCHuman;
use BauboLP\NPC\entitys\NPCModel;
use pocketmine\event\entity\EntityDespawnEvent;
use pocketmine\event\Listener;

class DespawnListener implements Listener
{

    public function despawn(EntityDespawnEvent $event)
    {
        $entity = $event->getEntity();
        if($entity instanceof NPCHuman || $entity instanceof NPCModel) {

        }
    }
}