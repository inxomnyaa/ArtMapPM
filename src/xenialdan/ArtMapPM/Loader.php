<?php

namespace xenialdan\ArtMapPM;

use pocketmine\entity\Entity;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\plugin\PluginBase;
use pocketmine\plugin\PluginException;
use xenialdan\ArtMapPM\entities\Easel as EaselEntity;
use xenialdan\ArtMapPM\items\Easel;
use xenialdan\MapAPI\API;

class Loader extends PluginBase
{

    public static $mapUtils;

    public function onEnable()
    {
        /*$img = imagecreate(32, 32);
        $i = imagecolorallocate($img, 255, 255, 255);
        imagefilledrectangle($img, 0, 0, 32, 32, $i);
        imagepng($img, \xenialdan\MapAPI\Loader::$path['images'] . '/empty.png');
        imagedestroy($img);*/
        try {
            ItemFactory::registerItem(new Easel(), true);
        } catch (\RuntimeException $exception) {
            $this->getLogger()->logException($exception);
        } catch (\InvalidArgumentException $exception) {
            $this->getLogger()->logException($exception);
        }
        Entity::registerEntity(EaselEntity::class);
        Item::initCreativeItems();

        self::$mapUtils = new API();

        try {
            $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
        } catch (PluginException $exception) {
            $this->getLogger()->logException($exception);
        }
    }

    /**
     * @return API
     */
    public static function getMapUtils()
    {
        return self::$mapUtils;
    }
}