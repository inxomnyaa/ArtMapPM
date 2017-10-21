<?php

namespace xenialdan\ArtMapPM;

use pocketmine\entity\Entity;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\plugin\PluginBase;
use xenialdan\ArtMapPM\items\Easel;
use xenialdan\ArtMapPM\entities\Easel as EaselEntity;
use xenialdan\MapAPI\API;

class Loader extends PluginBase{

	public static $mapUtils;

	public function onEnable(){
		ItemFactory::registerItem(new Easel());
		Entity::registerEntity(EaselEntity::class);
		Item::initCreativeItems();

		self::$mapUtils = new API();

		$this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
		$this->getServer()->getCommandMap()->register(Commands::class, new Commands($this));
	}

	/**
	 * @return API
	 */
	public static function getMapUtils(){
		return self::$mapUtils;
	}
}