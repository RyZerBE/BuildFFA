<?php
declare(strict_types=1);
namespace ryzerbe\buildffa\game\perks\type;


use ryzerbe\buildffa\game\perks\Perk;


class SwitcherPerk extends Perk {

	public function getCost(): int{
		return 600;
	}

	public function onUpdate(int $currentTick): void{}
}
