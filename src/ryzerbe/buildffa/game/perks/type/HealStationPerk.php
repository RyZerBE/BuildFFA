<?php
declare(strict_types=1);
namespace ryzerbe\buildffa\game\perks\type;


use pocketmine\block\BlockIds;
use pocketmine\level\particle\HeartParticle;
use ryzerbe\buildffa\game\perks\Perk;
use ryzerbe\buildffa\player\BuildFFAPlayerManager;
use ryzerbe\core\player\PMMPPlayer;
use ryzerbe\core\util\Vector3Utils;


class HealStationPerk extends Perk {

	public static array $placedStations = [];

	public function getCost(): int{
		return 300;
	}

	public function onUpdate(int $currentTick): void{
		if($currentTick % 20 !== 0) return;
		foreach (BuildFFAPlayerManager::getPlayers() as $bffaPlayer) {
			/** @var PMMPPlayer $player */
			$player = $bffaPlayer->getPlayer();

			$blockUnderPlayer = $player->getBlockUnderPlayer();
			if($player->getBlockUnderPlayer()->getId() === BlockIds::SEA_LANTERN) {
				$owner = HealStationPerk::$placedStations[Vector3Utils::toString($blockUnderPlayer)] ?? null;
				if($owner === null) continue;
				if($owner != $blockUnderPlayer->getName()) continue;

				if($player->getHealth() >= $player->getMaxHealth()) continue;
				$player->setHealth($player->getHealth() + 1.5);
				$player->getLevel()->addParticle(new HeartParticle($player->getEyePos(), 2));
			}
		}
	}
}
