<?php


namespace BauboLP\BuildFFA\forms\invsort;


use BauboLP\BuildFFA\BuildFFA;
use BauboLP\BuildFFA\provider\GameProvider;
use BauboLP\BuildFFA\provider\ItemProvider;
use BauboLP\BuildFFA\utils\BuildFFAPlayer;
use BauboLP\BuildFFA\utils\Kits;
use ryzerbe\core\language\LanguageProvider;
use muqsit\invmenu\InvMenu;
use muqsit\invmenu\transaction\InvMenuTransaction;
use muqsit\invmenu\transaction\InvMenuTransactionResult;
use pocketmine\inventory\Inventory;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class InvSortMenu
{
    /**
     * @param int $kit
     * @param \BauboLP\BuildFFA\utils\BuildFFAPlayer $player
     */
    public static function loadSort(int $kit, BuildFFAPlayer $player)
    {
        ItemProvider::clearAllInvs($player->getPlayer());
        $unbr = Enchantment::getEnchantment(Enchantment::UNBREAKING);
        $sharpness = Enchantment::getEnchantment(Enchantment::SHARPNESS);
        $effi = Enchantment::getEnchantment(Enchantment::EFFICIENCY);
        $knockback = Enchantment::getEnchantment(Enchantment::EFFICIENCY);
        $infinity = Enchantment::getEnchantment(Enchantment::INFINITY);


        $invMenu = InvMenu::create(InvMenu::TYPE_CHEST)
        ->setName(BuildFFA::Prefix.TextFormat::YELLOW."Sort your Inventory")
            ->setListener(function (InvMenuTransaction $transaction): InvMenuTransactionResult{
                $clicked = $transaction->getItemClicked();
                if($clicked->getId() === Item::STAINED_GLASS_PANE)
                    return $transaction->discard();
                return $transaction->continue();
            })
            ->setInventoryCloseListener(function (Player $player, Inventory $inv){
                if(($obj = GameProvider::getBuildFFAPlayer($player->getName())) != null) {
                    if($obj->isSort()) {
                        $save = true;
                        foreach ($player->getInventory()->getContents(true) as $slot => $item) {
                            if($item->getId() != Item::AIR) {
                                $save = false;
                            }
                        }

                        $translateToInvSlot = [
                            9 => 0,
                            10 => 1,
                            11 => 2,
                            12 => 3,
                            13 => 4,
                            14 => 5,
                            15 => 6,
                            16 => 7,
                            17 => 8
                        ];

                        if($save) {
                            $canBeSave = [9, 10, 11, 12, 13, 14, 15, 16, 17];

                            $sword = null;
                            $stick = null;
                            $blocks = null;
                            $webs = null;
                            $kitItem = null;
                            $rp = null;
                            $ep = null;
                            foreach ($inv->getContents(true) as $slot => $item) {
                                if($item->getId() != Item::AIR && $item->getId() != Item::GLASS_PANE) {
                                    if($item->getId() == Item::GOLD_SWORD) {
                                        if(in_array($slot, $canBeSave)) {
                                            $sword = $translateToInvSlot[$slot];
                                        }
                                    }else if($item->getId() == Item::STICK) {
                                        if(in_array($slot, $canBeSave)) {
                                            $stick = $translateToInvSlot[$slot];
                                        }
                                    }else if($item->getId() == Item::RED_SANDSTONE) {
                                        if(in_array($slot, $canBeSave)) {
                                            $blocks = $translateToInvSlot[$slot];
                                        }
                                    }else if($item->getId() == Item::WEB) {
                                        if(in_array($slot, $canBeSave)) {
                                            $webs = $translateToInvSlot[$slot];
                                        }
                                    }else if($item->getId() == Item::TNT || $item->getId() == Item::FISHING_ROD || $item->getId() == Item::SNOWBALL || $item->getId() == Item::IRON_PICKAXE || $item->getId() == Item::BOW) {
                                        if(in_array($slot, $canBeSave)) {
                                            $kitItem = $translateToInvSlot[$slot];
                                        }
                                    }else if($item->getId() == Item::BLAZE_ROD) {
                                        if(in_array($slot, $canBeSave)) {
                                            $rp = $translateToInvSlot[$slot];
                                        }
                                    }else if($item->getId() == Item::ENDER_PEARL) {
                                        if(in_array($slot, $canBeSave)) {
                                            $ep = $translateToInvSlot[$slot];
                                        }
                                    }
                                }
                            }

                            if($sword == null || $kitItem == null || $webs == null || $stick == null || $ep == null || $rp == null || $blocks == null) {
                                $sort = explode(":", "$sword:$stick:$blocks:$webs:$kitItem:$rp:$ep");
                                $obj->setSort(false);
                                $newInvSort = $obj->getInvSorts();
                                $newInvSort[GameProvider::getKit()] = ["blocks" => $sort[2], "stick" => $sort[1], "sword" => $sort[0], "webs" => $sort[3], "kit-item" => $sort[4], "rp" => $sort[5], "ep" => $sort[6]];
                                $obj->setInvSorts($newInvSort);
                                $obj->giveItems();
                                $obj->getPlayer()->playSound('random.levelup', 5, 1.0, [$player]);
                            }else {
                                $obj->giveItems();
                                $obj->getPlayer()->playSound('random.bass', 1, 1.0, [$obj->getPlayer()]);
                                $player->sendMessage(BuildFFA::Prefix.LanguageProvider::getMessageContainer('something-went-wrong', $player->getName()));
                            }
                        }else {
                            $obj->giveItems();
                            $obj->getPlayer()->playSound('random.bass', 1, 1.0, [$obj->getPlayer()]);
                            $player->sendMessage(BuildFFA::Prefix.LanguageProvider::getMessageContainer('something-went-wrong', $player->getName()));
                        }
                    }
                }
            });

        $blocks = Item::get(Item::RED_SANDSTONE, 0, 64)->setCustomName(TextFormat::GOLD."Bausteine");
        $enderPearl = Item::get(Item::ENDER_PEARL, 0, 1)->setCustomName(TextFormat::GREEN."EnderPearl");
        $rp = Item::get(Item::BLAZE_ROD, 0, 1)->setCustomName(TextFormat::GREEN."Rettungsplattform");

        $player->setSort(true);
        $inv = $invMenu->getInventory();

        for($i = 0; $i < 9; $i++) {
            $inv->setItem($i, Item::get(Item::STAINED_GLASS_PANE, 0, 1)->setCustomName(""));
        }

        for($i = 17; $i < 27; $i++) {
            $inv->setItem($i, Item::get(Item::STAINED_GLASS_PANE, 0, 1)->setCustomName(""));
        }

        $translateToChestSlot = [
            0 => 9,
            1 => 10,
            2 => 11,
            3 => 12,
            4 => 13,
            5 => 14,
            6 => 15,
            7 => 16,
            8 => 17
        ];

        if($kit == Kits::RUSHER) {
            $stick = Item::get(Item::STICK, 0, 1)->setCustomName(TextFormat::GOLD."Knüppel");
            $sword = Item::get(Item::GOLD_SWORD, 0, 1)->setCustomName(TextFormat::GOLD."Machete");
            $webs = Item::get(Item::WEB, 0, 3)->setCustomName(TextFormat::GOLD."Web");
            $pickaxe = Item::get(Item::IRON_PICKAXE, 0, 1)->setCustomName(TextFormat::GOLD."Spitzhacke");

            $stick->addEnchantment(new EnchantmentInstance($knockback, 1));
            $sword->addEnchantment(new EnchantmentInstance($sharpness, 1));
            $sword->addEnchantment(new EnchantmentInstance($unbr, 20));
            $pickaxe->addEnchantment(new EnchantmentInstance($effi, 2));
            $pickaxe->addEnchantment(new EnchantmentInstance($unbr, 20));


            $sort = $player->getInvSorts()[Kits::RUSHER];
            $inv->setItem($translateToChestSlot[$sort["sword"]], $sword);
            $inv->setItem($translateToChestSlot[$sort["stick"]], $stick);
            $inv->setItem($translateToChestSlot[$sort["blocks"]], $blocks);
            $inv->setItem($translateToChestSlot[$sort["webs"]], $webs);
            $inv->setItem($translateToChestSlot[$sort["kit-item"]], $pickaxe);
            $inv->setItem($translateToChestSlot[$sort["rp"]], $rp);
            $inv->setItem($translateToChestSlot[$sort["ep"]], $enderPearl);
        }elseif($kit == Kits::SPAMMER) {
            $stick = Item::get(Item::STICK, 0, 1)->setCustomName(TextFormat::GOLD."Knüppel");
            $sword = Item::get(Item::GOLD_SWORD, 0, 1)->setCustomName(TextFormat::GOLD."Machete");
            $webs = Item::get(Item::WEB, 0, 3)->setCustomName(TextFormat::GOLD."Web");
            $bow = Item::get(Item::BOW, 0, 1)->setCustomName(TextFormat::GOLD."Bogen");

            $stick->addEnchantment(new EnchantmentInstance($knockback, 1));
            $sword->addEnchantment(new EnchantmentInstance($sharpness, 1));
            $sword->addEnchantment(new EnchantmentInstance($unbr, 20));
            $bow->addEnchantment(new EnchantmentInstance($unbr, 20));
            $bow->addEnchantment(new EnchantmentInstance($infinity, 1));

            $sort = $player->getInvSorts()[Kits::SPAMMER];
            $inv->setItem($translateToChestSlot[$sort["sword"]], $sword);
            $inv->setItem($translateToChestSlot[$sort["stick"]], $stick);
            $inv->setItem($translateToChestSlot[$sort["blocks"]], $blocks);
            $inv->setItem($translateToChestSlot[$sort["webs"]], $webs);
            $inv->setItem($translateToChestSlot[$sort["kit-item"]], $bow);
            $inv->setItem($translateToChestSlot[$sort["rp"]], $rp);
            $inv->setItem($translateToChestSlot[$sort["ep"]], $enderPearl);
        }elseif($kit == Kits::BASEDEF) {
            $stick = Item::get(Item::STICK, 0, 1)->setCustomName(TextFormat::GOLD."Knüppel");
            $sword = Item::get(Item::GOLD_SWORD, 0, 1)->setCustomName(TextFormat::GOLD."Machete");
            $webs = Item::get(Item::WEB, 0, 3)->setCustomName(TextFormat::GOLD."Web");
            $rod = Item::get(Item::FISHING_ROD, 0, 1)->setCustomName(TextFormat::GOLD."Angel");

            $stick->addEnchantment(new EnchantmentInstance($knockback, 1));
            $sword->addEnchantment(new EnchantmentInstance($sharpness, 1));
            $sword->addEnchantment(new EnchantmentInstance($unbr, 20));

            $sort = $player->getInvSorts()[Kits::BASEDEF];
            $inv->setItem($translateToChestSlot[$sort["sword"]], $sword);
            $inv->setItem($translateToChestSlot[$sort["stick"]], $stick);
            $inv->setItem($translateToChestSlot[$sort["blocks"]], $blocks);
            $inv->setItem($translateToChestSlot[$sort["webs"]], $webs);
            $inv->setItem($translateToChestSlot[$sort["kit-item"]], $rod);
            $inv->setItem($translateToChestSlot[$sort["rp"]], $rp);
            $inv->setItem($translateToChestSlot[$sort["ep"]], $enderPearl);
        }elseif($kit == Kits::TNT) {
            $stick = Item::get(Item::STICK, 0, 1)->setCustomName(TextFormat::GOLD."Knüppel");
            $sword = Item::get(Item::GOLD_SWORD, 0, 1)->setCustomName(TextFormat::GOLD."Machete");
            $webs = Item::get(Item::WEB, 0, 3)->setCustomName(TextFormat::GOLD."Web");
            $tnt = Item::get(Item::TNT, 0, 3)->setCustomName(TextFormat::GOLD."Sprengkörper");

            $stick->addEnchantment(new EnchantmentInstance($knockback, 1));
            $sword->addEnchantment(new EnchantmentInstance($sharpness, 1));
            $sword->addEnchantment(new EnchantmentInstance($unbr, 20));


            $sort = $player->getInvSorts()[Kits::TNT];
            $inv->setItem($translateToChestSlot[$sort["sword"]], $sword);
            $inv->setItem($translateToChestSlot[$sort["stick"]], $stick);
            $inv->setItem($translateToChestSlot[$sort["blocks"]], $blocks);
            $inv->setItem($translateToChestSlot[$sort["webs"]], $webs);
            $inv->setItem($translateToChestSlot[$sort["kit-item"]], $tnt);
            $inv->setItem($translateToChestSlot[$sort["rp"]], $rp);
            $inv->setItem($translateToChestSlot[$sort["ep"]], $enderPearl);
        }elseif($kit == Kits::SNOWBALL) {
            $stick = Item::get(Item::STICK, 0, 1)->setCustomName(TextFormat::GOLD . "Knüppel");
            $sword = Item::get(Item::GOLD_SWORD, 0, 1)->setCustomName(TextFormat::GOLD . "Machete");
            $webs = Item::get(Item::WEB, 0, 1)->setCustomName(TextFormat::GOLD . "Web");
            $snowballs = Item::get(Item::SNOWBALL, 0, 16)->setCustomName(TextFormat::GOLD . "Snowballs");

            $stick->addEnchantment(new EnchantmentInstance($knockback, 1));
            $sword->addEnchantment(new EnchantmentInstance($sharpness, 1));
            $sword->addEnchantment(new EnchantmentInstance($unbr, 20));


            $sort = $player->getInvSorts()[Kits::SNOWBALL];
            $inv->setItem($translateToChestSlot[$sort["sword"]], $sword);
            $inv->setItem($translateToChestSlot[$sort["stick"]], $stick);
            $inv->setItem($translateToChestSlot[$sort["blocks"]], $blocks);
            $inv->setItem($translateToChestSlot[$sort["webs"]], $webs);
            $inv->setItem($translateToChestSlot[$sort["kit-item"]], $snowballs);
            $inv->setItem($translateToChestSlot[$sort["rp"]], $rp);
            $inv->setItem($translateToChestSlot[$sort["ep"]], $enderPearl);
        }
        $invMenu->send($player->getPlayer());
    }
}