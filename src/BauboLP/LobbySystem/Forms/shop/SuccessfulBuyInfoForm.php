<?php


namespace BauboLP\LobbySystem\Forms\shop;


use BauboLP\Core\Provider\LanguageProvider;
use BauboLP\LobbySystem\LobbySystem;
use pocketmine\form\MenuForm;
use pocketmine\form\MenuOption;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class SuccessfulBuyInfoForm extends MenuForm
{

    public function __construct(string $playerName)
    {
        $options = [];
        $options[] = new MenuOption(TextFormat::GREEN."Okay ;)");
        parent::__construct(LobbySystem::Prefix.TextFormat::AQUA."Successful", LanguageProvider::getMessageContainer('lobby-successful-buy-info-text', $playerName), $options, function (Player $player, int $selectedOption): void{});
    }
}