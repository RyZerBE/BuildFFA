<?php
declare(strict_types=1);
namespace ryzerbe\buildffa\game\perks;


use ryzerbe\core\util\customitem\CustomItem;
use ryzerbe\core\util\customitem\CustomItemManager;


abstract class Perk {

	public function __construct(protected string $name, protected CustomItem $item){
		CustomItemManager::getInstance()->registerCustomItem($item);
	}

	public abstract function getCost(): int;
	public abstract function onUpdate(int $currentTick): void;

	public function getImagePath(): string {
		return "";
	}

	public function getImageType(): int {
		return -1;
	}

	public function getItem(): CustomItem{
		return $this->item;
	}

	/**
	 * Function getName
	 * @return string
	 */
	public function getName(): string{
		return $this->name;
	}
}
