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

namespace pocketmine\entity\pathfinding;

class Path{
	/** @var PathPoint[] */
	protected $pathPoints = [];
	protected $count = 0;

	public function addPoint(PathPoint $point) : PathPoint{
		if ($point->index === -1){
			$this->pathPoints[] = $point;
			$point->index = $this->count++;

			$this->sortByDistance();
		}

		return $point;
	}

	public function clearPath() : void{
		$this->count = 0;
		$this->pathPoints = [];
	}

	public function dequeue() : ?PathPoint{
		$point = array_shift($this->pathPoints);

		if($point !== null){
			$point = clone $point;
			$point->index = -1;
		}

		return $point;
	}

	public function changeDistance(PathPoint $point, float $distance) : void{
		if(isset($this->pathPoints[$point->index])){
			$this->pathPoints[$point->index]->distanceToTarget = $distance;

			$this->sortByDistance();
		}
	}

	protected function sortByDistance() : void{
		uasort($this->pathPoints, function(PathPoint $point1, PathPoint $point2) : int{
			if($point1->distanceToTarget === $point2->distanceToTarget){
				return 0;
			}

			return $point1->distanceToTarget > $point2->distanceToTarget ? -1 : 1;
		});

		$this->pathPoints = array_values($this->pathPoints); // resort indexes
	}

	public function isEmpty() : bool{
		return $this->count === 0;
	}
}