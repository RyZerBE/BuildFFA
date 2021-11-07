<?php


namespace BauboLP\LobbySystem\Provider;


use BauboLP\Core\Provider\LanguageProvider;
use BauboLP\Core\Provider\StaffProvider;
use BauboLP\LobbySystem\Forms\addons\AddonMainMenu;
use BauboLP\LobbySystem\Forms\GameModerForm;
use BauboLP\LobbySystem\Forms\LobbySwitcherForm;
use BauboLP\LobbySystem\LobbySystem;
use BauboLP\LobbySystem\Utils\LobbyPlayer;
use pocketmine\item\Item;
use pocketmine\network\mcpe\protocol\types\EntityLink;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class ItemProvider
{
    /**
     * @param \pocketmine\Player $player
     */
    public static function giveLobbyItems(Player $player)
    {
        self::clearAllInvs($player);
        $teleporter = Item::get(Item::FIREWORKS, 0, 1)->setCustomName("Navigator");
        $lobbySwitcher = Item::get(Item::NETHER_STAR, 0, 1)->setCustomName(TextFormat::AQUA . "LobbySwitcher");
        $addons = Item::get(Item::COOKIE, 0, 1)->setCustomName(TextFormat::GOLD . "Addons");
        $shield = Item::get(Item::ENDER_EYE, 0, 1)->setCustomName(TextFormat::RED . "Shield");

        $inv = $player->getInventory();
        $inv->setItem(4, $teleporter);
        $inv->setItem(5, $lobbySwitcher);
        $inv->setItem(2, $addons);
        if($player->hasPermission("lobby.shield") || StaffProvider::isLogin($player->getName()))
        $inv->setItem(6, $shield);

      //  $player->getArmorInventory()->setChestplate(Item::get(Item::ELYTRA));


        ///// GADGET STUFF \\\\\\
        if(($obj = LobbySystem::getPlayerCache($player->getName())) != null) {
            if($obj->getSpecial() != null && $obj->getSpecial() != "") {
                if($obj->getSpecial() == "Spiderman") {
                    $inv->setItem(0, Item::get(Item::DIAMOND_HOE, 0, 1)->setCustomName(TextFormat::RED."Spiderman"));
                }else if($obj->getSpecial() == "Bomber") {
                    $inv->setItem(0, Item::get(Item::TNT, 0, 1)->setCustomName(TextFormat::YELLOW."Bomber"));
                }else if($obj->getSpecial() == "Paintball Gun") {
                    $inv->setItem(0, Item::get(Item::GOLD_HOE, 0, 1)->setCustomName(TextFormat::GOLD."Paintball Gun"));
                }
            }
        }
    }


    public static function execItem(Player $player)
    {
        if (isset(AnimationProvider::$delay[$player->getName()])) return;

        $item = $player->getInventory()->getItemInHand();
        $itemName = TextFormat::clean($item->getCustomName());
        switch ($itemName) {
            case "Navigator":
                $player->sendForm(new GameModerForm());
                AnimationProvider::$delay[$player->getName()] = time() + 0.5;
                break;
            case "LobbySwitcher":
                $player->sendForm(new LobbySwitcherForm());
                AnimationProvider::$delay[$player->getName()] = time() + 0.5;
                break;
            case "Addons":
                if (($obj = LobbySystem::getPlayerCache($player->getName())) != null)
                    $player->sendForm(new AddonMainMenu($obj));

                AnimationProvider::$delay[$player->getName()] = time() + 0.5;
                break;
            case "Shield":
                AnimationProvider::$delay[$player->getName()] = time() + 0.5;

                if (($obj = LobbySystem::getPlayerCache($player->getName())) != null) {
                    if($obj->isShield()) {
                        $obj->setShield(false);
                        $player->sendActionBarMessage(LobbySystem::Prefix.LanguageProvider::getMessageContainer('lobby-shield-deactivated', $player->getName()));
                    }else {
                        $obj->setShield(true);
                        $player->sendActionBarMessage(LobbySystem::Prefix.LanguageProvider::getMessageContainer('lobby-shield-activated', $player->getName()));
                    }
                }
                break;
            case "Quit":
                if (($obj = LobbySystem::getPlayerCache($player->getName())) != null) {
                    $obj->resetTimer();
                    $player->setAllowFlight(true);
                    $obj->setPlayingJumpAndRun(false);
                    ItemProvider::giveLobbyItems($player);
                    foreach (Server::getInstance()->getOnlinePlayers() as $onlinePlayer) {
                        $player->showPlayer($onlinePlayer);
                    }
                }
                    break;
            case "Go to start":
                $player->teleport(LobbyGamesProvider::$jumpAndRunStartVec);
                $player->playSound("mob.endermen.portal", 5.0, 1.0, [$player]);
                if (($obj = LobbySystem::getPlayerCache($player->getName())) != null)
                    $obj->resetTimer();
                    break;
        }
    }

    public static function clearAllInvs(Player $player)
    {
        $player->getInventory()->clearAll();
        $player->getCraftingGrid()->clearAll();
        $player->getArmorInventory()->clearAll();
        $player->removeAllEffects();
    }
}