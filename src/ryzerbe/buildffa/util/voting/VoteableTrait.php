<?php

declare(strict_types=1);

namespace ryzerbe\buildffa\util\voting;

use pocketmine\Player;
use function array_search;
use function count;
use function in_array;

trait VoteableTrait {
    protected array $votes = [];

    public function resetVotes(): void {
        $this->votes = [];
    }

    public function addVote(Player $player): void {
        $this->votes[] = $player->getName();
    }

    public function hasVoted(Player $player): bool {
        return in_array($player->getName(), $this->votes);
    }

    public function removeVote(Player $player): void {
        unset($this->votes[array_search($player->getName(), $this->votes)]);
    }

    public function getVotes(): int {
        return count($this->votes);
    }
}