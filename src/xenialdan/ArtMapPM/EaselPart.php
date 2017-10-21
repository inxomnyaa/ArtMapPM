<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
*/

namespace xenialdan\ArtMapPM;


use pocketmine\block\Block;
use pocketmine\entity\Entity;
use pocketmine\level\Level;
use pocketmine\level\Location;
use pocketmine\network\mcpe\protocol\ClientboundMapItemDataPacket;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\tile\ItemFrame;
use pocketmine\tile\ItemFrame as TileItemFrame;
use pocketmine\tile\Sign;
use pocketmine\tile\Sign as TileSign;
use pocketmine\tile\Tile;
use xenialdan\ArtMapPM\entities\Easel;
use xenialdan\MapAPI\API;
use xenialdan\MapAPI\item\Map;

class EaselPart{

	/**
	 * Represents a part of an easel object
	 */

	public static $ARBITRARY_SIGN_ID = "*{=}*";
	private static $EASEL_ID = "Easel";

	private static $modifier = 0.65;
	private static $heightOffset = -1.4;

	public static function spawn(Location $easelLocation, int $facing){
		$sign = Block::get(Block::WALL_SIGN, $dump = self::getSignFacing($facing));

		$easelLocation->getLevel()->setBlock($easelLocation->getSide($facing + 2), $sign, true, false);
		/** @var Sign $tile */
		$tile = Tile::createTile(Tile::SIGN, $easelLocation->getLevel(), TileSign::createNBT($easelLocation->getSide($facing + 2)->floor()));
		$tile->scheduleUpdate();
		$tile->setLine(3, self::$ARBITRARY_SIGN_ID);
		/** @var Block $frame */
		$frame = Block::get(Block::ITEM_FRAME_BLOCK, self::getFrameFacing($facing));
		$easelLocation->getLevel()->setBlock($easelLocation->floor(), $frame, true, false);

		/** @var ItemFrame $tile */
		$tile = Tile::createTile(Tile::ITEM_FRAME, $easelLocation->getLevel(), TileItemFrame::createNBT($easelLocation->floor()));
		/* Fake map */
		$map = API::importFromPNG('empty', 32);
		if ($map instanceof Map){
			$tile->setItem($map);
		}
		$tile->scheduleUpdate();
		$map->update(ClientboundMapItemDataPacket::BITFLAG_TEXTURE_UPDATE);

		$partPos = self::getPartPos($easelLocation, $facing);
		/** @var Easel $stand */
		$stand = new Easel($easelLocation->getLevel(), Entity::createBaseNBT($partPos, null, $partPos->getYaw()));
		if ($stand instanceof Easel){
			$easelLocation->getLevel()->addEntity($stand);
			$stand->spawnToAll();
			$stand->setBasePlate(false);
			$stand->setCustomNameVisible(true);
			$stand->setCustomName(self::$EASEL_ID);
			$stand->setGravity(false);
			$stand->setArms(false);
			$stand->setHealth(1);
			$stand->setDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_TAMED);
			$stand->setDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_SADDLED);
		}
	}

	public static function getFacing(Player $player){
		$faces = [
			0 => 3,
			1 => 1,
			2 => 2,
			3 => 0
		];
		Server::getInstance()->broadcastMessage(__LINE__ . "|" . $faces[$player->getDirection()]);
		return $faces[$player->getDirection()];
	}

	private static function getSignFacing(int $facing){
		$faces = [
			0 => 2,
			1 => 3,
			2 => 4,
			3 => 5
		];
		return $faces[$facing];
	}

	private static function getFrameFacing(int $facing){
		$faces = [
			0 => 2,
			1 => 3,
			2 => 5,
			3 => 1
		];
		return $faces[$facing];
	}

	public static function getYawOffset(int $face): int{
		switch ($face){
			case 0:
				return 0;
				break;
			case 3:
				return 90;
				break;
			case 1:
				return 180;
				break;
			case 2:
				return 270;
				break;
		}

		return 0;
	}

	private static function getOffset(Level $world, int $facing): Location{
		$x = 0;
		$z = 0;

		switch ($facing){
			case 0:
				$z = -self::$modifier;
				break;
			case 1:
				$z = self::$modifier;
				break;
			case 2:
				$x = -self::$modifier;
				break;
			case 3:
				$x = self::$modifier;
				break;
		}

		$x += 0.5;
		$z += 0.5;
		return new Location($x, self::$heightOffset, $z, self::getYawOffset($facing), 0, $world);
	}

	private static function getPartPos(Location $easelLocation, int $facing): Location{
		$offset = self::getOffset($easelLocation->getLevel(), $facing);
		$temp = $easelLocation->add($offset);
		$partLocation = new Location($temp->getX(), $temp->getY(), $temp->getZ(), $offset->getYaw(), 0, $offset->getLevel());
		return $partLocation;
	}
}