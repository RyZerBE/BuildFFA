<?php


namespace BauboLP\LobbySystem\Utils;


use pocketmine\level\particle\FlameParticle;
use pocketmine\level\particle\HeartParticle;
use pocketmine\level\particle\LavaDripParticle;
use pocketmine\level\particle\LavaParticle;
use pocketmine\level\Position;
use pocketmine\math\Vector3;

class Wing
{
    const FLAME_PARTICLE = 1;
    const HEART_PARTICLE = 2;
    const LAVA_DRIP_PARTICLE = 3;

    /**@var int*/
    private $scale = 0.3;
    /** @var array */
    private $shape = [];
    /** @var array */
    private $vector3 = [];

    public function __construct(array $shape){
        $this->shape = $shape;
        $l1 = count($this->shape);
        for($y = 0; $y < $l1; $y++) {
            $l2 = count($this->shape[$y]);
            for($x = 0; $x < $l2; $x++) {
                $flag = $shape[$y][$x];
                if($flag == 0) continue;
                $kx = $x - (int) ($l2 / 2);
                $ky = ($y - (int) ($l1 / 2)) * (-1);
                $this->vector3[] = [new Vector3($kx, $this->scale * $ky + 1.7), $flag];
            }
        }
    }

    /**
     * @param Position $pos
     * @param float $angle
     */
    public function draw(Position $pos, float $angle) :void{
        $level = $pos->getLevel();
        $sin = sin(deg2rad($angle));
        $cos = cos(deg2rad($angle));
        foreach($this->vector3 as $data){
            $r = $this->scale * $data[0]->x;
            $px = $r * $cos;
            $pz = $r * $sin;
            $level->addParticle($this->parseCharacter($pos->add($px, $data[0]->y, $pz), $data[1]));
        }
    }

    /**
     * @param \pocketmine\math\Vector3 $vector3
     * @param null|string $character
     * @return \pocketmine\level\particle\Particle|null
     */
    public function parseCharacter(Vector3 $vector3, $character)
    {
        switch ($character) {
            case self::FLAME_PARTICLE:
                return new FlameParticle($vector3);
                break;
            case self::HEART_PARTICLE:
                return new HeartParticle($vector3);
                break;
            case self::LAVA_DRIP_PARTICLE:
                return new LavaParticle($vector3);
               break;
        }

        return null;
    }
}