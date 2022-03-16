<?php

declare(strict_types=1);

namespace ryzerbe\buildffa\game\safezone;

use Exception;
use pocketmine\Server;
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
        try{
            CustomItemManager::getInstance()->registerCustomItem($item);
            self::$items[$item->getClass()] = $item;
        } catch(Exception $exception) {
            Server::getInstance()->getLogger()->logException($exception);
        }
    }

    public static function getItems(): array{
        return self::$items;
    }
}