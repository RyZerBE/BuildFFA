<?php


namespace BauboLP\BuildFFA\animation;


class AnimationProvider
{
    /** @var array  */
    public static $activeAnimation = [];

    public static function addActiveAnimation(Animation $animation)
    {
        self::$activeAnimation[$animation->getAnimationId()] = $animation;
    }
}