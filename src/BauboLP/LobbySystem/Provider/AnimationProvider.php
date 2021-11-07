<?php


namespace BauboLP\LobbySystem\Provider;


use BauboLP\LobbySystem\LobbySystem;
use pocketmine\math\Vector3;
use pocketmine\utils\TextFormat;
use slapper\entities\SlapperHuman;

class AnimationProvider
{

    public static $joinAnimation = [
        TextFormat::BLACK . '*      ' . TextFormat::RED . "*******      " . TextFormat::BLACK . "*",
        TextFormat::BLACK . '*      ' . TextFormat::DARK_RED . '*' . '   ' . TextFormat::RED . "*******      " . TextFormat::BLACK . "*",
        TextFormat::BLACK . '*      ' . TextFormat::DARK_RED . ' *' . '  ' . TextFormat::RED . "*******      " . TextFormat::BLACK . "*",
        TextFormat::BLACK . '*      ' . TextFormat::DARK_RED . '  *' . ' ' . TextFormat::RED . "*******      " . TextFormat::BLACK . "*",
        TextFormat::BLACK . '*      ' . TextFormat::DARK_RED . '   *' . TextFormat::RED . "*******      " . TextFormat::BLACK . "*",
        TextFormat::BLACK . '*      ' . TextFormat::DARK_RED . "*" . TextFormat::RED . "******      " . TextFormat::BLACK . "*",
        TextFormat::BLACK . '*      ' . TextFormat::WHITE . "R" . TextFormat::DARK_RED . "*" . TextFormat::RED . "*****      " . TextFormat::BLACK . "*",
        TextFormat::BLACK . '*      ' . TextFormat::WHITE . "Ry" . TextFormat::DARK_RED . "*" . TextFormat::RED . "****      " . TextFormat::BLACK . "*",
        TextFormat::BLACK . '*      ' . TextFormat::WHITE . "RyZ" . TextFormat::DARK_RED . "*" . TextFormat::RED . "***      " . TextFormat::BLACK . "*",
        TextFormat::BLACK . '*      ' . TextFormat::WHITE . "RyZe" . TextFormat::DARK_RED . "*" . TextFormat::RED . "**      " . TextFormat::BLACK . "*",
        TextFormat::BLACK . '*      ' . TextFormat::WHITE . "RyZer" . TextFormat::DARK_RED . "*" . TextFormat::RED . "*      " . TextFormat::BLACK . "*",
        TextFormat::BLACK . '*      ' . TextFormat::WHITE . "RyZerB" . TextFormat::DARK_RED . "*" . TextFormat::RED . "      " . TextFormat::BLACK . "*",
        TextFormat::BLACK . '*      ' . TextFormat::WHITE . "RyZerBE" . TextFormat::RED . "      " . TextFormat::BLACK . "*",
        TextFormat::BLACK . '*      ' . TextFormat::WHITE . "RyZerBE" . TextFormat::DARK_RED . '*' . "      " . TextFormat::BLACK . "*",
        TextFormat::BLACK . '*      ' . TextFormat::WHITE . "RyZerBE" . TextFormat::DARK_RED . ' *' . "     " . TextFormat::BLACK . "*",
        TextFormat::BLACK . '*      ' . TextFormat::WHITE . "RyZerBE" . TextFormat::DARK_RED . '  *' . "    " . TextFormat::BLACK . "*",
        TextFormat::BLACK . '*      ' . TextFormat::WHITE . "RyZerBE" . TextFormat::DARK_RED . '   *' . "   " . TextFormat::BLACK . "*",
        TextFormat::BLACK . '*      ' . TextFormat::WHITE . "RyZer" . TextFormat::AQUA . "BE" . TextFormat::RED . "      " . TextFormat::BLACK . "*"
    ];

    public static $afkAnimation = [
          TextFormat::WHITE.TextFormat::BOLD."RyZer".TextFormat::AQUA."BE           ",
          TextFormat::WHITE.TextFormat::BOLD."RyZer".TextFormat::AQUA."BE         ",
          TextFormat::WHITE.TextFormat::BOLD."RyZer".TextFormat::AQUA."BE       ",
          TextFormat::WHITE.TextFormat::BOLD."RyZer".TextFormat::AQUA."BE     ",
          TextFormat::WHITE.TextFormat::BOLD."RyZer".TextFormat::AQUA."BE   ",
          TextFormat::WHITE.TextFormat::BOLD."RyZer".TextFormat::AQUA."BE ",
          TextFormat::WHITE.TextFormat::BOLD."RyZer".TextFormat::AQUA."BE",
          TextFormat::WHITE.TextFormat::BOLD."  RyZer".TextFormat::AQUA."BE",
          TextFormat::WHITE.TextFormat::BOLD."    RyZer".TextFormat::AQUA."BE",
          TextFormat::WHITE.TextFormat::BOLD."      RyZer".TextFormat::AQUA."BE",
          TextFormat::WHITE.TextFormat::BOLD."        RyZer".TextFormat::AQUA."BE",
          TextFormat::WHITE.TextFormat::BOLD."          RyZer".TextFormat::AQUA."BE",
          TextFormat::WHITE.TextFormat::BOLD."            RyZer".TextFormat::AQUA."BE",
    ];
    /** @var bool  */
    public static $addonBlocker = false;

    /** @var array */
    public static $playerJoinAnimation = [];
    /** @var array  */
    public static $playerAFkAnimation = [];
    /** @var array */
    public static $delayedTP = [];
    /** @var array */
    public static $teleportAnimation = [];
    /** @var array */
    public static $djCooldown = [];
    /** @var array */
    public static $itemsToKill = [];
    /** @var array  */
    public static $blockReplace = [];
    /** @var array  */
    public static $delay = [];
    /** @var array  */
    public static $specialDelay = [];

    /**
     * @param string $player
     */
    public static function addPlayerToAnimation(string $player): void
    {
        self::$playerJoinAnimation[$player] = 0;
    }

    /**
     * @param string $player
     */
    public static function removePlayerFromAnimation(string $player): void
    {
        unset(self::$playerJoinAnimation[$player]);
    }
}