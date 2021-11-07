<?php


namespace BauboLP\LobbySystem\Events;


use pocketmine\event\level\ChunkLoadEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\tile\Sign;

class ExhaustListener implements Listener
{

    public function onExhaust(PlayerExhaustEvent $event)
    {
        $event->getPlayer()->setFood(20);
    }

    public function loadChunk(ChunkLoadEvent $event) {
        foreach ($event->getChunk()->getTiles() as $tile) {
            if ($tile instanceof Sign) {
                if ($tile->getText()[0] == "ClanWar Queue") {
                    $type = $tile->getText()[2];
                    if (strtolower($type) == "elo") {
                        $tile->setText(
                            "§foO §o§4Clan§fWar §fOo",
                            "",
                            "§7[§aElo§7]",
                            "§b- §cClick to join §b-"
                        );
                    //    var_dump("Elo");
                    } else if (strtolower($type) == "fun") {
                        $tile->setText(
                            "§foO §o§4Clan§fWar §fOo",
                            "",
                            "§7[§aFun§7]",
                            "§b- §cClick to join §b-"
                        );
                       // var_dump("Fun");
                    }
                }
            }
        }
    }
}