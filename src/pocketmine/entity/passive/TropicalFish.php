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

namespace pocketmine\entity\passive;

use pocketmine\entity\WaterAnimal;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\network\mcpe\protocol\ActorEventPacket;
use function atan2;
use function mt_rand;
use function sqrt;
use const M_PI;

class TropicalFish extends WaterAnimal{
	public const NETWORK_ID = self::TROPICAL_FISH;

	public const SHAPE_A = 0;
	public const SHAPE_B = 1;

	public const PATTERN_1 = 0;
	public const PATTERN_2 = 1;
	public const PATTERN_3 = 2;
	public const PATTERN_4 = 3;
	public const PATTERN_5 = 4;
	public const PATTERN_6 = 5;

	public const COLOR_WHITE = 0;
	public const COLOR_ORANGE = 1;
	public const COLOR_MAGENTA = 2;
	public const COLOR_LIGHTBLUE = 3;
	public const COLOR_YELLOW = 4;
	public const COLOR_LIGHTGREEN = 5;
	public const COLOR_PINK = 6;
	public const COLOR_GRAY = 7;
	public const COLOR_SILVER = 8;
	public const COLOR_CYAN = 9;
	public const COLOR_PURPLE = 10;
	public const COLOR_BLUE = 11;
	public const COLOR_BROWN = 12;
	public const COLOR_GREEN = 13;
	public const COLOR_RED = 14;

	public $width = 0.4;
	public $height = 0.4;

	/** @var Vector3 */
	public $swimDirection = null;
	public $swimSpeed = 0.1;

	private $switchDirectionTicker = 0;

	public function initEntity() : void{
		$this->setMaxHealth(6);

		if($this->namedtag->hasTag("Variant", IntTag::class)){
			$this->propertyManager->setInt(self::DATA_VARIANT, $this->namedtag->getInt("Variant"));
		}else{
			$this->propertyManager->setInt(self::DATA_VARIANT, mt_rand(0, 1));
		}

		if($this->namedtag->hasTag("MarkVariant", IntTag::class)){
			$this->propertyManager->setInt(self::DATA_MARK_VARIANT, $this->namedtag->getInt("MarkVariant"));
		}else{
			$this->propertyManager->setInt(self::DATA_MARK_VARIANT, mt_rand(0, 5));
		}

		if($this->namedtag->hasTag("Color", ByteTag::class)){
			$this->propertyManager->setByte(self::DATA_COLOR, $this->namedtag->getByte("Color"));
		}else{
			$this->propertyManager->setByte(self::DATA_COLOR, mt_rand(0, 14));
		}

		if($this->namedtag->hasTag("Color2", ByteTag::class)){
			$this->propertyManager->setByte(self::DATA_COLOR_2, $this->namedtag->getByte("Color2"));
		}else{
			$this->propertyManager->setByte(self::DATA_COLOR_2, mt_rand(0, 14));
		}

		$this->setMovementSpeed(0.12);

		parent::initEntity();
	}

	public function getName() : string{
		return "Tropical Fish";
	}

	public function attack(EntityDamageEvent $source) : void{
		parent::attack($source);
		if($source->isCancelled()){
			return;
		}

		if($source instanceof EntityDamageByEntityEvent){
			$this->swimSpeed = mt_rand(150, 350) / 2000;
			$e = $source->getDamager();
			if($e !== null){
				$this->swimDirection = (new Vector3($this->x - $e->x, $this->y - $e->y, $this->z - $e->z))->normalize();
			}
		}
	}

	private function generateRandomDirection() : Vector3{
		return new Vector3(mt_rand(-1000, 1000) / 1000, mt_rand(-500, 500) / 1000, mt_rand(-1000, 1000) / 1000);
	}


	public function entityBaseTick(int $tickDiff = 1) : bool{
		if($this->closed){
			return false;
		}

		if(++$this->switchDirectionTicker === 100 or $this->isCollided){
			$this->switchDirectionTicker = 0;
			if(mt_rand(0, 100) < 50){
				$this->swimDirection = null;
			}
		}

		$hasUpdate = parent::entityBaseTick($tickDiff);

		if($this->isAlive()){

			if($this->y > 62 and $this->swimDirection !== null){
				$this->swimDirection->y = -0.5;
			}

			$inWater = $this->isUnderwater();
			if(!$inWater){
				$this->swimDirection = null;
			}elseif($this->swimDirection !== null){
				if($this->motion->lengthSquared() <= $this->swimDirection->lengthSquared()){
					$this->motion = $this->swimDirection->multiply($this->swimSpeed);
				}
			}else{
				$this->swimDirection = $this->generateRandomDirection();
				$this->swimSpeed = 0.05;
			}

			$f = sqrt(($this->motion->x ** 2) + ($this->motion->z ** 2));
			$this->yaw = (-atan2($this->motion->x, $this->motion->z) * 180 / M_PI);
			$this->pitch = (-atan2($f, $this->motion->y) * 180 / M_PI);
		}

		return $hasUpdate;
	}

	public function getDrops() : array{
		$drops = [
			ItemFactory::get(Item::CLOWNFISH, 0, 1),
		];
		if(mt_rand(1, 4) ===1){
			$drops[] = ItemFactory::get(Item::BONE, 1, mt_rand(1, 2));
		}
		return $drops;
	}

	protected function applyGravity() : void{
		if(!$this->isUnderwater()){
			parent::applyGravity();
		}
	}
}