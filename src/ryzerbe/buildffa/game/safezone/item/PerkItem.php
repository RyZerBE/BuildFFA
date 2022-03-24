<?php
declare(strict_types=1);

namespace ryzerbe\buildffa\game\safezone\item;

use pocketmine\block\BlockIds;
use pocketmine\item\Item;
use pocketmine\item\ItemIds;
use pocketmine\utils\TextFormat;
use ryzerbe\buildffa\game\safezone\form\PerkForm;
use ryzerbe\core\player\PMMPPlayer;
use ryzerbe\core\util\customitem\CustomItem;


class PerkItem extends CustomItem{

	public function __construct(){
		parent::__construct(Item::get(ItemIds::ENDER_EYE)->setCustomName(TextFormat::GOLD."Buy Perks"), 6);
	}

	public function onInteract(PMMPPlayer $player, Item $item): void{
		if($player->hasItemCooldown($item)) return;
		$player->resetItemCooldown($item, 10);
		PerkForm::open($player);
	}
}
