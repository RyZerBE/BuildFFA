<?php

declare(strict_types=1);

namespace ryzerbe\buildffa\game\setup\item;

use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\level\particle\RedstoneParticle;
use pocketmine\Server;
use pocketmine\utils\Config;
use ryzerbe\buildffa\game\map\Map;
use ryzerbe\core\player\PMMPPlayer;
use ryzerbe\core\util\customitem\CustomItem;
use ryzerbe\core\util\Vector3Utils;

class SetupSpawnItem extends CustomItem {
    public function __construct(){
        parent::__construct(ItemFactory::get(ItemIds::CARROT_ON_A_STICK)->setCustomName("§r§aSetup Spawn"));
    }

    public function onInteract(PMMPPlayer $player, Item $item): void{
        if($player->hasItemCooldown($item)) return;
        $player->resetItemCooldown($item, 10);
        $settings = new Config(Server::getInstance()->getDataPath()."/worlds/".$player->getLevel()->getFolderName()."/settings.json");

        $vector3 = $player->floor()->add(0.5, 0.0, 0.5);

        $settings->set(Map::KEY_SPAWN, Vector3Utils::toString($vector3));
        $settings->save();

        $player->getLevel()->addParticle(new RedstoneParticle($vector3->add(0, 1, 0), 20));
    }
}