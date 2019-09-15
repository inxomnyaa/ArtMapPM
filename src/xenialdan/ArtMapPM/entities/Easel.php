<?php

namespace xenialdan\ArtMapPM\entities;

use pocketmine\block\BlockIds;
use pocketmine\entity\Living;
use pocketmine\entity\Rideable;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\tile\ItemFrame;

class Easel extends Living implements Rideable
{
    const NETWORK_ID = self::ARMOR_STAND;
    public $width = 1.0;
    public $height = 1.8;

    protected $gravity = 0;

    public function getName(): string
    {
        return "Easel";
    }

    public function getMaxHealth(): int
    {
        return 1;
    }

    protected function onDeath(): void
    {
        $this->getLevel()->useBreakOn($this->floor()->up());
        foreach ($this->getLevel()->getBlock($this->floor()->up())->getHorizontalSides() as $block) {
            if ($block->getId() === BlockIds::ITEM_FRAME_BLOCK) {
                $tile = $block->getLevel()->getTile($block);
                if ($tile instanceof ItemFrame) {
                    if ($tile->hasItem() && $tile->getItem()->getId() === ItemIds::FILLED_MAP) {
                        $this->getLevel()->useBreakOn($block);
                        break;
                    }
                }
            }
        }
        parent::onDeath();
    }

    public function getDrops(): array
    {
        return [
            ItemFactory::get(ItemIds::ARMOR_STAND)->setCustomName("Easel")
        ];
    }

    public function setBasePlate(bool $value = true): void
    {
        $this->setGenericFlag(self::DATA_FLAG_SHOWBASE, $value);
    }

    public function setCustomNameVisible(bool $value = true): void
    {
        $this->setNameTagVisible($value);
        $this->setNameTagAlwaysVisible($value);
    }

    public function setCustomName(string $value = ""): void
    {
        $this->setNameTag($value);
    }

    public function setGravity(bool $value = false): void
    {
        $this->setGenericFlag(self::DATA_FLAG_AFFECTED_BY_GRAVITY, $value);
    }

    public function setArms(bool $value = true): void
    {
        //TODO $this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_ARMS, $value);//TODO fix/update
    }
}
