<?php

declare(strict_types=1);

namespace ryzerbe\buildffa\game\kit;

use pocketmine\item\Durable;
use pocketmine\item\Item;
use ryzerbe\buildffa\util\voting\Voteable;
use ryzerbe\buildffa\util\voting\VoteableTrait;

class Kit implements Voteable {
    use VoteableTrait;

    public const TAG_IDENTIFIER = "item:identifier";
    public const TAG_INFINITE  = "item:infinite";
    public const TAG_DESTROY_DELAY = "item:destroy_delay";
    public const TAG_RESTORE_COOLDOWN = "item:restore_cooldown";
    public const TAG_ORIGINAL_ITEM = "item:original_item";

    /**
     * @param Item[] $items
     */
    public function __construct(
        protected string $name,
        protected array $items,
        public string $image = "",
        public int $imageType = -1
    ){
        foreach($this->items as $item) {
            if(!$item instanceof Durable) continue;
            $item->setUnbreakable();
        }
    }

    public function getName(): string{
        return $this->name;
    }

    public function getItems(): array{
        return $this->items;
    }

    public function enable(): void {
    }

    public function disable(): void {
        $this->resetVotes();
    }
}