<?php
declare(strict_types=1);
namespace ryzerbe\buildffa\game\perks\item;


use pocketmine\block\BlockIds;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\item\Item;
use ryzerbe\buildffa\game\perks\type\HealStationPerk;
use ryzerbe\core\util\customitem\CustomItem;
use ryzerbe\core\util\Vector3Utils;


class HealStationItem extends CustomItem {

	public function __construct(){
		parent::__construct(Item::get(BlockIds::SEA_LANTERN, 0, 2));
	}

	/**
	 * Function onPlace
	 * @param BlockPlaceEvent $event
	 * @return void
	 * @priority HIGHEST
	 */
	public function onPlace(BlockPlaceEvent $event){
		if($event->isCancelled()) return;

		$block = $event->getBlock();
		$player = $event->getPlayer();
		HealStationPerk::$placedStations[Vector3Utils::toString($block->asVector3())] = $player->getName();
	}
}
