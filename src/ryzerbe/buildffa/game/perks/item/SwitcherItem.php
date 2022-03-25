<?php
declare(strict_types=1);
namespace ryzerbe\buildffa\game\perks\item;


use pocketmine\entity\projectile\Egg;
use pocketmine\event\entity\ProjectileHitEntityEvent;
use pocketmine\item\Item;
use pocketmine\item\ItemIds;
use pocketmine\level\sound\EndermanTeleportSound;
use ryzerbe\core\player\PMMPPlayer;
use ryzerbe\core\util\customitem\CustomItem;


class SwitcherItem extends CustomItem {

	public function __construct(){
		parent::__construct(Item::get(ItemIds::EGG));
	}

	public function onInteract(PMMPPlayer $player, Item $item): void{
		if($player->hasItemCooldown($item)) return;

		$player->resetItemCooldown($item, 10);
		$player->getInventory()->removeItem(Item::get(ItemIds::EGG));
	}

	public function onEntityHit(ProjectileHitEntityEvent $event){
		$entity = $event->getEntity();
		$hitEntity = $event->getEntityHit();
		$vector3 = $hitEntity->asVector3();
		$owner = $entity->getOwningEntity();
		if($owner === null) return;

		if($entity instanceof Egg && $hitEntity instanceof PMMPPlayer) {
			$hitEntity->teleport($owner);
			$owner->teleport($vector3);
			$owner->getLevel()->addSound(new EndermanTeleportSound($owner->asVector3()), [$owner]);
			$owner->getLevel()->addSound(new EndermanTeleportSound($hitEntity->asVector3()), [$hitEntity]);
		}
	}

	public function cancelInteract(): bool{
		return false;
	}
}
