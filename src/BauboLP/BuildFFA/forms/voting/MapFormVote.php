<?php


namespace BauboLP\BuildFFA\forms\voting;


use BauboLP\BuildFFA\BuildFFA;
use BauboLP\BuildFFA\provider\GameProvider;
use ryzerbe\core\language\LanguageProvider;
use pocketmine\form\MenuForm;
use pocketmine\form\MenuOption;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class MapFormVote extends MenuForm
{

    public function __construct()
    {
        $options = [];

        $maps = array_keys(GameProvider::getMaps());
        foreach ($maps as $map) {
            $options[] = new MenuOption(TextFormat::GOLD.$map);
        }
        parent::__construct(BuildFFA::Prefix.TextFormat::YELLOW."Map", "", $options, function (Player $player, int $selectedOption) use ($maps): void{
             if(($obj = GameProvider::getBuildFFAPlayer($player->getName())) != null) {
                 $map = $maps[$selectedOption];
                 if($obj->getVoteMap() != null) {
                     $player->sendMessage(BuildFFA::Prefix.LanguageProvider::getMessageContainer('already-voted', $player->getName()));
                    return;
                 }
                 $obj->setVoteMap($map);
             }
        });
    }
}