<?php
declare(strict_types=1);
namespace ryzerbe\buildffa\game\perks\item;


use pocketmine\block\BlockIds;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\item\Item;
use pocketmine\level\Level;
use ryzerbe\buildffa\game\perks\type\HealStationPerk;
use ryzerbe\core\util\customitem\CustomItem;
use ryzerbe\core\util\customitem\CustomItemManager;


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
		$customItem = CustomItemManager::getInstance()->getCustomItemByItem($event->getItem());
		if($customItem === null) return;

		$block = $event->getBlock();
		$player = $event->getPlayer();
		HealStationPerk::$placedStations[Level::blockHash($block->getFloorX(), $block->getFloorY(), $block->getFloorZ())] = $player->getName();
		var_dump(HealStationPerk::$placedStations[Level::blockHash($block->getFloorX(), $block->getFloorY(), $block->getFloorZ())] ?? null);
	}

	public function cancelInteract(): bool{
		return false;
	}
}
