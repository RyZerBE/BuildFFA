<?php
declare(strict_types=1);
namespace ryzerbe\buildffa\game\perks\type;


use ryzerbe\buildffa\game\perks\Perk;


class GapplePerk extends Perk{
	public function getCost(): int{
		return 250;
	}

	public function onUpdate(int $currentTick): void{}
}
