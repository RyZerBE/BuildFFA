<?php

declare(strict_types=1);

namespace ryzerbe\buildffa\game\entry;

use ryzerbe\buildffa\game\GameManager;

abstract class Entry {
    protected int $id;

    public function __construct(){
        $this->id = GameManager::$entryId--;
    }

    abstract public function getDelay(): int;
    abstract public function handle(): void;

    public function getId(): int{
        return $this->id;
    }
}