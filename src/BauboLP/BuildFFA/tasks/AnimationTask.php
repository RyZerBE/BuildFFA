<?php


namespace BauboLP\BuildFFA\tasks;


use BauboLP\BuildFFA\animation\Animation;
use BauboLP\BuildFFA\animation\AnimationProvider;
use pocketmine\scheduler\Task;

class AnimationTask extends Task
{

    /**
     * @inheritDoc
     */
    public function onRun(int $currentTick)
    {
        foreach (array_values(AnimationProvider::$activeAnimation) as $animation) {
            if($animation instanceof Animation)
                $animation->tick();
        }
    }
}