<?php


namespace BauboLP\LobbySystem\Forms\addons;


use BauboLP\LobbySystem\LobbySystem;
use BauboLP\LobbySystem\Utils\LobbyPlayer;
use pocketmine\form\MenuForm;
use pocketmine\form\MenuOption;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class AddonMainMenu extends MenuForm
{

    public function __construct(LobbyPlayer $player)
    {
        $options = [];
        $cParticle = count($player->getParticles()) == 0 ? 0 : count($player->getParticles()) - 1;
        $cFallItems = count($player->getFallItems()) == 0 ? 0 : count($player->getFallItems()) - 1;
        $cWings = count($player->getWings()) == 0 ? 0 : count($player->getWings()) - 1;
        $cSpecial = count($player->getSpecials()) == 0 ? 0 : count($player->getSpecials()) - 1;
        $cWalkingBlocks = count($player->getWalkingBlocks()) == 0 ? 0 : count($player->getWalkingBlocks()) - 1;
        $options[] = new MenuOption(TextFormat::GOLD.TextFormat::BOLD."CosmeticsBE"."\n".TextFormat::GRAY."• ".TextFormat::GREEN."FREE TO USE".TextFormat::GRAY." •");
        $options[] = new MenuOption(TextFormat::DARK_PURPLE."Particles"."\n".TextFormat::GRAY."• ".TextFormat::YELLOW.$cParticle."/9".TextFormat::GRAY." •");
     //   $options[] = new MenuOption(TextFormat::GREEN."Pets"."\n".TextFormat::YELLOW."0/12");
        $options[] = new MenuOption(TextFormat::LIGHT_PURPLE."Fall Items"."\n".TextFormat::GRAY."• ".TextFormat::YELLOW.$cFallItems."/11".TextFormat::GRAY." •");
        $options[] = new MenuOption(TextFormat::GOLD."Wings"."\n".TextFormat::GRAY."• ".TextFormat::YELLOW.$cWings."/3".TextFormat::GRAY." •");
        $options[] = new MenuOption(TextFormat::GREEN."Walking Blocks"."\n".TextFormat::GRAY."• ".TextFormat::YELLOW.$cWalkingBlocks."/6".TextFormat::GRAY." •");
        $options[] = new MenuOption(TextFormat::DARK_RED."Spe".TextFormat::WHITE."cial"."\n".TextFormat::GRAY."• ".TextFormat::YELLOW.$cSpecial."/3".TextFormat::GRAY." •");
        parent::__construct(LobbySystem::Prefix.TextFormat::AQUA."Addons", "", $options, function (Player $player, int $selectedOption): void{
            $p = LobbySystem::getPlayerCache($player->getName());
            switch ($selectedOption) {
                case 0:
                    $player->getServer()->dispatchCommand($player, "cbe");
                    break;
                case 1:
                    $player->sendForm(new ParticlesForm($p));
                    break;
                case 2:
                    $player->sendForm(new FallItemForm($p));
                    break;
                case 3:
                    $player->sendForm(new WingsForm($p));
                    break;
                case 4:
                    $player->sendForm(new WalkingBlocksForm($p));
                    break;
                case 5:
                    $player->sendForm(new SpecialForm($p));
                    break;
            }
        });
    }
}