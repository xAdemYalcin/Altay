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

namespace pocketmine\event\player;

use pocketmine\lang\TextContainer;
use pocketmine\Player;

/**
 * Called when the player spawns in the world after logging in, when they first see the terrain.
 *
 * Note: A lot of data is sent to the player between login and this event. Disconnecting the player during this event
 * will cause this data to be wasted. Prefer disconnecting at login-time if possible to minimize bandwidth wastage.
 * @see PlayerLoginEvent
 */
class PlayerJoinEvent extends PlayerEvent{
	/** @var string|TextContainer */
	protected $joinMessage;

	/**
	 * PlayerJoinEvent constructor.
	 *
	 * @param Player               $player
	 * @param TextContainer|string $joinMessage
	 */
	public function __construct(Player $player, $joinMessage){
		$this->player = $player;
		$this->joinMessage = $joinMessage;
	}

	/**
	 * @param string|TextContainer $joinMessage
	 */
	public function setJoinMessage($joinMessage) : void{
		$this->joinMessage = $joinMessage;
	}

	/**
	 * @return string|TextContainer
	 */
	public function getJoinMessage(){
		return $this->joinMessage;
	}

}