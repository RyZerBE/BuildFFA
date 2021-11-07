<?php


namespace BauboLP\LobbySystem\Provider;


use pocketmine\math\Vector3;
use pocketmine\utils\Config;

class ConfigProvider
{
    /** @var array */
    private $configs = [];

    const PATH = "/root/RyzerCloud/data/Lobby";

    public function __construct()
    {
        if(!is_dir(self::PATH)) {
            mkdir(self::PATH);
        }
        if(!file_exists(self::PATH."/LobbySpawns.yml")) {
            $c = new Config(self::PATH."/LobbySpawns.yml", Config::YAML);
            $c->save();
        }
        $this->configs['spawns'] = new Config(self::PATH."/LobbySpawns.yml", Config::YAML);
    }

    /**
     * @param string $game
     * @param \pocketmine\math\Vector3 $vector3
     */
    public function setGameSpawn(string $game, Vector3 $vector3)
    {
        $config = $this->getConfig('spawns');
        $config->set($game, "{$vector3->x}:{$vector3->y}:{$vector3->z}");
        $config->save();
    }

    /**
     * @param string $game
     * @return \pocketmine\math\Vector3|null
     */
    public function getGameSpawn(string $game): ?Vector3
    {
        $config = $this->getConfig('spawns');
        if(empty($config->get($game))) return null;

        $pos = explode(":", $config->get($game));
        return new Vector3((float)$pos[0], (float)$pos[1], (float)$pos[2]);
    }

    /**
     * @param string $game
     */
    public function removeGameSpawn(string $game)
    {
        $config = $this->getConfig('spawns');
        $config->remove($game);
        $config->save();
    }

    /**
     * @param string $index
     * @return \pocketmine\utils\Config
     */
    public function getConfig(string $index): \pocketmine\utils\Config
    {
        return $this->configs[$index];
    }

}