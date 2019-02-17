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

use pocketmine\item\Item;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\tile\Beacon as TileBeacon;

class Beacon extends Transparent{

	protected $id = self::BEACON;

	public function __construct(){

	}

	public function getName() : string{
		return "Beacon";
	}

	public function getLightLevel() : int{
		return 15;
	}

	public function getHardness() : float{
		return 3;
	}

	public function getBreakTime(Item $item) : float{
		return 4.5;
	}

	protected function getTileClass() : ?string{
		return TileBeacon::class;
	}

	public function onActivate(Item $item, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if($player instanceof Player){
			$tile = $this->level->getTile($this);
			if($tile instanceof TileBeacon){
				$top = $this->getSide(Facing::UP);
				if($top->isTransparent() !== true){
					return true;
				}

				$player->addWindow($tile->getInventory());
			}
		}

		return true;
	}
}