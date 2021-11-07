<?php


namespace BauboLP\LobbySystem\Forms\addons;


use BauboLP\Core\Provider\LanguageProvider;
use BauboLP\LobbySystem\LobbySystem;
use BauboLP\LobbySystem\Utils\LobbyPlayer;
use pocketmine\form\MenuForm;
use pocketmine\form\MenuOption;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class WingsForm extends MenuForm
{

    public function __construct(LobbyPlayer $player)
    {
        $options = [];
        if($player->haveBoughtWing("Heart Wings")) {
            $options[] = new MenuOption(TextFormat::RED."Heart Wings\n".TextFormat::GREEN."Click to use");
        }else {
            $options[] = new MenuOption(TextFormat::RED."Heart Wings\n".TextFormat::YELLOW."20000 Coins");
        }
        if($player->haveBoughtWing("Fire Wings")) {
            $options[] = new MenuOption(TextFormat::GOLD."Fire Wings\n".TextFormat::GREEN."Click to use");
        }else {
            $options[] = new MenuOption(TextFormat::GOLD."Fire Wings\n".TextFormat::YELLOW."20000 Coins");
        }
        if($player->haveBoughtWing("LavaDrip Wings")) {
            $options[] = new MenuOption(TextFormat::GOLD."LavaDrip Wings\n".TextFormat::GREEN."Click to use");
        }else {
            $options[] = new MenuOption(TextFormat::GOLD."LavaDrip Wings\n".TextFormat::YELLOW."50000 Coins");
        }

        $options[] = new MenuOption(TextFormat::DARK_RED."Remove");
        parent::__construct(LobbySystem::Prefix . TextFormat::AQUA . "Addons", "", $options, function (Player $player, int $selectedOption): void {
                switch ($selectedOption) {
                    case 0:
                        if (($obj = LobbySystem::getPlayerCache($player->getName())) != null) {
                            if ($obj->haveBoughtWing("Heart Wings")) {
                                $obj->setWing("Heart Wings");
                                $obj->getPlayer()->sendMessage(LobbySystem::Prefix . LanguageProvider::getMessageContainer('lobby-selected-wing', $player->getName(), ['#wing' => TextFormat::RED . "Heart Wings"]));
                            } else {
                                $player->sendForm(new ReallyBuyAddonForm($obj->getPlayer()->getName(), "Heart Wings", 20000, ReallyBuyAddonForm::WING));
                            }
                        }
                        break;
                    case 1:
                        if (($obj = LobbySystem::getPlayerCache($player->getName())) != null) {
                            if ($obj->haveBoughtWing("Fire Wings")) {
                                $obj->setWing("Fire Wings");
                                $obj->getPlayer()->sendMessage(LobbySystem::Prefix . LanguageProvider::getMessageContainer('lobby-selected-wing', $player->getName(), ['#wing' => TextFormat::GOLD . "Fire Wings"]));
                            } else {
                                $player->sendForm(new ReallyBuyAddonForm($obj->getPlayer()->getName(), "Fire Wings", 20000, ReallyBuyAddonForm::WING));
                            }
                        }
                        break;
                    case 2:
                        if (($obj = LobbySystem::getPlayerCache($player->getName())) != null) {
                            if ($obj->haveBoughtWing("LavaDrip Wings")) {
                                $obj->setWing("LavaDrip Wings");
                                $obj->getPlayer()->sendMessage(LobbySystem::Prefix . LanguageProvider::getMessageContainer('lobby-selected-wing', $player->getName(), ['#wing' => TextFormat::GOLD . "LavaDrip Wings"]));
                            } else {
                                $player->sendForm(new ReallyBuyAddonForm($obj->getPlayer()->getName(), "LavaDrip Wings", 50000, ReallyBuyAddonForm::WING));
                            }
                        }
                        break;
                    case 3:
                        if (($obj = LobbySystem::getPlayerCache($player->getName())) != null)
                            $obj->setWing(null);
                        break;
                }
        });
    }
}