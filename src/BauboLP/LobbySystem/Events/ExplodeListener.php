<?php


namespace BauboLP\LobbySystem\Events;


use pocketmine\event\entity\EntityExplodeEvent;
use pocketmine\event\Listener;

class ExplodeListener implements Listener
{

    public function explode(EntityExplodeEvent $event)
    {
        $event->setBlockList([]);
    }
}