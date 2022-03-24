<?php

declare(strict_types=1);

namespace ryzerbe\buildffa\game\kit;

use Exception;
use pocketmine\block\BlockIds;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\Item;
use pocketmine\item\ItemIds;
use pocketmine\Server;
use ryzerbe\buildffa\game\kit\item\EnderPearl;
use ryzerbe\buildffa\game\kit\item\RescuePlatform;
use ryzerbe\core\util\customitem\CustomItemManager;
use ryzerbe\core\util\ItemUtils;

class KitManager {
    /** @var Kit[]  */
    protected static array $kits = [];

    public static function init(): void {
        /** @var CustomItemManager $customItemManager */
        $customItemManager = CustomItemManager::getInstance();
        try{
            $customItemManager->registerAll([
                new EnderPearl(),
                new RescuePlatform(),
            ]);
        } catch(Exception $exception) {
            Server::getInstance()->getLogger()->logException($exception);
        }

        self::register(new Kit("Rusher", [
            "sword" => ItemUtils::addEnchantments(Item::get(ItemIds::GOLD_SWORD)->setCustomName("§r§6Stick"), [
                Enchantment::SHARPNESS => 1
            ]),
            "stick" => ItemUtils::addEnchantments(Item::get(ItemIds::STICK)->setCustomName("§r§6Stick"), [
                Enchantment::KNOCKBACK => 1
            ]),
            "pickaxe" => ItemUtils::addEnchantments(Item::get(ItemIds::IRON_PICKAXE)->setCustomName("§r§6Pickaxe"), [
                Enchantment::EFFICIENCY => 2
            ]),
            "block_1" => ItemUtils::addItemTags(Item::get(BlockIds::SANDSTONE, 0, 64), [
                Kit::TAG_INFINITE => "1"
            ]),

            "enderpearl" => $customItemManager->getCustomItemByClass(EnderPearl::class)->getItem(),
            "rescue_platform" => $customItemManager->getCustomItemByClass(RescuePlatform::class)->getItem(),

            "helmet" => ItemUtils::addEnchantments(Item::get(ItemIds::LEATHER_HELMET)->setCustomName("§r§6Helmet"), [
                Enchantment::PROTECTION => 1
            ]),
            "chestplate" => ItemUtils::addEnchantments(Item::get(ItemIds::IRON_CHESTPLATE)->setCustomName("§r§6Chestplate"), [
                Enchantment::PROTECTION => 1
            ]),
            "leggings" => ItemUtils::addEnchantments(Item::get(ItemIds::LEATHER_LEGGINGS)->setCustomName("§r§6Leggings"), [
                Enchantment::PROTECTION => 1
            ]),
            "boots" => ItemUtils::addEnchantments(Item::get(ItemIds::LEATHER_BOOTS)->setCustomName("§r§6Boots"), [
                Enchantment::PROTECTION => 1
            ]),
        ]));

        self::register(new Kit("Spammer", [
            "sword" => ItemUtils::addEnchantments(Item::get(ItemIds::GOLD_SWORD)->setCustomName("§r§6Stick"), [
                Enchantment::SHARPNESS => 1
            ]),
            "bow" => Item::get(ItemIds::BOW),
            "pickaxe" => ItemUtils::addEnchantments(Item::get(ItemIds::IRON_PICKAXE)->setCustomName("§r§6Pickaxe"), [
                Enchantment::EFFICIENCY => 2
            ]),
            "block_1" => ItemUtils::addItemTags(Item::get(BlockIds::SANDSTONE, 0, 64), [
                Kit::TAG_INFINITE => "1"
            ]),

            "enderpearl" => $customItemManager->getCustomItemByClass(EnderPearl::class)->getItem(),
            "rescue_platform" => $customItemManager->getCustomItemByClass(RescuePlatform::class)->getItem(),

            "helmet" => ItemUtils::addEnchantments(Item::get(ItemIds::LEATHER_HELMET)->setCustomName("§r§6Helmet"), [
                Enchantment::PROTECTION => 1
            ]),
            "chestplate" => ItemUtils::addEnchantments(Item::get(ItemIds::IRON_CHESTPLATE)->setCustomName("§r§6Chestplate"), [
                Enchantment::PROTECTION => 1
            ]),
            "leggings" => ItemUtils::addEnchantments(Item::get(ItemIds::LEATHER_LEGGINGS)->setCustomName("§r§6Leggings"), [
                Enchantment::PROTECTION => 1
            ]),
            "boots" => ItemUtils::addEnchantments(Item::get(ItemIds::LEATHER_BOOTS)->setCustomName("§r§6Boots"), [
                Enchantment::PROTECTION => 1
            ]),
			"arrow" => Item::get(ItemIds::ARROW, 0, 8)
        ]));

        self::register(new Kit("Basedef", [
            "sword" => ItemUtils::addEnchantments(Item::get(ItemIds::GOLD_SWORD)->setCustomName("§r§6Stick"), [
                Enchantment::SHARPNESS => 1
            ]),
            "rod" => Item::get(ItemIds::FISHING_ROD),
            "pickaxe" => ItemUtils::addEnchantments(Item::get(ItemIds::IRON_PICKAXE)->setCustomName("§r§6Pickaxe"), [
                Enchantment::EFFICIENCY => 2
            ]),
            "block_1" => ItemUtils::addItemTags(Item::get(BlockIds::SANDSTONE, 0, 64), [
                Kit::TAG_INFINITE => "1"
            ]),

            "enderpearl" => $customItemManager->getCustomItemByClass(EnderPearl::class)->getItem(),
            "rescue_platform" => $customItemManager->getCustomItemByClass(RescuePlatform::class)->getItem(),

            "helmet" => ItemUtils::addEnchantments(Item::get(ItemIds::LEATHER_HELMET)->setCustomName("§r§6Helmet"), [
                Enchantment::PROTECTION => 1
            ]),
            "chestplate" => ItemUtils::addEnchantments(Item::get(ItemIds::IRON_CHESTPLATE)->setCustomName("§r§6Chestplate"), [
                Enchantment::PROTECTION => 1
            ]),
            "leggings" => ItemUtils::addEnchantments(Item::get(ItemIds::LEATHER_LEGGINGS)->setCustomName("§r§6Leggings"), [
                Enchantment::PROTECTION => 1
            ]),
            "boots" => ItemUtils::addEnchantments(Item::get(ItemIds::LEATHER_BOOTS)->setCustomName("§r§6Boots"), [
                Enchantment::PROTECTION => 1
            ]),
        ]));

        self::register(new Kit("Snowballer", [
            "sword" => ItemUtils::addEnchantments(Item::get(ItemIds::GOLD_SWORD)->setCustomName("§r§6Stick"), [
                Enchantment::SHARPNESS => 1
            ]),
            "snowballs" => Item::get(ItemIds::SNOWBALL, 0, 8),
            "pickaxe" => ItemUtils::addEnchantments(Item::get(ItemIds::IRON_PICKAXE)->setCustomName("§r§6Pickaxe"), [
                Enchantment::EFFICIENCY => 2
            ]),
            "block_1" => ItemUtils::addItemTags(Item::get(BlockIds::SANDSTONE, 0, 64), [
                Kit::TAG_INFINITE => "1"
            ]),

            "enderpearl" => $customItemManager->getCustomItemByClass(EnderPearl::class)->getItem(),
            "rescue_platform" => $customItemManager->getCustomItemByClass(RescuePlatform::class)->getItem(),

            "helmet" => ItemUtils::addEnchantments(Item::get(ItemIds::LEATHER_HELMET)->setCustomName("§r§6Helmet"), [
                Enchantment::PROTECTION => 1
            ]),
            "chestplate" => ItemUtils::addEnchantments(Item::get(ItemIds::IRON_CHESTPLATE)->setCustomName("§r§6Chestplate"), [
                Enchantment::PROTECTION => 1
            ]),
            "leggings" => ItemUtils::addEnchantments(Item::get(ItemIds::LEATHER_LEGGINGS)->setCustomName("§r§6Leggings"), [
                Enchantment::PROTECTION => 1
            ]),
            "boots" => ItemUtils::addEnchantments(Item::get(ItemIds::LEATHER_BOOTS)->setCustomName("§r§6Boots"), [
                Enchantment::PROTECTION => 1
            ]),
        ]));
    }

    public static function register(Kit $kit): void {
        self::$kits[$kit->getName()] = $kit;
    }

    public static function getKit(string $kit): ?Kit {
        return self::$kits[$kit] ?? null;
    }

    public static function getKits(): array{
        return self::$kits;
    }
}