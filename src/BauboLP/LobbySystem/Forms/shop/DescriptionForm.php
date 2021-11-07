<?php


namespace BauboLP\LobbySystem\Forms\shop;


use BauboLP\LobbySystem\LobbySystem;
use pocketmine\form\MenuForm;
use pocketmine\form\MenuOption;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class DescriptionForm extends MenuForm
{

    public function __construct(string $rank, array $description)
    {
        $options = [];
        $options[] = new MenuOption(TextFormat::GREEN."Continue");
        $options[] = new MenuOption(TextFormat::RED."Close");
        $lines = "";
        foreach ($description as $line) {
            $lines .= TextFormat::WHITE."• ".TextFormat::RESET.TextFormat::AQUA.$line."\n";
        }
        parent::__construct(LobbySystem::Prefix.TextFormat::AQUA."Description", $lines, $options, function (Player $player, int $selectedOption) use ($rank): void{
            switch ($selectedOption) {
                case 0:
                    $player->sendForm(new DiscountCodeForm($rank, LobbySystem::$ranks[$rank], $player->getName()));
                    break;
            }
        });
    }
}