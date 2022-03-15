<?php

declare(strict_types=1);

namespace ryzerbe\buildffa\util\voting;

use pocketmine\Player;

interface Voteable {
    public function resetVotes(): void;

    public function addVote(Player $player): void;

    public function hasVoted(Player $player): bool;

    public function removeVote(Player $player): void;

    public function getVotes(): int;
}