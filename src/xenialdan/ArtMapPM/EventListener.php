<?php

namespace xenialdan\ArtMapPM;

use pocketmine\entity\Entity;
use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\item\ItemIds;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\InteractPacket;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\PlayerInputPacket;
use pocketmine\network\mcpe\protocol\SetActorLinkPacket;
use pocketmine\network\mcpe\protocol\types\EntityLink;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\Server;
use pocketmine\tile\ItemFrame as TileitemFrame;
use pocketmine\utils\Color;
use xenialdan\ArtMapPM\entities\Easel;
use xenialdan\MapAPI\item\Map;

class EventListener implements Listener
{
    public $owner;
    private static $LINKS = [];

    /**
     * EventListener constructor.
     * @param Plugin $plugin
     */
    public function __construct(Plugin $plugin)
    {
        $this->owner = $plugin;
    }

    /**
     * @param DataPacketReceiveEvent $event
     * @throws \BadMethodCallException
     * @throws \RuntimeException
     */
    public function onDataPacket(DataPacketReceiveEvent $event)
    {
        if ($event->getPacket() instanceof InventoryTransactionPacket) {
            $event->setCancelled($this->handleInventoryTransaction($event->getPacket(), $event->getPlayer()));
        }
        if ($event->getPacket() instanceof InteractPacket) {
            $event->setCancelled($this->handleInteract($event->getPacket(), $event->getPlayer()));
        }
        /** @var PlayerInputPacket $packet */
        if (($packet = $event->getPacket()) instanceof PlayerInputPacket) {
            if ($packet->motionX == 0 && $packet->motionY == 0) {
                $event->setCancelled(true);
            }
        }
    }

    /**
     * Don't expect much from this handler. Most of it is roughly hacked and duct-taped together.
     *
     * @param InteractPacket $packet
     * @param Player $player
     * @return bool
     * @throws \RuntimeException
     */
    public function handleInteract(InteractPacket $packet, Player $player): bool
    {
        switch ($packet->action) {
            case InteractPacket::ACTION_LEAVE_VEHICLE:
                if ($this->isRiding($player)) {
                    $this->setEntityLink(Server::getInstance()->findEntity(self::getLINK($player)->fromEntityUniqueId, $player->getLevel()), $player, 0);
                    return true;
                }
                break;
        }

        return false;
    }

    /**
     * Don't expect much from this handler. Most of it is roughly hacked and duct-taped together.
     *
     * @param InventoryTransactionPacket $packet
     * @param Player $player
     * @return bool
     * @throws \RuntimeException
     * @throws \RuntimeException
     */
    public function handleInventoryTransaction(InventoryTransactionPacket $packet, Player $player): bool
    {
        switch ($packet->transactionType) {
            case InventoryTransactionPacket::TYPE_USE_ITEM_ON_ENTITY:
                {
                    $type = $packet->trData->actionType;
                    switch ($type) {
                        case InventoryTransactionPacket::USE_ITEM_ON_ENTITY_ACTION_INTERACT:
                            {
                                $target = $player->getLevel()->getEntity($packet->trData->entityRuntimeId);
                                if ($target === null) {
                                    return false;
                                }
                                if (!$this->isRiding($player)) {
                                    $this->setEntityLink($target, $player);
                                    return true;
                                }
                            }
                    }
                    break;
                }
            case InventoryTransactionPacket::TYPE_USE_ITEM:
                {
                    $type = $packet->trData->actionType;
                    switch ($type) {
                        case InventoryTransactionPacket::USE_ITEM_ACTION_CLICK_BLOCK:
                            {
                                //TODO dye colors
                                if (in_array($player->getInventory()->getItemInHand()->getId(), [ItemIds::AIR, ItemIds::DYE]) && $this->isRiding($player)) {
                                    /** @var Vector3 $clickpos */
                                    $clickpos = $packet->trData->clickPos;
                                    /** @var TileItemFrame $tile */
                                    $tile = $player->getLevel()->getTile(new Vector3($packet->trData->x, $packet->trData->y, $packet->trData->z));
                                    if ($tile instanceof TileItemFrame) {
                                        $item = $tile->getItem();
                                        if ($item instanceof Map) {
                                            /** @var Map $map */
                                            $map = Loader::getMapUtils()->getCachedMap($item->getMapId());
                                            $height = $item->getHeight();
                                            $width = $item->getWidth();
                                            $y = floor($height - $height * $clickpos->y);
                                            /** @var Easel $easel */
                                            $easel = Server::getInstance()->findEntity(self::getLINK($player)->fromEntityUniqueId, $player->getLevel());
                                            switch ($easel->getDirection()) {
                                                case Vector3::SIDE_NORTH - 2:
                                                    {
                                                        $x = floor($width - $width * $clickpos->z);
                                                        break;
                                                    }
                                                case Vector3::SIDE_EAST - 2:
                                                    {
                                                        $x = floor($width - $width * $clickpos->x);
                                                        break;
                                                    }
                                                case Vector3::SIDE_SOUTH - 2:
                                                    {
                                                        $x = floor($width * $clickpos->x);
                                                        break;
                                                    }
                                                case Vector3::SIDE_WEST - 2:
                                                    {
                                                        $x = floor($width * $clickpos->z);
                                                        break;
                                                    }
                                                default:
                                                    $x = 0;
                                            }
                                            $map->setColorAt(new Color(mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255)), $x, $y);
                                        }
                                        return true;
                                    }
                                }
                                break;
                            }
                    }
                    break;
                }

        }

