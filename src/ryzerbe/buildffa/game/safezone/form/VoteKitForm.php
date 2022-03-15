<?php

declare(strict_types=1);

namespace ryzerbe\buildffa\game\safezone\form;

use jojoe77777\FormAPI\SimpleForm;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use ryzerbe\buildffa\BuildFFA;
use ryzerbe\buildffa\game\kit\KitManager;

class VoteKitForm {
    public static function open(Player $player): void {
        $form = new SimpleForm(function(Player $player, mixed $data): void {
            if($data === null) return;
            $kit = KitManager::getKit($data);
            if($kit === null) {
                $player->sendMessage(BuildFFA::PREFIX."§cSomething went wrong...");
                return;
            }
            if($kit->hasVoted($player)) {
                $player->playSound("note.bass", 5.0, 1.0, [$player]);
                return;
            }
            $kit->addVote($player);
            $player->playSound("random.levelup", 5.0, 1.0, [$player]);
        });
        foreach(KitManager::getKits() as $kit) {
            $form->addButton(($kit->hasVoted($player) ? TextFormat::GREEN : TextFormat::DARK_GRAY)."⇨ ".TextFormat::GREEN.$kit->getName()."\n§r§7§o".$kit->getVotes()." Votes", $kit->imageType, $kit->image, $kit->getName());
        }
        $form->sendToPlayer($player);
    }
}