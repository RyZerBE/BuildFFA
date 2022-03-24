<?php
declare(strict_types=1);
namespace ryzerbe\buildffa\game\perks\item;
use pocketmine\item\Item;
use pocketmine\item\ItemIds;
use ryzerbe\core\player\PMMPPlayer;
use ryzerbe\core\util\customitem\CustomItem;


/**
 * Class BoosterItem
 * @package ryzerbe\buildffa\game\perks\type
 * @author Jan Sohn / xxAROX
 * @date 24. März, 2022 - 19:32
 * @ide PhpStorm
 * @project BuilderFFA
 */
class BoosterItem extends CustomItem {

	public function __construct(){
		parent::__construct(Item::get(ItemIds::GHAST_TEAR));
	}

	public function onInteract(PMMPPlayer $player, Item $item): void{
		$player->jump();
		$motion = $player->getDirectionVector();
		$motion->x *= 1.7;
		$motion->z *= 1.7;
		$motion->y += 0.8;
		$player->setMotion($motion);
		$player->getInventory()->setItemInHand($item->pop());
	}
}
