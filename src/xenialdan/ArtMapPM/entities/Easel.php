<?php

namespace xenialdan\ArtMapPM\entities;

use pocketmine\entity\Entity;
use pocketmine\entity\Rideable;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;

class Easel extends Entity implements Rideable{
	const NETWORK_ID = self::ARMOR_STAND;

	public function getName(): string{
		return "Easel";
	}

	public function getMaxHealth(): int{
		return 1;
	}

	public function getDrops(): array{
		return [
			ItemFactory::get(ItemIds::ARMOR_STAND)->setCustomName("Easel")
		];
	}

	public function setBasePlate($value = true){
		$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_SHOWBASE, $value);//TODO fix/update
	}

	public function setCustomNameVisible($value = true){
		$this->setNameTagVisible($value);
		$this->setNameTagAlwaysVisible($value);
	}

	public function setCustomName($value = ""){
		$this->setNameTag($value);
	}

	public function setGravity($value = true){
		$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_AFFECTED_BY_GRAVITY, $value);
	}

	public function setArms($value = true){
		//TODO $this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_ARMS, $value);//TODO fix/update
	}
}
