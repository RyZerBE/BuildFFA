<?php


namespace BauboLP\LobbySystem\Forms;


use BauboLP\LobbySystem\LobbySystem;
use pocketmine\form\MenuForm;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class ProfileForm extends MenuForm
{

    public function __construct()
    {
        $options = [];
        $buttons = ['Languages', 'Clans'];
        parent::__construct(LobbySystem::Prefix.TextFormat::AQUA."Profile", "", $options, function (Player $player, int $selectedOption): void{

        });
    }
}