<?php

declare(strict_types=1);

namespace ryzerbe\buildffa\game\map;

use pocketmine\level\Level;
use pocketmine\Server;
use pocketmine\utils\Config;
use ryzerbe\buildffa\BuildFFA;
use ryzerbe\core\util\Vector3Utils;
use function count;
use function floatval;
use function is_file;
use function scandir;

class MapManager {
    /** @var Map[]  */
    protected static array $maps = [];

    public static function init(): void {
        $server = Server::getInstance();
        $path = $server->getDataPath()."/worlds/";
        $defaultWorld = $server->getDefaultLevel()->getFolderName();
        foreach(scandir($path) as $world) {
            if(!is_file($path.$world."/settings.json")) continue;
            if($world === $defaultWorld) {
                BuildFFA::getInstance()->getLogger()->error("Default level must not be an map!");
                continue;
            }

            $settings = new Config($path.$world."/settings.json");
            self::addMap(new Map(
                $settings->get(Map::KEY_NAME, "Unknown"),
                $world,
                Vector3Utils::fromString($settings->get(Map::KEY_SPAWN, "0:100:0")),
                floatval($settings->get(Map::KEY_PROTECTION_RADIUS, 10.0)),
                $settings->get(Map::KEY_IMAGE, ""),
                $settings->get(Map::KEY_IMAGE_TYPE, -1),
            ));
        }
        BuildFFA::getInstance()->getLogger()->info("Loaded ".count(self::$maps)." maps.");
    }

    public static function getMaps(): array{
        return self::$maps;
    }

    public static function addMap(Map $map): void {
        self::$maps[$map->getFolderName()] = $map;
    }

    public static function getMapByFolderName(string $map): ?Map {
        return self::$maps[$map] ?? null;
    }

    public static function getMapByWorld(Level $level): ?Map {
        return self::getMapByFolderName($level->getFolderName());
    }

    public static function getMapByName(string $name): ?Map {
        foreach(self::getMaps() as $map) {
            if($map->getName() === $name) return $map;
        }
        return null;
    }
}