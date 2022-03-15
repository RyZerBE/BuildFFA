<?php

declare(strict_types=1);

namespace ryzerbe\buildffa\player;

use pocketmine\Player;
use ryzerbe\core\player\PMMPPlayer;

class BuildFFAPlayerManager {
    /** @var BuildFFAPlayer[]  */
    protected static array $players = [];

    public static function getPlayers(): array{
        return self::$players;
    }

    /**
     * @param PMMPPlayer $player
     */
    public static function addPlayer(Player $player): BuildFFAPlayer {
        return (self::$players[$player->getName()] = new BuildFFAPlayer($player));
    }

    public static function removePlayer(Player $player): void {
        self::get($player)?->unload();
        unset(self::$players[$player->getName()]);
    }

    public static function get(Player|string $player): ?BuildFFAPlayer {
        return self::$players[($player instanceof Player ? $player->getName() : $player)] ?? null;
    }
}