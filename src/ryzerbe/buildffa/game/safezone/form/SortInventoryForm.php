<?php

declare(strict_types=1);

namespace ryzerbe\buildffa\game\safezone\form;

use jojoe77777\FormAPI\SimpleForm;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use ryzerbe\buildffa\BuildFFA;
use ryzerbe\buildffa\game\kit\KitManager;
use ryzerbe\buildffa\player\BuildFFAPlayerManager;

class SortInventoryForm {
    public static function open(Player $player): void {
        $bFFAPlayer = BuildFFAPlayerManager::get($player);
        if($bFFAPlayer === null) {
            return;
        }
        $form = new SimpleForm(function(Player $player, mixed $data) use ($bFFAPlayer): void {
            if($data === null) return;
            $kit = KitManager::getKit($data);
            if($kit === null) {
                $player->sendMessage(BuildFFA::PREFIX."§cSomething went wrong...");
                return;
            }
            $bFFAPlayer->sortInventory($kit);
        });
        foreach(KitManager::getKits() as $kit) {
            $form->addButton(TextFormat::WHITE."⇨".TextFormat::DARK_GREEN." ".$kit->getName(), $kit->imageType, $kit->image, $kit->getName());
        }
        $form->setTitle(TextFormat::GOLD.TextFormat::BOLD."Sort inventory");
        $form->sendToPlayer($player);
    }
}