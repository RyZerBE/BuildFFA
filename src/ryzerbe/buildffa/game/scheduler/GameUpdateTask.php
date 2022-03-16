<?php

declare(strict_types=1);

namespace ryzerbe\buildffa\game\scheduler;

use pocketmine\network\mcpe\protocol\BossEventPacket;
use pocketmine\Player;
use pocketmine\scheduler\Task;
use ryzerbe\buildffa\game\GameManager;
use ryzerbe\buildffa\game\kit\item\SpecialItem;
use ryzerbe\buildffa\player\BuildFFAPlayerManager;
use ryzerbe\core\language\LanguageProvider;
use ryzerbe\core\util\customitem\CustomItemManager;
use ryzerbe\core\util\time\TimeFormat;
use function count;
use function floor;

class GameUpdateTask extends Task {
    public function onRun(int $currentTick): void{
        foreach(GameManager::getEntries($currentTick) as $entry) {
            $entry->handle();
        }
        GameManager::removeEntries($currentTick);

        if(count(BuildFFAPlayerManager::getPlayers()) > 0) {
            if(--GameManager::$mapChangeTimer <= 0) {
                GameManager::resetEntries();
                GameManager::setKit(GameManager::getTopKit());
                GameManager::setMap(GameManager::getTopMap());
                GameManager::$mapChangeTimer = GameManager::DEFAULT_MAP_AND_KIT_CHANGE_DELAY;
            }
        }

        if(GameManager::$mapChangeTimer % 20 === 0) {
            $bossbar = GameManager::getBossbar();
            $bossbar->setHealthPercent(GameManager::$mapChangeTimer / GameManager::DEFAULT_MAP_AND_KIT_CHANGE_DELAY, false);

            $timeFormat = new TimeFormat(0, 0, 0, 0, (int)floor(GameManager::$mapChangeTimer / 1200), (int)floor((GameManager::$mapChangeTimer / 20) % 60));
            $time = (
                ($timeFormat->getMinutes() <= 9 ? "0" : "").$timeFormat->getMinutes().":".
                ($timeFormat->getSeconds() <= 9 ? "0" : "").$timeFormat->getSeconds()
            );

            foreach(BuildFFAPlayerManager::getPlayers() as $bFFAPlayer) {
                $player = $bFFAPlayer->getPlayer();
                $bossbar->setTitle(LanguageProvider::getMessageContainer("buildffa-bossbar-next-map-and-kit-change", $player, [
                    "#time" => $time
                ]), false);
                $this->sendBossEventPacket($player, BossEventPacket::TYPE_HEALTH_PERCENT);
                $this->sendBossEventPacket($player, BossEventPacket::TYPE_TITLE);
                $this->sendBossEventPacket($player, BossEventPacket::TYPE_TEXTURE);

                foreach($player->getInventory()->getContents() as $slot => $item) {
                    $customItem = CustomItemManager::getInstance()->getCustomItemByItem($item);
                    if($customItem instanceof SpecialItem) {
                        $customItem->onUpdate($player, $item, $slot);
                    }
                }
            }
        }

        if(GameManager::$mapChangeTimer % 5 === 0) {
            foreach(BuildFFAPlayerManager::getPlayers() as $bFFAPlayer) {
                if($bFFAPlayer->needsScoreboardUpdate()) $bFFAPlayer->updateScoreboard(true);
            }
        }
    }

    /**
     * HACK: This is only required, because we can´t set the color in Altay´s bossbar
     */
    protected function sendBossEventPacket(Player $player, int $eventType) : void{
        $bossbar = GameManager::getBossbar();
        $pk = new BossEventPacket();
        $pk->bossEid = $bossbar->getEntityId();
        $pk->eventType = $eventType;

        switch($eventType){
            case BossEventPacket::TYPE_SHOW:
                $pk->title = $bossbar->getTitle();
                $pk->healthPercent = $bossbar->getHealthPercent();
                $pk->color = 3;
                $pk->overlay = 0;
                $pk->unknownShort = 0;
                break;
            case BossEventPacket::TYPE_REGISTER_PLAYER:
            case BossEventPacket::TYPE_UNREGISTER_PLAYER:
                $pk->playerEid = $player->getId();
                break;
            case BossEventPacket::TYPE_TITLE:
                $pk->title = $bossbar->getTitle();
                break;
            case BossEventPacket::TYPE_HEALTH_PERCENT:
                $pk->healthPercent = $bossbar->getHealthPercent();
                break;
            case BossEventPacket::TYPE_TEXTURE:
                $pk->color = 3;
                $pk->overlay = 0;
                break;
        }
        $player->sendDataPacket($pk);
    }
}