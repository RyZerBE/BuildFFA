<?php


namespace BauboLP\BuildFFA\forms\voting;


use BauboLP\BuildFFA\BuildFFA;
use BauboLP\BuildFFA\provider\GameProvider;
use BauboLP\BuildFFA\provider\ItemProvider;
use baubolp\core\provider\LanguageProvider;
use pocketmine\form\MenuForm;
use pocketmine\form\MenuOption;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class KitVoteForm extends MenuForm
{

    public function __construct()
    {
        $options = [];
        $kits = array_keys(GameProvider::getKits());

        foreach ($kits as $kit) {
            $options[] = new MenuOption(TextFormat::GOLD.ItemProvider::convertKitIndexToString($kit));
        }

        parent::__construct(BuildFFA::Prefix.TextFormat::YELLOW."Kits", "", $options, function (Player $player, int $selectedOption) use ($kits): void{
            $kit = $kits[$selectedOption];

            if(($obj = GameProvider::getBuildFFAPlayer($player->getName())) != null) {
                if($obj->getVoteKit() != null) {
                    $player->sendMessage(BuildFFA::Prefix.LanguageProvider::getMessageContainer('already-voted', $player->getName()));
                    return;
                }
                $obj->setVoteKit($kit);
            }
        });
    }
}