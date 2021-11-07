<?php


namespace BauboLP\LobbySystem\Forms\addons;


use BauboLP\Core\Provider\LanguageProvider;
use BauboLP\LobbySystem\LobbySystem;
use BauboLP\LobbySystem\Provider\ItemProvider;
use BauboLP\LobbySystem\Utils\LobbyPlayer;
use pocketmine\form\MenuForm;
use pocketmine\form\MenuOption;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class SpecialForm extends MenuForm
{

    public function __construct(LobbyPlayer $player)
    {
        $options = [];
        if($player->haveBoughtSpecial("Bomber")) {
            $options[] = new MenuOption(TextFormat::YELLOW."Bomber\n".TextFormat::GREEN."Click to use");
        }else {
            $options[] = new MenuOption(TextFormat::YELLOW."Bomber\n".TextFormat::YELLOW."25000 Coins");
        }
        if($player->haveBoughtSpecial("Spiderman")) {
            $options[] = new MenuOption(TextFormat::RED."Spiderman\n".TextFormat::GREEN."Click to use");
        }else {
            $options[] = new MenuOption(TextFormat::RED."Spiderman\n".TextFormat::YELLOW."25000 Coins");
        }
        if($player->haveBoughtSpecial("Paintball Gun")) {
            $options[] = new MenuOption(TextFormat::GOLD."Paintball Gun\n".TextFormat::GREEN."Click to use");
        }else {
            $options[] = new MenuOption(TextFormat::GOLD."Paintball Gun\n".TextFormat::YELLOW."25000 Coins");
        }
        $options[] = new MenuOption(TextFormat::DARK_RED."Remove");

        parent::__construct(LobbySystem::Prefix.TextFormat::AQUA."Addons", "", $options, function (Player $player, int $selectedOption): void{
            switch ($selectedOption) {
                case 0:
                    if (($obj = LobbySystem::getPlayerCache($player->getName())) != null) {
                        if ($obj->haveBoughtSpecial("Bomber")) {
                            $obj->setSpecial("Bomber");
                            ItemProvider::clearAllInvs($player);
                            ItemProvider::giveLobbyItems($player);
                            $obj->getPlayer()->sendMessage(LobbySystem::Prefix . LanguageProvider::getMessageContainer('lobby-selected-special', $player->getName(), ['#special' => TextFormat::YELLOW . "Bomber"]));
                        } else {
                            $player->sendForm(new ReallyBuyAddonForm($obj->getPlayer()->getName(), "Bomber", 25000, ReallyBuyAddonForm::SPECIAL));
                        }
                    }
                    break;
                case 1:
                    if (($obj = LobbySystem::getPlayerCache($player->getName())) != null) {
                        if ($obj->haveBoughtSpecial("Spiderman")) {
                            $obj->setSpecial("Spiderman");
                            ItemProvider::clearAllInvs($player);
                            ItemProvider::giveLobbyItems($player);
                            $obj->getPlayer()->sendMessage(LobbySystem::Prefix . LanguageProvider::getMessageContainer('lobby-selected-special', $player->getName(), ['#special' => TextFormat::RED . "Spiderman"]));
                        } else {
                            $player->sendForm(new ReallyBuyAddonForm($obj->getPlayer()->getName(), "Spiderman", 25000, ReallyBuyAddonForm::SPECIAL));
                        }
                    }
                    break;
                case 2:
                    if (($obj = LobbySystem::getPlayerCache($player->getName())) != null) {
                        if ($obj->haveBoughtSpecial("Paintball Gun")) {
                            $obj->setSpecial("Paintball Gun");
                            ItemProvider::clearAllInvs($player);
                            ItemProvider::giveLobbyItems($player);
                            $obj->getPlayer()->sendMessage(LobbySystem::Prefix . LanguageProvider::getMessageContainer('lobby-selected-special', $player->getName(), ['#special' => TextFormat::GOLD . "Paintball Gun"]));
                        } else {
                            $player->sendForm(new ReallyBuyAddonForm($obj->getPlayer()->getName(), "Paintball Gun", 25000, ReallyBuyAddonForm::SPECIAL));
                        }
                    }
                    break;
                case 3:
                    if (($obj = LobbySystem::getPlayerCache($player->getName())) != null) {
                        $obj->setSpecial(null);
                        ItemProvider::clearAllInvs($player);
                        ItemProvider::giveLobbyItems($player);
                    }
                    break;
            }
        });
    }
}