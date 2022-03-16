<?php

declare(strict_types=1);

namespace ryzerbe\buildffa\game\setup\item;

use jojoe77777\FormAPI\CustomForm;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\level\particle\RedstoneParticle;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\Config;
use ryzerbe\buildffa\game\map\Map;
use ryzerbe\core\player\PMMPPlayer;
use ryzerbe\core\util\customitem\CustomItem;
use ryzerbe\core\util\Vector3Utils;
use function cos;
use function floatval;
use function sin;
use function strval;

class SetupMapSettingsItem extends CustomItem {
    public function __construct(){
        parent::__construct(ItemFactory::get(ItemIds::BLAZE_ROD)->setCustomName("§r§aSettings"));
    }

    public function onInteract(PMMPPlayer $player, Item $item): void{
        if($player->hasItemCooldown($item)) return;
        $player->resetItemCooldown($item, 10);
        $settings = new Config(Server::getInstance()->getDataPath()."/worlds/".$player->getLevel()->getFolderName()."/settings.json");

        $form = new CustomForm(function(Player $player, mixed $data) use ($settings): void {
            if($data === null) return;
            $radius = floatval($data[Map::KEY_PROTECTION_RADIUS]);

            $settings->set(Map::KEY_NAME, $data[Map::KEY_NAME]);
            $settings->set(Map::KEY_PROTECTION_RADIUS, $radius);
            $settings->save();

            $center = Vector3Utils::fromString($settings->get(Map::KEY_SPAWN, "0:100:0"));
            $level = $player->getLevel();
            for($i = 0; $i <= 360; $i++) {
                $cosOffset = $radius * cos($i);
                $sinOffset = $radius * sin($i);
                $level->addParticle(new RedstoneParticle($center->add($cosOffset, 0, $sinOffset), 10));
                $level->addParticle(new RedstoneParticle($center->add($cosOffset, $sinOffset), 10));
                $level->addParticle(new RedstoneParticle($center->add(0, $sinOffset, $cosOffset), 10));
            }
            $player->playSound("random.levelup");
        });
        $form->addInput("Name", "", $settings->get(Map::KEY_NAME, ""), Map::KEY_NAME);
        $form->addInput("Protection Radius", "", strval($settings->get(Map::KEY_PROTECTION_RADIUS, 10.0)), Map::KEY_PROTECTION_RADIUS);
        $form->sendToPlayer($player);
    }
}