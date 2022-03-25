<?php
declare(strict_types=1);
namespace ryzerbe\buildffa\game\safezone\form;


use jojoe77777\FormAPI\SimpleForm;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use ryzerbe\buildffa\game\perks\PerkManager;
use ryzerbe\buildffa\player\BuildFFAPlayerManager;


class PerkForm{

	public static function open(Player $player): void {
		$bFFAPlayer = BuildFFAPlayerManager::get($player);
		if($bFFAPlayer === null) {
			return;
		}

		$form = new SimpleForm(function(Player $player, mixed $data) use ($bFFAPlayer): void {
			if($data === null) return;

			$perk = PerkManager::getInstance()->getPerk($data);
			if($perk === null) return;
			$rbePlayer = $bFFAPlayer->getRyZerPlayer();
			if($rbePlayer->getCoins() < $perk->getCost()) {
				$rbePlayer->sendTranslate("not-enough-coins");
				$rbePlayer->getPlayer()->playSound("note.bass", 1.0, 1.0, [$player]);
				return;
			}

			$rbePlayer->removeCoins($perk->getCost(), true);
			$rbePlayer->getPlayer()->playSound("random.levelup", 1.0, 1.0, [$player]);
			$bFFAPlayer->givePerk($perk);
		});

		foreach(PerkManager::getInstance()->getPerks() as $perk) {
			$form->addButton($perk->getName()."\n".TextFormat::GRAY."⇨ ".TextFormat::WHITE.$perk->getCost().TextFormat::GOLD." Coins", $perk->getImageType(), $perk->getImagePath(), $perk->getName());
		}
		$form->setTitle(TextFormat::DARK_PURPLE.TextFormat::BOLD."Perks");
		$form->sendToPlayer($player);
	}
}
