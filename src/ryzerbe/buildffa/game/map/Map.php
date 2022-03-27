<?php

declare(strict_types=1);

namespace ryzerbe\buildffa\game\map;

use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\level\Level;
use pocketmine\level\Location;
use pocketmine\math\Vector3;
use pocketmine\Server;
use ryzerbe\buildffa\player\BuildFFAPlayerManager;
use ryzerbe\buildffa\util\voting\Voteable;
use ryzerbe\buildffa\util\voting\VoteableTrait;

class Map implements Voteable {
    use VoteableTrait;

    public const KEY_NAME = "Name";
    public const KEY_SPAWN = "Spawn";
    public const KEY_PROTECTION_RADIUS = "ProtectionRadius";
    public const KEY_IMAGE = "Image";
    public const KEY_IMAGE_TYPE = "ImageType";

    public function __construct(
        protected string $name,
        protected string $folderName,
        protected Vector3 $spawn,
        protected float $spawnProtectionRadius,
        public string $image = "",
        public int $imageType = -1
    ){
        $this->spawnProtectionRadius = $this->spawnProtectionRadius ** 2;
    }

    public function getName(): string{
        return $this->name;
    }

    public function getFolderName(): string{
        return $this->folderName;
    }

    public function getSpawn(): Vector3{
        return $this->spawn;
    }

    public function getSpawnLocation(bool $nullSafe = false): ?Location {
        $level = Server::getInstance()->getLevelByName($this->getFolderName());
        if($level === null){
            if($nullSafe) {
                $position = Server::getInstance()->getDefaultLevel()->getSpawnLocation();
                return Location::fromObject($position, $position->getLevel(), 0, 0);
            }
            return null;
        }
        return Location::fromObject($this->getSpawn(), $level, 0, 0);
    }

    public function isInSpawnRadius(Vector3 $vector3): bool {
        return $this->spawn->distanceSquared($vector3) <= $this->spawnProtectionRadius;
    }

    public function enable(): void {
        $server = Server::getInstance();
        $server->loadLevel($this->getFolderName());
        $level = $server->getLevelByName($this->getFolderName());
        $level->stopTime();
        $level->setTime(1000);
        $level->setDifficulty(Level::DIFFICULTY_NORMAL);
        $level->setAutoSave(false);

        $location = $this->getSpawnLocation();
        foreach(BuildFFAPlayerManager::getPlayers() as $bFFAPlayer) {
            $player = $bFFAPlayer->getPlayer();
            $player->teleport($location);
            $player->setImmobile(false);
            $bFFAPlayer->setInSafeZone(false);
            $bFFAPlayer->enterSafeZone();
        }
    }

    public function disable(): void {
        $this->resetVotes();

        $server = Server::getInstance();
        foreach($server->getOnlinePlayers() as $player) {
            $player->getInventory()->clearAll();
            $player->getArmorInventory()->clearAll();
            $player->setHealth($player->getMaxHealth());
            $player->setImmobile();
            $player->addEffect(new EffectInstance(Effect::getEffect(Effect::BLINDNESS), 99999999, 1, false));
        }
        $server->unloadLevel($server->getLevelByName($this->getFolderName()));
    }
}