<?php


namespace BauboLP\LobbySystem\Forms\shop;


use BauboLP\LobbySystem\LobbySystem;
use pocketmine\form\MenuForm;
use pocketmine\form\MenuOption;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class RankSelectForm extends MenuForm
{

    public function __construct()
    {
        $options = [];
        foreach (array_keys(LobbySystem::$ranks) as $key) {
            $options[] = new MenuOption(LobbySystem::$ranks[$key]['name']."\n".TextFormat::YELLOW.LobbySystem::$ranks[$key]['cost']." Coins");
        }
        parent::__construct(LobbySystem::Prefix.TextFormat::AQUA."Ranks", "", $options, function (Player $player, int $selectedOption): void {
            $rank = array_keys(LobbySystem::$ranks)[$selectedOption];
                $rankData = LobbySystem::$ranks[$rank];
                $player->sendForm(new DescriptionForm($rank, $rankData['description']));
        });
    }

}