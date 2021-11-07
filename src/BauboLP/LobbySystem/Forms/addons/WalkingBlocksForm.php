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

class WalkingBlocksForm extends MenuForm
{

    public function __construct(LobbyPlayer $player)
    {
        $options = [];
        if ($player->haveBoughtWalkingBlock("Glasses")) { #colorize glass
            $options[] = new MenuOption(TextFormat::GREEN . "Glasses\n" . TextFormat::GREEN . "Click to use");
        } else {
            $options[] = new MenuOption(TextFormat::GREEN . "Glasses\n" . TextFormat::YELLOW . "10000 Coins");
        }
        if ($player->haveBoughtWalkingBlock("Rich Rich")) { #gold, diamond, emeralds blocks
            $options[] = new MenuOption(TextFormat::GOLD . "Rich Rich\n" . TextFormat::GREEN . "Click to use");
        } else {
            $options[] = new MenuOption(TextFormat::GOLD . "Rich Rich\n" . TextFormat::YELLOW . "10000 Coins");
        }
        if ($player->haveBoughtWalkingBlock("Ice Cold")) { #Snow Ice Blocks
            $options[] = new MenuOption(TextFormat::AQUA . "Ice " . TextFormat::WHITE . "Cold\n" . TextFormat::GREEN . "Click to use");
        } else {
            $options[] = new MenuOption(TextFormat::AQUA . "Ice " . TextFormat::WHITE . "Cold\n" . TextFormat::YELLOW . "10000 Coins");
        }
        if ($player->haveBoughtWalkingBlock("Farmer")) {#heu Melons
            $options[] = new MenuOption(TextFormat::RED . "Farmer\n" . TextFormat::GREEN . "Click to use");
        } else {
            $options[] = new MenuOption(TextFormat::RED . "Farmer\n" . TextFormat::YELLOW . "10000 Coins");
        }

        if ($player->haveBoughtWalkingBlock("Concrete")) {#colorize concrete
            $options[] = new MenuOption(TextFormat::LIGHT_PURPLE . "Concrete\n" . TextFormat::GREEN . "Click to use");
        } else {
            $options[] = new MenuOption(TextFormat::LIGHT_PURPLE . "Concrete\n" . TextFormat::YELLOW . "10000 Coins");
        }
        if ($player->haveBoughtWalkingBlock("BedWars")) {#colorize concrete
            $options[] = new MenuOption(TextFormat::RED . "BedWars\n" . TextFormat::GREEN . "Click to use");
        } else {
            $options[] = new MenuOption(TextFormat::RED . "BedWars\n" . TextFormat::YELLOW . "10000 Coins");
        }

        $options[] = new MenuOption(TextFormat::DARK_RED . "Remove");

        parent::__construct(LobbySystem::Prefix . TextFormat::AQUA . "Addons", "", $options, function (Player $player, int $selectedOption): void {
            switch ($selectedOption) {
                case 0:
                    if (($obj = LobbySystem::getPlayerCache($player->getName())) != null) {
                        if ($obj->haveBoughtWalkingBlock("Glasses")) {
                            $obj->setWalkingBlock("Glasses");
                            $obj->getPlayer()->sendMessage(LobbySystem::Prefix . LanguageProvider::getMessageContainer('lobby-selected-walkingblock', $player->getName(), ['#walkingblock' => TextFormat::GREEN . "Glasses"]));
                        } else {
                            $player->sendForm(new ReallyBuyAddonForm($obj->getPlayer()->getName(), "Glasses", 10000, ReallyBuyAddonForm::WALKING_BLOCKS));
                        }
                    }
                    break;
                case 1:
                    if (($obj = LobbySystem::getPlayerCache($player->getName())) != null) {
                        if ($obj->haveBoughtWalkingBlock("Rich Rich")) {
                            $obj->setWalkingBlock("Rich Rich");
                            $obj->getPlayer()->sendMessage(LobbySystem::Prefix . LanguageProvider::getMessageContainer('lobby-selected-walkingblock', $player->getName(), ['#walkingblock' => TextFormat::GOLD . "Rich Rich"]));
                        } else {
                            $player->sendForm(new ReallyBuyAddonForm($obj->getPlayer()->getName(), "Rich Rich", 10000, ReallyBuyAddonForm::WALKING_BLOCKS));
                        }
                    }
                    break;
                case 2:
                    if (($obj = LobbySystem::getPlayerCache($player->getName())) != null) {
                        if ($obj->haveBoughtWalkingBlock("Ice Cold")) {
                            $obj->setWalkingBlock("Ice Cold");
                            $obj->getPlayer()->sendMessage(LobbySystem::Prefix . LanguageProvider::getMessageContainer('lobby-selected-walkingblock', $player->getName(), ['#walkingblock' => TextFormat::AQUA . "Ice " . TextFormat::WHITE . "Cold"]));
                        } else {
                            $player->sendForm(new ReallyBuyAddonForm($obj->getPlayer()->getName(), "Ice Cold", 10000, ReallyBuyAddonForm::WALKING_BLOCKS));
                        }
                    }
                    break;
                case 3:
                    if (($obj = LobbySystem::getPlayerCache($player->getName())) != null) {
                        if ($obj->haveBoughtWalkingBlock("Farmer")) {
                            $obj->setWalkingBlock("Farmer");
                            $obj->getPlayer()->sendMessage(LobbySystem::Prefix . LanguageProvider::getMessageContainer('lobby-selected-walkingblock', $player->getName(), ['#walkingblock' => TextFormat::RED . "Farmer"]));
                        } else {
                            $player->sendForm(new ReallyBuyAddonForm($obj->getPlayer()->getName(), "Farmer", 10000, ReallyBuyAddonForm::WALKING_BLOCKS));
                        }
                    }
                    break;
                case 4:
                    if (($obj = LobbySystem::getPlayerCache($player->getName())) != null) {
                        if ($obj->haveBoughtWalkingBlock("Concrete")) {
                            $obj->setWalkingBlock("Concrete");
                            $obj->getPlayer()->sendMessage(LobbySystem::Prefix . LanguageProvider::getMessageContainer('lobby-selected-walkingblock', $player->getName(), ['#walkingblock' => TextFormat::LIGHT_PURPLE . "Concrete"]));
                        } else {
                            $player->sendForm(new ReallyBuyAddonForm($obj->getPlayer()->getName(), "Concrete", 10000, ReallyBuyAddonForm::WALKING_BLOCKS));
                        }
                    }
                    break;
                case 5:
                    if (($obj = LobbySystem::getPlayerCache($player->getName())) != null) {
                        if ($obj->haveBoughtWalkingBlock("BedWars")) {
                            $obj->setWalkingBlock("BedWars");
                            $obj->getPlayer()->sendMessage(LobbySystem::Prefix . LanguageProvider::getMessageContainer('lobby-selected-walkingblock', $player->getName(), ['#walkingblock' => TextFormat::RED . "BedWars"]));
                        } else {
                            $player->sendForm(new ReallyBuyAddonForm($obj->getPlayer()->getName(), "BedWars", 10000, ReallyBuyAddonForm::WALKING_BLOCKS));
                        }
                    }
                    break;
                case 6:
                    if (($obj = LobbySystem::getPlayerCache($player->getName())) != null) {
                        $obj->setWalkingBlock(null);
                        ItemProvider::clearAllInvs($player);
                        ItemProvider::giveLobbyItems($player);
                    }
                    break;
            }
        });
    }
}