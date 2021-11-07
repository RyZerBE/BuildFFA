<?php


namespace BauboLP\LobbySystem\Tasks;


use BauboLP\LobbySystem\LobbySystem;
use pocketmine\scheduler\Task;

class BossBarTask extends Task
{
    /** @var int  */
    private $i = 0;

    /**
     * @inheritDoc
     */
    public function onRun(int $currentTick)
    {
        if(empty(LobbySystem::$bossBarLines[$this->i]))
            $this->i = 0;

        LobbySystem::$bossBar->setTitle(LobbySystem::$bossBarLines[$this->i]);
        $this->i++;
        LobbySystem::$bossBar->setPercentage($this->i / count(LobbySystem::$bossBarLines));
    }
}