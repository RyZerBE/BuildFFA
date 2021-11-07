<?php


namespace BauboLP\BuildFFA\events;


use pocketmine\event\Listener;
use pocketmine\event\player\PlayerExhaustEvent;

class FeedListener implements Listener
{

    public function exhaust(PlayerExhaustEvent $event)
    {
        $event->getPlayer()->setFood(20.0);
    }
}