        return false;
    }

    private function isRiding(Player $player)
    {
        return ($player->getDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_RIDING));
    }

    public function setEntityLink(Entity $main, Entity $riding, int $type = 1)
    {
        if ($main->isAlive() and $riding->isAlive() and $main->getLevel() === $riding->getLevel()) {
            /*$pk = new SetActorLinkPacket();
            $pk->link = new EntityLink();
            $pk->link->fromEntityUniqueId = $main->getId();
            $pk->link->toEntityUniqueId = $riding->getId();
            $pk->link->type = $type;
            $pk->link->byte2 = 0;
            $main->getLevel()->getServer()->broadcastPacket($main->getLevel()->getPlayers(), $pk);*/
            $main->getDataPropertyManager()->setLong(Entity::DATA_OWNER_EID, $riding->getId());
            $riding->setDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_RIDING, $type !== 0);
            switch ($type) {
                case 0:
                    {//unlink
                        $pk = new SetActorLinkPacket();
                        $pk->link = self::getLINK($riding);
                        $pk->link->type = $type;
                        $pk->link->immediate = true;
                        $main->getLevel()->getServer()->broadcastPacket($main->getLevel()->getPlayers(), $pk);
                        break;
                    }
                case 1:
                    {//rider?
                        $pk = new SetActorLinkPacket();
                        $pk->link = new EntityLink();
                        $pk->link->fromEntityUniqueId = $main->getId();
                        $pk->link->toEntityUniqueId = $riding->getId();
                        $pk->link->type = $type;
                        $pk->link->immediate = true;
                        self::setLINK($pk->link);
                        $main->getLevel()->getServer()->broadcastPacket($main->getLevel()->getPlayers(), $pk);
                        $riding->getDataPropertyManager()->setVector3(Entity::DATA_RIDER_SEAT_POSITION, new Vector3(0, 1.5, 1.5));//TODO
                        $main->setDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_WASD_CONTROLLED, false);//TODO
                        break;
                    }
                case 2:
                    {//companion?
                        /*$pk = new SetActorLinkPacket();
                        $pk->link = new EntityLink();
                        $pk->link->fromEntityUniqueId = $main->getId();
                        $pk->link->toEntityUniqueId = $riding->getId();
                        $pk->link->type = $type;
                        $pk->link->byte2 = 1;
                        $main->getLevel()->getServer()->broadcastPacket($main->getLevel()->getPlayers(), $pk);*/
                        $riding->getDataPropertyManager()->setInt(Entity::DATA_RIDER_SEAT_POSITION, 1);
                        break;
                    }
            }
        }
    }

    public static function getLINK(Entity $entity): ?EntityLink
    {
        return self::$LINKS[$entity->getId()] ?? null;
    }

    public static function setLINK(EntityLink $link)
    {
        self::$LINKS[$link->toEntityUniqueId] = $link;
    }
}