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

use pocketmine\entity\Entity;
use pocketmine\entity\pathfinding\processor\NodeProcessor;
use pocketmine\level\Level;
use pocketmine\math\Vector3;

class PathFinder{
	/** @var Path */
	protected $path;
	protected $pathOptions = [];
	/** @var NodeProcessor */
	protected $nodeProcessor;

	public function __construct(NodeProcessor $nodeProcessor){
		$this->path = new Path();
		$this->nodeProcessor = $nodeProcessor;
	}

	public function findPathToEntity(Level $level, Entity $entity, Entity $targetEntity, float $maxDist){
		return $this->findPathToXYZ($level, $entity, $targetEntity->x, $targetEntity->getBoundingBox()->minY, $targetEntity->z, $maxDist);
	}

	public function findPathToPosition(Level $level, Entity $entity, Vector3 $targetPos, float $maxDist){
		return $this->findPathToXYZ($level, $entity, $targetPos->getX() + 0.5, $targetPos->getY() + 0.5, $targetPos->getZ() + 0.5, $maxDist);
	}

	protected function findPathToXYZ(Level $level, Entity $entity, float $x, float $y, float $z, float $maxDist){
		$this->path->clearPath();

		$this->nodeProcessor->initProcessor($level, $entity);

		$startPoint = $this->nodeProcessor->getPathPointTo($entity);
		$endPoint = $this->nodeProcessor->getPathPointToCoords($entity, $x, $y, $z);
		$pathEntity = $this->addToPath($entity, $startPoint, $endPoint, $maxDist);

		$this->nodeProcessor->postProcess();

		return $pathEntity;
	}

	private function addToPath(Entity $entity, PathPoint $startPoint, PathPoint $endPoint, float $maxDistance){
		$startPoint->totalPathDistance = 0.0;
		$startPoint->distanceToNext = $startPoint->distanceSquared($endPoint);
		$startPoint->distanceToTarget = $startPoint->distanceToNext;

		$this->path->clearPath();
		$this->path->addPoint($startPoint);

		$currentPoint = $startPoint;
		var_dump("geldi");

		while(!$this->path->isEmpty()){
			$point = $this->path->dequeue();
			if($point === null){
				return null;
			}

			if($point->equals($endPoint)){
				return $this->createPathEntity($endPoint);
			}

			if($point->distanceSquared($endPoint) < $currentPoint->distanceSquared($endPoint)){
				$currentPoint = $point;
			}

			$point->visited = true;

			$i = $this->nodeProcessor->findPathOptions($this->pathOptions, $entity, $point, $endPoint, $maxDistance);
			var_dump($i);
			for($j = 0; $j < $i; ++$j){
				$point2 = $this->pathOptions[$j];
				$f = $point->totalPathDistance + $point->distanceSquared($point2);

				if($f < ($maxDistance * 2) and (!$point2->isAssigned() or $f < $point2->totalPathDistance)){
					$point2->previous = $point;
					$point2->totalPathDistance = $f;
					$point2->distanceToNext = $point2->distanceToSquared($endPoint);

					if($point2->isAssigned()){
						$this->path->changeDistance($point2, $point2->totalPathDistance + $point2->distanceToNext);
					}else{
						$point2->distanceToTarget = $point2->totalPathDistance + $point2->distanceToNext;
						$this->path->addPoint($point2);
					}
				}
			}
		}

		if($currentPoint === $startPoint){
			return null;
		}else{
			return $this->createPathEntity($currentPoint);
		}
	}

	protected function createPathEntity(PathPoint $current) : PathEntity{
		$points = [];

		for($point = $current; $point->previous !== null; $point = $point->previous){
			array_unshift($points, $point);
		}
		unset($points[0]);

		return new PathEntity($points);
	}
}