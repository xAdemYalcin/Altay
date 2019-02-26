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

namespace pocketmine\command\utils;

use pocketmine\command\CommandSender;
use pocketmine\entity\Entity;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\utils\Utils;

class CommandSelector{

	public const SELECTOR_ALL_PLAYERS = "@a";
	public const SELECTOR_ALL_ENTITIES = "@e";
	public const SELECTOR_CLOSEST_PLAYER = "@p";
	public const SELECTOR_RANDOM_PLAYER = "@r";
	public const SELECTOR_YOURSELF = "@s";

	private function __construct(){
		// NOOP
	}

	/**
	 * @param CommandSender $sender
	 * @param string        $selector
	 * @param string        $entityType
	 * @param Vector3|null  $pos
	 *
	 * @return Entity[]
	 */
	public static function findTargets(CommandSender $sender, string $selector, string $entityType = Entity::class, ?Vector3 $pos = null) : array{
		$targets = [];

		if(!($pos instanceof Position) and $pos instanceof Vector3){
			if($sender instanceof Position){
				$pos = $sender->asPosition()->setComponents($pos->x, $pos->y, $pos->z);
			}else{
				$pos = new Position($pos->x, $pos->y, $pos->z, $sender->getServer()->getLevelManager()->getDefaultLevel());
			}
		}

		if($pos === null){
			$pos = $sender instanceof Position ? $sender : $sender->getServer()->getLevelManager()->getDefaultLevel()->getSpawnLocation();
		}
		switch($selector){
			case CommandSelector::SELECTOR_ALL_PLAYERS:
				$targets = $sender->getServer()->getOnlinePlayers();
				break;
			case CommandSelector::SELECTOR_ALL_ENTITIES:
				$targets = array_filter($pos->getLevel()->getEntities(), function($value) use ($entityType) : bool{
					return $value instanceof $entityType;
				});
				break;
			case CommandSelector::SELECTOR_CLOSEST_PLAYER:
				if($sender instanceof $entityType){
					$targets = [$sender];
				}else{
					$nearest = $pos->getLevel()->getNearestEntity($pos, 100, $entityType);
					if($nearest !== null){
						$targets = [$nearest];
					}
				}
				break;
			case CommandSelector::SELECTOR_RANDOM_PLAYER:
				$players = array_values($sender->getServer()->getOnlinePlayers());
				$targets = !empty($players) ? [$players[mt_rand(0, count($players) - 1)]] : [];
				break;
			case CommandSelector::SELECTOR_YOURSELF:
				$targets = $sender instanceof $entityType ? [$sender] : [];
				break;
			default:
				$player = $sender->getServer()->getPlayerExact($selector);
				$targets = $player instanceof $entityType ? [$player] : [];
				break;
		}

		if(empty($targets)){
			throw new NoSelectorMatchException;
		}

		return $targets;
	}
}