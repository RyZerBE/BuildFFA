<?php


namespace BauboLP\BuildFFA\animation;


use function uniqid;

abstract class Animation
{
    /** @var int  */
    private int $ticks = 0;

    /** @var string */
    private string $id;

    public function __construct()
    {
        $this->id = uniqid();
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
     * @return string
     */
    public function getAnimationId(): string
    {
        return $this->id;
    }


    public function stop(): void
    {
        unset(AnimationProvider::$activeAnimation[$this->getAnimationId()]);
    }
}