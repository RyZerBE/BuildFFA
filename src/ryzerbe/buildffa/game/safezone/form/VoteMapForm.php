<?php

declare(strict_types=1);

namespace ryzerbe\buildffa\game\safezone\form;

use jojoe77777\FormAPI\SimpleForm;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use ryzerbe\buildffa\BuildFFA;
use ryzerbe\buildffa\game\map\MapManager;

class VoteMapForm {
    public static function open(Player $player): void {
        $form = new SimpleForm(function(Player $player, mixed $data): void {
            if($data === null) return;
            $map = MapManager::getMapByName($data);
            if($map === null) {
                $player->sendMessage(BuildFFA::PREFIX."§cSomething went wrong...");
                return;
            }
            if($map->hasVoted($player)) {
                $player->playSound("note.bass", 5.0, 1.0, [$player]);
                return;
            }
            $map->addVote($player);
            $player->playSound("random.levelup", 5.0, 1.0, [$player]);
        });
        foreach(MapManager::getMaps() as $map) {
            $form->addButton(($map->hasVoted($player) ? TextFormat::GREEN : TextFormat::DARK_GRAY)."⇨ ".TextFormat::GREEN.$map->getName()."\n§r§7§o".$map->getVotes()." Votes", $map->imageType, $map->image, $map->getName());
        }
        $form->sendToPlayer($player);
    }
}