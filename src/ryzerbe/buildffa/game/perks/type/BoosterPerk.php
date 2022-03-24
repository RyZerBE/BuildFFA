<?php
declare(strict_types=1);
namespace ryzerbe\buildffa\game\perks\type;

use ryzerbe\buildffa\game\perks\Perk;


class BoosterPerk extends Perk{

	public function getCost(): int{
		return 300;
	}

	public function onUpdate(int $currentTick): void{}
}
