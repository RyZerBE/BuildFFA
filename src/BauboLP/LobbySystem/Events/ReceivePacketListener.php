<?php


namespace BauboLP\LobbySystem\Events;


use BauboLP\LobbySystem\LobbySystem;
use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\RequestChunkRadiusPacket;

class ReceivePacketListener implements Listener
{

    public function packet(DataPacketReceiveEvent $event)
    {
        $packet = $event->getPacket();
        if ($packet instanceof RequestChunkRadiusPacket) {
            $player = $event->getPlayer();
            if ($player !== null && $player->isOnline()) {
                $player->setViewDistance(LobbySystem::VIEW_DISTANCE);
            }
        }
    }
}