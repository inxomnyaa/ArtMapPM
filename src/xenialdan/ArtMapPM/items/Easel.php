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

declare(strict_types=1);

namespace xenialdan\ArtMapPM\items;

use pocketmine\block\Block;
use pocketmine\item\Item;
use pocketmine\level\Location;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use xenialdan\ArtMapPM\EaselPart;

class Easel extends Item
{
    /**
     * Easel constructor.
     * @param int $meta
     * @throws \InvalidArgumentException
     */
    public function __construct($meta = 0)
    {
        parent::__construct(self::ARMOR_STAND, $meta, "ArtMap Easel");
    }

    public function canBeActivated()
    {
        return true;
    }

    /**
     * @param Player $player
     * @param Block $blockReplace
     * @param Block $blockClicked
     * @param int $face
     * @param Vector3 $clickVector
     * @return bool
     * @throws \BadMethodCallException
     * @throws \InvalidArgumentException
     * @throws \InvalidStateException
     */
    public function onActivate(Player $player, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector): bool
    {
        if ($face !== Vector3::SIDE_UP) {
            $player->sendMessage(TextFormat::RED . "Must be placed on ground!");
            return false;
        }
        foreach ($blockClicked->getLevel()->getNearbyEntities(new AxisAlignedBB(
            $blockClicked->getFloorX(),
            $blockClicked->getFloorY() + 1,
            $blockClicked->getFloorZ(),
            $blockClicked->getFloorX() + 1,
            $blockClicked->getFloorY() + 2,
            $blockClicked->getFloorZ() + 1
        )) as $nearbyEntity) {
            if ($nearbyEntity instanceof \xenialdan\ArtMapPM\entities\Easel) {
                $player->sendMessage(TextFormat::RED . "There already is an easel there!");
                return false;
            }
        }
        EaselPart::spawn(Location::fromObject($blockClicked->add(0, 2), $blockClicked->getLevel(), $player->getYaw()), EaselPart::getFacing($player));
        return true;
    }
}
