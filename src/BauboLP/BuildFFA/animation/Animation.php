<?php


namespace BauboLP\BuildFFA\animation;


abstract class Animation
{
    /** @var int  */
    private $ticks = 0;

    /** @var int */
    private $id;

    public function __construct()
    {
        $this->id = rand(1, 20000);
    }

    public function tick() {
        $this->ticks++;
    }

    /**
     * @return int
     */
    public function getCurrentTick(): int
    {
        return $this->ticks;
    }

    /**
     * @return int
     */
    public function getAnimationId(): int
    {
        return $this->id;
    }


    public function stop(): void
    {
        unset(AnimationProvider::$activeAnimation[$this->getAnimationId()]);
    }
}