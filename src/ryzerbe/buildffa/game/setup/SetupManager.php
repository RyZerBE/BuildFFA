<?php

declare(strict_types=1);

namespace ryzerbe\buildffa\game\setup;

use ryzerbe\buildffa\game\setup\item\SetupMapSettingsItem;
use ryzerbe\buildffa\game\setup\item\SetupSpawnItem;
use ryzerbe\core\util\customitem\CustomItem;
use ryzerbe\core\util\customitem\CustomItemManager;

class SetupManager {
    /** @var CustomItem[]  */
    protected static array $items = [];

    public static function init(): void {
        self::register(new SetupSpawnItem());
        self::register(new SetupMapSettingsItem());
    }

    public static function register(CustomItem $item): void {
        CustomItemManager::getInstance()->registerCustomItem($item);
        self::$items[$item->getClass()] = $item;
    }

    public static function getItems(): array{
        return self::$items;
    }
}