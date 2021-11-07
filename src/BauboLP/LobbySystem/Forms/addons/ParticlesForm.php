<?php


namespace BauboLP\LobbySystem\Forms\addons;


use BauboLP\Core\Provider\LanguageProvider;
use BauboLP\LobbySystem\LobbySystem;
use BauboLP\LobbySystem\Utils\LobbyPlayer;
use pocketmine\form\MenuForm;
use pocketmine\form\MenuOption;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class ParticlesForm extends MenuForm
{

    public function __construct(LobbyPlayer $player)
    {
        $options = [];
        if($player->haveBoughtParticle("Hearts")) {
            $options[] = new MenuOption(TextFormat::RED."Hearts\n".TextFormat::GREEN."Click to use");
        }else {
            $options[] = new MenuOption(TextFormat::RED."Hearts\n".TextFormat::YELLOW."5000 Coins");
        }
        if($player->haveBoughtParticle("AngryVillager")) {
            $options[] = new MenuOption(TextFormat::LIGHT_PURPLE."AngryVillager\n".TextFormat::GREEN."Click to use");
        }else {
            $options[] = new MenuOption(TextFormat::LIGHT_PURPLE."AngryVillager\n".TextFormat::YELLOW."5000 Coins");
        }
        if($player->haveBoughtParticle("HappyVillager")) {
            $options[] = new MenuOption(TextFormat::GREEN."HappyVillager\n".TextFormat::GREEN."Click to use");
        }else {
            $options[] = new MenuOption(TextFormat::GREEN."HappyVillager\n".TextFormat::YELLOW."5000 Coins");
        }
        if($player->haveBoughtParticle("Enchant")) {
            $options[] = new MenuOption(TextFormat::AQUA."Enchant\n".TextFormat::GREEN."Click to use");
        }else {
            $options[] = new MenuOption(TextFormat::AQUA."Enchant\n".TextFormat::YELLOW."5000 Coins");
        }
        if($player->haveBoughtParticle("Critical")) {
            $options[] = new MenuOption(TextFormat::RED."Critical\n".TextFormat::GREEN."Click to use");
        }else {
            $options[] = new MenuOption(TextFormat::RED."Critical\n".TextFormat::YELLOW."5000 Coins");
        }
        if($player->haveBoughtParticle("HugeExplode")) {
            $options[] = new MenuOption(TextFormat::GRAY."HugeExplode\n".TextFormat::GREEN."Click to use");
        }else {
            $options[] = new MenuOption(TextFormat::GRAY."HugeExplode\n".TextFormat::YELLOW."5000 Coins");
        }
        if($player->haveBoughtParticle("Lava")) {
            $options[] = new MenuOption(TextFormat::GOLD."Lava\n".TextFormat::GREEN."Click to use");
        }else {
            $options[] = new MenuOption(TextFormat::GOLD."Lava\n".TextFormat::YELLOW."5000 Coins");
        }
        if($player->haveBoughtParticle("LavaDrip")) {
            $options[] = new MenuOption(TextFormat::GOLD."LavaDrip\n".TextFormat::GREEN."Click to use");
        }else {
            $options[] = new MenuOption(TextFormat::GOLD."LavaDrip\n".TextFormat::YELLOW."5000 Coins");
        }
        if($player->haveBoughtParticle("Portal")) {
            $options[] = new MenuOption(TextFormat::DARK_PURPLE."Portal\n".TextFormat::GREEN."Click to use");
        }else {
            $options[] = new MenuOption(TextFormat::DARK_PURPLE."Portal\n".TextFormat::YELLOW."5000 Coins");
        }
        $options[] = new MenuOption(TextFormat::DARK_RED."Remove");

        parent::__construct(LobbySystem::Prefix.TextFormat::AQUA."Addons", "", $options, function (Player $player, int $selectedOption): void {
            switch ($selectedOption) {
                case 0:
                    if (($obj = LobbySystem::getPlayerCache($player->getName())) != null) {
                        if ($obj->haveBoughtParticle("Hearts")) {
                            $obj->setParticle("Hearts");
                            $obj->getPlayer()->sendMessage(LobbySystem::Prefix . LanguageProvider::getMessageContainer('lobby-selected-particle', $player->getName(), ['#particle' => TextFormat::RED . "Hearts"]));
                        } else {
                            $player->sendForm(new ReallyBuyAddonForm($obj->getPlayer()->getName(), "Hearts", 5000, ReallyBuyAddonForm::PARTICLE));
                        }
                    }
                    break;
                case 1:
                    if (($obj = LobbySystem::getPlayerCache($player->getName())) != null) {
                        if ($obj->haveBoughtParticle("AngryVillager")) {
                            $obj->setParticle("AngryVillager");
                            $obj->getPlayer()->sendMessage(LobbySystem::Prefix . LanguageProvider::getMessageContainer('lobby-selected-particle', $player->getName(), ['#particle' => TextFormat::LIGHT_PURPLE . "AngryVillager"]));
                        } else {
                            $player->sendForm(new ReallyBuyAddonForm($obj->getPlayer()->getName(), "AngryVillager", 5000, ReallyBuyAddonForm::PARTICLE));
                        }
                    }
                    break;
                case 2:
                    if (($obj = LobbySystem::getPlayerCache($player->getName())) != null) {
                        if ($obj->haveBoughtParticle("HappyVillager")) {
                            $obj->setParticle("HappyVillager");
                            $obj->getPlayer()->sendMessage(LobbySystem::Prefix . LanguageProvider::getMessageContainer('lobby-selected-particle', $player->getName(), ['#particle' => TextFormat::GREEN . "HappyVillager"]));
                        } else {
                            $player->sendForm(new ReallyBuyAddonForm($obj->getPlayer()->getName(), "HappyVillager", 5000, ReallyBuyAddonForm::PARTICLE));
                        }
                    }
                    break;
                case 3:
                    if (($obj = LobbySystem::getPlayerCache($player->getName())) != null) {
                        if ($obj->haveBoughtParticle("Enchant")) {
                            $obj->setParticle("Enchant");
                            $obj->getPlayer()->sendMessage(LobbySystem::Prefix . LanguageProvider::getMessageContainer('lobby-selected-particle', $player->getName(), ['#particle' => TextFormat::AQUA . "Enchant"]));
                        } else {
                            $player->sendForm(new ReallyBuyAddonForm($obj->getPlayer()->getName(), "Enchant", 5000, ReallyBuyAddonForm::PARTICLE));
                        }
                    }
                    break;
                case 4:
                    if (($obj = LobbySystem::getPlayerCache($player->getName())) != null) {
                        if ($obj->haveBoughtParticle("Critical")) {
                            $obj->setParticle("Critical");
                            $obj->getPlayer()->sendMessage(LobbySystem::Prefix . LanguageProvider::getMessageContainer('lobby-selected-particle', $player->getName(), ['#particle' => TextFormat::RED . "Critical"]));
                        } else {
                            $player->sendForm(new ReallyBuyAddonForm($obj->getPlayer()->getName(), "Critical", 5000, ReallyBuyAddonForm::PARTICLE));
                        }
                    }
                    break;
                case 5:
                    if (($obj = LobbySystem::getPlayerCache($player->getName())) != null) {
                        if ($obj->haveBoughtParticle("HugeExplode")) {
                            $obj->setParticle("HugeExplode");
                            $obj->getPlayer()->sendMessage(LobbySystem::Prefix . LanguageProvider::getMessageContainer('lobby-selected-particle', $player->getName(), ['#particle' => TextFormat::GRAY . "HugeExplode"]));
                        } else {
                            $player->sendForm(new ReallyBuyAddonForm($obj->getPlayer()->getName(), "HugeExplode", 5000, ReallyBuyAddonForm::PARTICLE));
                        }
                    }
                    break;
                case 6:
                    if (($obj = LobbySystem::getPlayerCache($player->getName())) != null) {
                        if ($obj->haveBoughtParticle("Lava")) {
                            $obj->setParticle("Lava");
                            $obj->getPlayer()->sendMessage(LobbySystem::Prefix . LanguageProvider::getMessageContainer('lobby-selected-particle', $player->getName(), ['#particle' => TextFormat::GOLD . "Lava"]));
                        } else {
                            $player->sendForm(new ReallyBuyAddonForm($obj->getPlayer()->getName(), "Lava", 5000, ReallyBuyAddonForm::PARTICLE));
                        }
                    }
                    break;
                case 7:
                    if (($obj = LobbySystem::getPlayerCache($player->getName())) != null) {
                        if ($obj->haveBoughtParticle("LavaDrip")) {
                            $obj->setParticle("LavaDrip");
                            $obj->getPlayer()->sendMessage(LobbySystem::Prefix . LanguageProvider::getMessageContainer('lobby-selected-particle', $player->getName(), ['#particle' => TextFormat::GOLD . "LavaDrip"]));
                        } else {
                            $player->sendForm(new ReallyBuyAddonForm($obj->getPlayer()->getName(), "LavaDrip", 5000, ReallyBuyAddonForm::PARTICLE));
                        }
                    }
                    break;
                case 8:
                    if (($obj = LobbySystem::getPlayerCache($player->getName())) != null) {
                        if ($obj->haveBoughtParticle("Portal")) {
                            $obj->setParticle("Portal");
                            $obj->getPlayer()->sendMessage(LobbySystem::Prefix . LanguageProvider::getMessageContainer('lobby-selected-particle', $player->getName(), ['#particle' => TextFormat::DARK_PURPLE . "Portal"]));
                        } else {
                            $player->sendForm(new ReallyBuyAddonForm($obj->getPlayer()->getName(), "Portal", 5000, ReallyBuyAddonForm::PARTICLE));
                        }
                    }
                    break;
                case 9:
                    if (($obj = LobbySystem::getPlayerCache($player->getName())) != null) {
                        $obj->setParticle(null);
                    }
                    break;
            }
        });
    }

}