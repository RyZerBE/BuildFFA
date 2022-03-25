<?php
declare(strict_types=1);
namespace ryzerbe\buildffa\game\perks;


use pocketmine\item\Item;
use pocketmine\item\ItemIds;
use pocketmine\utils\SingletonTrait;
use pocketmine\utils\TextFormat;
use ryzerbe\buildffa\game\perks\item\BoosterItem;
use ryzerbe\buildffa\game\perks\item\HealStationItem;
use ryzerbe\buildffa\game\perks\item\SwitcherItem;
use ryzerbe\buildffa\game\perks\type\BoosterPerk;
use ryzerbe\buildffa\game\perks\type\GapplePerk;
use ryzerbe\buildffa\game\perks\type\HealStationPerk;
use ryzerbe\buildffa\game\perks\type\SwitcherPerk;


class PerkManager {
	use SingletonTrait;

	/** @var Perk[]  */
	public array $perks = [];

	/**
	 * Function getPerks
	 * @return array
	 */
	public function getPerks(): array{
		return $this->perks;
	}

	public function __construct(){
		$perks = [
			new HealStationPerk(TextFormat::RED."2x Healstation", new HealStationItem()),
			new SwitcherPerk(TextFormat::GOLD."1x Switcher", new SwitcherItem()),
			new BoosterPerk(TextFormat::GREEN."1x Booster", new BoosterItem()),
			new GapplePerk(TextFormat::GOLD."1x Golden Apple", Item::get(ItemIds::GOLDEN_APPLE))
		];

		foreach ($perks as $perk) {
			$this->registerPerk($perk);
		}
	}

	public function getPerk(string $perkName){
		return $this->perks[$perkName] ?? null;
	}

	public function registerPerks(Perk ...$perks){
		foreach ($perks as $perk) {
			$this->registerPerk($perk);
		}
	}

	/**
	 * Function registerPerk
	 * @param Perk $perk
	 * @return void
	 */
	public function registerPerk(Perk $perk){
		if(isset($this->perks[$perk->getName()])) return;

		$this->perks[$perk->getName()] = $perk;
	}

	/**
	 * Function unregisterPerk
	 * @param Perk|string $perk
	 * @return void
	 */
	public function unregisterPerk(Perk|string $perk){
		if($perk instanceof Perk) $perk = $perk->getName();

		unset($this->perks[$perk]);
	}
}
