<?php


namespace BauboLP\LobbySystem\Forms\addons;


use BauboLP\Core\Provider\LanguageProvider;
use BauboLP\LobbySystem\LobbySystem;
use BauboLP\LobbySystem\Utils\LobbyPlayer;
use pocketmine\form\MenuForm;
use pocketmine\form\MenuOption;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class FallItemForm extends MenuForm
{

    public function __construct(LobbyPlayer $player)
    {
        $options = [];
        if($player->haveBoughtFallItem("Beacons")) {
            $options[] = new MenuOption(TextFormat::AQUA."Beacons\n".TextFormat::GREEN."Click to use");
        }else {
            $options[] = new MenuOption(TextFormat::AQUA."Beacons\n".TextFormat::YELLOW."7000 Coins");
        }
        if($player->haveBoughtFallItem("Emeralds")) {
            $options[] = new MenuOption(TextFormat::DARK_GREEN."Emeralds\n".TextFormat::GREEN."Click to use");
        }else {
            $options[] = new MenuOption(TextFormat::DARK_GREEN."Emeralds\n".TextFormat::YELLOW."7000 Coins");
        }
        if($player->haveBoughtFallItem("Redstone")) {
            $options[] = new MenuOption(TextFormat::RED."Redstone\n".TextFormat::GREEN."Click to use");
        }else {
            $options[] = new MenuOption(TextFormat::RED."Redstone\n".TextFormat::YELLOW."7000 Coins");
        }
        if($player->haveBoughtFallItem("Diamonds")) {
            $options[] = new MenuOption(TextFormat::AQUA."Diamonds\n".TextFormat::GREEN."Click to use");
        }else {
            $options[] = new MenuOption(TextFormat::AQUA."Diamonds\n".TextFormat::YELLOW."7000 Coins");
        }
        if($player->haveBoughtFallItem("Netherstars")) {
            $options[] = new MenuOption(TextFormat::GOLD."Netherstars\n".TextFormat::GREEN."Click to use");
        }else {
            $options[] = new MenuOption(TextFormat::GOLD."Netherstars\n".TextFormat::YELLOW."7000 Coins");
        }
        if($player->haveBoughtFallItem("Endportals")) {
            $options[] = new MenuOption(TextFormat::LIGHT_PURPLE."Endportals\n".TextFormat::GREEN."Click to use");
        }else {
            $options[] = new MenuOption(TextFormat::LIGHT_PURPLE."Endportals\n".TextFormat::YELLOW."7000 Coins");
        }
        if($player->haveBoughtFallItem("Enderpearls")) {
            $options[] = new MenuOption(TextFormat::DARK_PURPLE."Enderpearls\n".TextFormat::GREEN."Click to use");
        }else {
            $options[] = new MenuOption(TextFormat::DARK_PURPLE."Enderpearls\n".TextFormat::YELLOW."7000 Coins");
        }
        if($player->haveBoughtFallItem("Sugar")) {
            $options[] = new MenuOption(TextFormat::WHITE."Sugar\n".TextFormat::GREEN."Click to use");
        }else {
            $options[] = new MenuOption(TextFormat::WHITE."Sugar\n".TextFormat::YELLOW."7000 Coins");
        }
        if($player->haveBoughtFallItem("Cookies")) {
            $options[] = new MenuOption(TextFormat::GOLD."Cookies\n".TextFormat::GREEN."Click to use");
        }else {
            $options[] = new MenuOption(TextFormat::GOLD."Cookies\n".TextFormat::YELLOW."10000 Coins");
        }
        if($player->haveBoughtFallItem("Beds")) {
            $options[] = new MenuOption(TextFormat::WHITE."Be".TextFormat::RED."ds\n".TextFormat::GREEN."Click to use");
        }else {
            $options[] = new MenuOption(TextFormat::WHITE."Be".TextFormat::RED."ds\n".TextFormat::YELLOW."10000 Coins");
        }
        if($player->haveBoughtFallItem("Enchantment Tables")) {
            $options[] = new MenuOption(TextFormat::AQUA."Enchantment Tables\n".TextFormat::GREEN."Click to use");
        }else {
            $options[] = new MenuOption(TextFormat::AQUA."Enchantment Tables\n".TextFormat::YELLOW."7000 Coins");
        }

        $options[] = new MenuOption(TextFormat::DARK_RED."Remove");
        parent::__construct(LobbySystem::Prefix.TextFormat::AQUA."Addons", "", $options, function (Player $player, int $selectedOption): void{
            switch ($selectedOption) {
                case 0:
                    if (($obj = LobbySystem::getPlayerCache($player->getName())) != null) {
                        if ($obj->haveBoughtFallItem("Beacons")) {
                            $obj->setFallItem("Beacons");
                            $obj->getPlayer()->sendMessage(LobbySystem::Prefix . LanguageProvider::getMessageContainer('lobby-selected-fallitem', $player->getName(), ['#fallitem' => TextFormat::AQUA . "Beacons"]));
                        } else {
                            $player->sendForm(new ReallyBuyAddonForm($obj->getPlayer()->getName(), "Beacons", 7000, ReallyBuyAddonForm::FALL_ITEM));
                        }
                    }
                    break;
                case 1:
                    if (($obj = LobbySystem::getPlayerCache($player->getName())) != null) {
                        if ($obj->haveBoughtFallItem("Emeralds")) {
                            $obj->setFallItem("Emeralds");
                            $obj->getPlayer()->sendMessage(LobbySystem::Prefix . LanguageProvider::getMessageContainer('lobby-selected-fallitem', $player->getName(), ['#fallitem' => TextFormat::DARK_GREEN . "Emeralds"]));
                        } else {
                            $player->sendForm(new ReallyBuyAddonForm($obj->getPlayer()->getName(), "Emeralds", 7000, ReallyBuyAddonForm::FALL_ITEM));
                        }
                    }
                    break;
                case 2:
                    if (($obj = LobbySystem::getPlayerCache($player->getName())) != null) {
                        if ($obj->haveBoughtFallItem("Redstone")) {
                            $obj->setFallItem("Redstone");
                            $obj->getPlayer()->sendMessage(LobbySystem::Prefix . LanguageProvider::getMessageContainer('lobby-selected-fallitem', $player->getName(), ['#fallitem' => TextFormat::RED . "Redstone"]));
                        } else {
                            $player->sendForm(new ReallyBuyAddonForm($obj->getPlayer()->getName(), "Redstone", 7000, ReallyBuyAddonForm::FALL_ITEM));
                        }
                    }
                    break;
                case 3:
                    if (($obj = LobbySystem::getPlayerCache($player->getName())) != null) {
                        if ($obj->haveBoughtFallItem("Diamonds")) {
                            $obj->setFallItem("Diamonds");
                            $obj->getPlayer()->sendMessage(LobbySystem::Prefix . LanguageProvider::getMessageContainer('lobby-selected-fallitem', $player->getName(), ['#fallitem' => TextFormat::AQUA . "Diamonds"]));
                        } else {
                            $player->sendForm(new ReallyBuyAddonForm($obj->getPlayer()->getName(), "Diamonds", 7000, ReallyBuyAddonForm::FALL_ITEM));
                        }
                    }
                    break;
                case 4:
                    if (($obj = LobbySystem::getPlayerCache($player->getName())) != null) {
                        if ($obj->haveBoughtFallItem("Netherstars")) {
                            $obj->setFallItem("Netherstars");
                            $obj->getPlayer()->sendMessage(LobbySystem::Prefix . LanguageProvider::getMessageContainer('lobby-selected-fallitem', $player->getName(), ['#fallitem' => TextFormat::GOLD . "Netherstars"]));
                        } else {
                            $player->sendForm(new ReallyBuyAddonForm($obj->getPlayer()->getName(), "Netherstars", 7000, ReallyBuyAddonForm::FALL_ITEM));
                        }
                    }
                    break;
                case 5:
                    if (($obj = LobbySystem::getPlayerCache($player->getName())) != null) {
                        if ($obj->haveBoughtFallItem("Endportals")) {
                            $obj->setFallItem("Endportals");
                            $obj->getPlayer()->sendMessage(LobbySystem::Prefix . LanguageProvider::getMessageContainer('lobby-selected-fallitem', $player->getName(), ['#fallitem' => TextFormat::LIGHT_PURPLE . "Endportals"]));
                        } else {
                            $player->sendForm(new ReallyBuyAddonForm($obj->getPlayer()->getName(), "Endportals", 7000, ReallyBuyAddonForm::FALL_ITEM));
                        }
                    }
                    break;
                case 6:
                    if (($obj = LobbySystem::getPlayerCache($player->getName())) != null) {
                        if ($obj->haveBoughtFallItem("Enderpearls")) {
                            $obj->setFallItem("Enderpearls");
                            $obj->getPlayer()->sendMessage(LobbySystem::Prefix . LanguageProvider::getMessageContainer('lobby-selected-fallitem', $player->getName(), ['#fallitem' => TextFormat::DARK_PURPLE . "Enderpearls"]));
                        } else {
                            $player->sendForm(new ReallyBuyAddonForm($obj->getPlayer()->getName(), "Enderpearls", 7000, ReallyBuyAddonForm::FALL_ITEM));
                        }
                    }
                    break;
                case 7:
                    if (($obj = LobbySystem::getPlayerCache($player->getName())) != null) {
                        if ($obj->haveBoughtFallItem("Sugar")) {
                            $obj->setFallItem("Sugar");
                            $obj->getPlayer()->sendMessage(LobbySystem::Prefix . LanguageProvider::getMessageContainer('lobby-selected-fallitem', $player->getName(), ['#fallitem' => TextFormat::WHITE . "Sugar"]));
                        } else {
                            $player->sendForm(new ReallyBuyAddonForm($obj->getPlayer()->getName(), "Sugar", 7000, ReallyBuyAddonForm::FALL_ITEM));
                        }
                    }
                    break;
                case 8:
                    if (($obj = LobbySystem::getPlayerCache($player->getName())) != null) {
                        if ($obj->haveBoughtFallItem("Cookies")) {
                            $obj->setFallItem("Cookies");
                            $obj->getPlayer()->sendMessage(LobbySystem::Prefix . LanguageProvider::getMessageContainer('lobby-selected-fallitem', $player->getName(), ['#fallitem' => TextFormat::GOLD . "Cookies"]));
                        } else {
                            $player->sendForm(new ReallyBuyAddonForm($obj->getPlayer()->getName(), "Cookies", 10000, ReallyBuyAddonForm::FALL_ITEM));
                        }
                    }
                    break;
                case 9:
                    if (($obj = LobbySystem::getPlayerCache($player->getName())) != null) {
                        if ($obj->haveBoughtFallItem("Beds")) {
                            $obj->setFallItem("Beds");
                            $obj->getPlayer()->sendMessage(LobbySystem::Prefix . LanguageProvider::getMessageContainer('lobby-selected-fallitem', $player->getName(), ['#fallitem' => TextFormat::RED . "Beds"]));
                        } else {
                            $player->sendForm(new ReallyBuyAddonForm($obj->getPlayer()->getName(), "Beds", 10000, ReallyBuyAddonForm::FALL_ITEM));
                        }
                    }
                    break;
                case 10:
                    if (($obj = LobbySystem::getPlayerCache($player->getName())) != null) {
                        if ($obj->haveBoughtFallItem("Enchantment Tables")) {
                            $obj->setFallItem("Enchantment Tables");
                            $obj->getPlayer()->sendMessage(LobbySystem::Prefix . LanguageProvider::getMessageContainer('lobby-selected-fallitem', $player->getName(), ['#fallitem' => TextFormat::AQUA . "Enchantment Tables"]));
                        } else {
                            $player->sendForm(new ReallyBuyAddonForm($obj->getPlayer()->getName(), "Enchantment Tables", 10000, ReallyBuyAddonForm::FALL_ITEM));
                        }
                    }
                    break;
                case 11:
                    if (($obj = LobbySystem::getPlayerCache($player->getName())) != null) {
                        $obj->setFallItem(null);
                    }
                    break;
            }
        });
    }

}