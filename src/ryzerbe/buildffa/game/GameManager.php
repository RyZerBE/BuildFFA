<?php

declare(strict_types=1);

namespace ryzerbe\buildffa\game;

use pocketmine\entity\utils\Bossbar;
use pocketmine\Server;
use ryzerbe\buildffa\BuildFFA;
use ryzerbe\buildffa\game\entry\Entry;
use ryzerbe\buildffa\game\kit\Kit;
use ryzerbe\buildffa\game\kit\KitManager;
use ryzerbe\buildffa\game\map\Map;
use ryzerbe\buildffa\game\map\MapManager;
use ryzerbe\buildffa\game\safezone\SafeZoneManager;
use ryzerbe\buildffa\game\scheduler\GameUpdateTask;
use ryzerbe\buildffa\game\setup\SetupManager;
use function array_flip;
use function array_shift;
use function arsort;
use function count;
use function shuffle;
use function var_dump;

class GameManager {
    public const DEFAULT_MAP_CHANGE_DELAY = (20 * 60) * 5;

    public static int $entryId = -1;

    protected static ?Map $map = null;
    protected static ?Kit $kit = null;

    /** @var Entry[][]  */
    protected static array $entries = [];

    protected static Bossbar $bossbar;

    /** In ticks  */
    public static int $mapChangeTimer = self::DEFAULT_MAP_CHANGE_DELAY;

    public static function init(): void {
        MapManager::init();
        SetupManager::init();
        KitManager::init();
        SafeZoneManager::init();

        BuildFFA::getInstance()->getScheduler()->scheduleRepeatingTask(new GameUpdateTask(), 1);

        $maps = MapManager::getMaps();
        if(count($maps) > 0) {
            shuffle($maps);
            self::setMap(array_shift($maps));
        }

        $kits = KitManager::getKits();
        if(count($kits) > 0) {
            shuffle($kits);
            self::setKit(array_shift($kits));
        }

        self::$bossbar = new Bossbar("", 1.0);
    }

    public static function getMap(): ?Map{
        return self::$map;
    }

    public static function setMap(?Map $map): void{
        self::$map?->disable();
        self::$map = $map;
        self::$map?->enable();
    }

    public static function getTopMap(): Map {
        $maps = [];
        foreach(MapManager::getMaps() as $map) {
            $maps[$map->getName()] = $map->getVotes();
            $map->resetVotes();
        }
        arsort($maps);
        $maps = array_flip($maps);
        $map = MapManager::getMapByName(($array_shift = array_shift($maps)) ?? "");
        if($map === null) {
            var_dump($array_shift);
            $maps = MapManager::getMaps();
            $map = array_shift($maps);
        }
        return $map;
    }

    public static function getKit(): ?Kit{
        return self::$kit;
    }

    public static function setKit(?Kit $kit): void{
        self::$kit?->disable();
        self::$kit = $kit;
        self::$kit?->enable();
    }

    public static function getTopKit(): Kit {
        $kits = [];
        foreach(KitManager::getKits() as $kit) {
            $kits[$kit->getName()] = $kit->getVotes();
            $kit->resetVotes();
        }
        arsort($kits);
        $kits = array_flip($kits);
        $kit = KitManager::getKit(($array_shift = array_shift($kits)) ?? "");
        if($kit === null) {
            var_dump($array_shift);
            $kits = KitManager::getKits();
            $kit = array_shift($kits);
        }
        return $kit;
    }

    public static function addEntry(Entry $entry): void {
        self::$entries[Server::getInstance()->getTick() + $entry->getDelay()][] = $entry;
    }

    public static function getEntries(int $tick): array{
        return self::$entries[$tick] ?? [];
    }

    public static function removeEntries(int $tick): void {
        unset(self::$entries[$tick]);
    }

    public static function removeEntryById(int $id): void {
        foreach(self::$entries as $tick => $entries) {
            foreach($entries as $key => $entry) {
                if($entry->getId() === $id) {
                    unset(self::$entries[$tick][$key]);
                    return;
                }
            }
        }
    }

    public static function resetEntries(): void {
        self::$entries = [];
    }

    public static function getBossbar(): Bossbar{
        return self::$bossbar;
    }
}