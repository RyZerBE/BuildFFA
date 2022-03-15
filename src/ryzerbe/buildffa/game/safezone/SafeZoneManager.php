<?php

declare(strict_types=1);

namespace ryzerbe\buildffa\game\safezone;

use ryzerbe\buildffa\game\safezone\item\SortInventoryItem;
use ryzerbe\buildffa\game\safezone\item\VoteKitItem;
use ryzerbe\buildffa\game\safezone\item\VoteMapItem;
use ryzerbe\core\util\customitem\CustomItem;
use ryzerbe\core\util\customitem\CustomItemManager;

class SafeZoneManager {
    /** @var CustomItem[]  */
    protected static array $items = [];

    public static function init(): void {
        self::register(new SortInventoryItem());
        self::register(new VoteKitItem());
        self::register(new VoteMapItem());
    }

    public static function register(CustomItem $item): void {
        CustomItemManager::getInstance()->registerCustomItem($item);
        self::$items[$item->getClass()] = $item;
    }

    public static function getItems(): array{
        return self::$items;
    }
}