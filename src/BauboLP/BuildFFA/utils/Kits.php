<?php


namespace BauboLP\BuildFFA\utils;


abstract class Kits
{

    const RUSHER = 0;
    const SPAMMER = 1;
    const BASEDEF = 2;
    const TNT = 3;
    const SNOWBALL = 4;

    /**
     * @param int $kit
     * @return string
     */
    public static function convertKitIndexToString(int $kit): string
    {
        if($kit == self::RUSHER) {
            return "Rusher";
        }elseif($kit == self::SPAMMER) {
            return "Spammer";
        }elseif($kit == self::BASEDEF) {
            return "BaseDef";
        }elseif($kit == self::TNT) {
            return "TNT";
        }elseif($kit == self::SNOWBALL) {
            return "Snowball";
        }

        return "UNKNOWN";
    }
}