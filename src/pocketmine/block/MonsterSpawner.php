<?php

/*
 *               _ _
 *         /\   | | |
 *        /  \  | | |_ __ _ _   _
 *       / /\ \ | | __/ _` | | | |
 *      / ____ \| | || (_| | |_| |
 *     /_/    \_|_|\__\__,_|\__, |
 *                           __/ |
 *                          |___/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author TuranicTeam
 * @link https://github.com/TuranicTeam/Altay
 *
 */

declare(strict_types=1);

namespace pocketmine\block;

use pocketmine\item\SpawnEgg;
use pocketmine\item\TieredTool;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\tile\MobSpawner;
use function mt_rand;
use pocketmine\tile\TileFactory;

class MonsterSpawner extends Transparent{

	protected $id = self::MONSTER_SPAWNER;

	public function __construct(){

	}

	public function getHardness() : float{
		return 5;
	}

	public function getToolType() : int{
		return BlockToolType::TYPE_PICKAXE;
	}

	public function getToolHarvestLevel() : int{
		return TieredTool::TIER_WOODEN;
	}

	public function getName() : string{
		return "Monster Spawner";
	}

	public function getDropsForCompatibleTool(Item $item) : array{
		return [];
	}

	public function isAffectedBySilkTouch() : bool{
		return false;
	}

	protected function getXpDropAmount() : int{
		return mt_rand(15, 43);
	}

	public function onActivate(Item $item, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if($item instanceof SpawnEgg){
			/** @var MobSpawner $tile */
			$tile = TileFactory::create(MobSpawner::class, $this->level, $this);
			$tile->setEntityId($item->getDamage());
			$this->level->addTile($tile);

			if($player instanceof Player){
				$item->pop();
				$player->getInventory()->setItemInHand($item);
			}
		}
		return true;
	}
}
