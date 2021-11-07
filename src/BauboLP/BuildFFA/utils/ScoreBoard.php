<?php


namespace BauboLP\BuildFFA\utils;


use pocketmine\network\mcpe\protocol\RemoveObjectivePacket;
use pocketmine\network\mcpe\protocol\SetDisplayObjectivePacket;
use pocketmine\network\mcpe\protocol\SetScorePacket;
use pocketmine\network\mcpe\protocol\types\ScorePacketEntry;
use pocketmine\Player;

class ScoreBoard
{

    /**
     * @param Player $player
     * @param int $score
     * @param string $msg
     * @param string $objName
     */
    public static function setScoreboardEntry(Player $player, int $score, string $msg, string $objName)
    {
        $entry = new ScorePacketEntry();
        $entry->objectiveName = $objName;
        $entry->type = 3;
        $entry->customName = str_repeat(" ", 1) . $msg . str_repeat("  ", 2);
        $entry->score = $score;
        $entry->scoreboardId = $score;

        $pk = new SetScorePacket();
        $pk->type = 0;
        $pk->entries[$score] = $entry;
        $player->sendDataPacket($pk);
    }

    /**
     * @param Player $player
     * @param string $title
     * @param string $objName
     * @param string $slot
     * @param int $order
     */
    public static function createScoreboard(Player $player, string $title, string $objName, string $slot = "sidebar", $order = 0)
    {
        $pk = new SetDisplayObjectivePacket();
        $pk->displaySlot = $slot;
        $pk->objectiveName = $objName;
        $pk->displayName = $title;
        $pk->criteriaName = "dummy";
        $pk->sortOrder = $order;
        $player->sendDataPacket($pk);
    }

    /**
     * @param Player $player
     * @param string $objName
     */
    public static function rmScoreboard(Player $player, string $objName)
    {
        $pk = new RemoveObjectivePacket();
        $pk->objectiveName = $objName;
        $player->sendDataPacket($pk);
    }
}