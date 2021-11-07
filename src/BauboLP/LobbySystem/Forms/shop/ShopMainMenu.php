<?php


namespace BauboLP\LobbySystem\Forms\shop;


use BauboLP\LobbySystem\LobbySystem;
use pocketmine\form\MenuForm;
use pocketmine\form\MenuOption;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class ShopMainMenu extends MenuForm
{

    public function __construct()
    {
        $options = [];
        $options[] = new MenuOption(TextFormat::GOLD."Ranks");
        parent::__construct(LobbySystem::Prefix.TextFormat::AQUA."Shop", TextFormat::GOLD."shop.ryzer.be", $options, function (Player $player, int $selectedOption): void{
            switch ($selectedOption) {
                case 0:
                    $player->sendForm(new RankSelectForm());
                    break;
            }
        });
    }
}