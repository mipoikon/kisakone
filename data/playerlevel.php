<?php
/**
 * Suomen Frisbeegolfliitto Kisakone
 * Copyright 2009-2010 Kisakone projektiryhmä
 * Copyright 2013-2016 Tuomo Tanskanen <tuomo@tanskanen.org>
 * Copyright 2016 Mikko Poikonen
 *
 * Data access for player level calculations
 *
 * --
 *
 * This file is part of Kisakone.
 * Kisakone is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Kisakone is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with Kisakone.  If not, see <http://www.gnu.org/licenses/>.
 * */
require_once 'data/db.php';
require_once 'data/player.php';
require_once 'core/playerlevel/playerlevel.php';
require_once 'core/playerlevel/playerlevelsummary.php';
require_once 'core/playerlevel/roundplayerlevelsummary.php';

/*
 * Get round player levels for a round
 */
function GetRoundPlayerLevels($roundid, $endTime = '9999-01-01') {
	// Find player level history for those that finished the round
	// For convinience, join also player name
	$result = db_all ("
			SELECT :PlayerLevel.*, :Player.lastname as lastname, :Player.firstname as firstname FROM :PlayerLevel, :RoundResult, :Player
			WHERE :PlayerLevel.Player = :RoundResult.Player			 
			AND :RoundResult.DidNotFinish = 0 AND :RoundResult.Round = $roundid
			AND :PlayerLevel.Time < '$endTime' 
			AND :PlayerLevel.Player = :Player.player_id
			ORDER BY :PlayerLevel.Player, :PlayerLevel.Time");
	
	$playersLevels = toPlayersLevels($result);	
	$roundResults = GetRoundResults($roundid, "results");
	
	$roundPlayerLevelSummary = new RoundPlayerLevelSummary($roundid);
	
	$avgPropagatorLevel = 0;
	$numPropagators = 0;
	$avgPropagatorResult = 0;
	
	// Combine round results and player levels
	foreach ($roundResults as $result) {		
		// Check that player levels were found for result
		if (array_key_exists($result['PlayerId'], $playersLevels)) {  
			$playerLevelSummary = $playersLevels[$result['PlayerId']];
			$playerLevelSummary->result = $result['Total'];
			$roundPlayerLevelSummary->pushToAllPlayers($playerLevelSummary);
		} 
	}

	selectPropagators($roundPlayerLevelSummary);
	calculateSlope($roundPlayerLevelSummary);
	
	return $roundPlayerLevelSummary;	
}

/*
 * Calculates and saves player levels for an event
 */
function SavePlayerLevels($eventid) {
	// Save earned player levels
	$rounds = GetEventRounds($eventid);
	foreach ($rounds as $round) {
		$roundPlayerLevelSummary =  GetRoundPlayerLevels($round->id, date('Y-m-d H:i', $round->starttime));
		
		if (!$roundPlayerLevelSummary->slope || $roundPlayerLevelSummary->slope == 0) {
			// No slope, cannot calculate player levels
			continue;
		}
		
		// Loop all results and calculate individual level
		$result = db_all("SELECT :RoundResult.id, :RoundResult.Player, DidNotFinish, Course, Result FROM :RoundResult,:Round,:Event WHERE Round=:Round.id and :Round.Event= $eventid");
		
		foreach ($result as $row) {		
			$dnf = $row['DidNotFinish'];
			$course = $row['Course'];
			$id = $row['id'];
			$player = $row['Player'];
			$result = $row['Result'];
			
			
			if ($dnf == 1) continue;

			$level = calulateRoundResultLevel($roundPlayerLevelSummary, $result);
			
			SaveRoundPlayerLevel($level, $player, $id);
		}		
	}
}

function SaveRoundPlayerLevel($level, $player, $roundResult) {
	// Delete first, we may be re-calculating
	$del = db_exec("DELETE FROM :PlayerLevel WHERE RoundResult = $roundResult");
	// TODO: Use round start time as level time
	$ins = db_exec("INSERT INTO :PlayerLevel (Player, Level, Type, RoundResult) VALUES ($player, $level, 'round', $roundResult)");
}

/*
 * Selects and sets propagators from all players with levels on round
 */
function selectPropagators($roundPlayerLevelSummary) {
	// If no players available
	if (!$roundPlayerLevelSummary->allPlayers) {
		$roundPlayerLevelSummary->propagatorPlayers = array();
		return;
	}
	foreach ($roundPlayerLevelSummary->allPlayers as $playerLevelSummary) {
		// Currently all players used as propagators, implement proper selection
		$roundPlayerLevelSummary->pushToPropagatorPlayers($playerLevelSummary);
	}
}

function selectUsedLevels($playerLevelSummary) {
	// If no levels available
	if (!$playerLevelSummary->allLevels) {
		$playerLevelSummary->usedLevels = array();
		return;
	}
	foreach ($playerLevelSummary->allLevels as $playerLevel) {
		// Currently all levels are used, implement filtering
		$playerLevelSummary->pushToUsedLevels($playerLevel);
	}
}

/*
 * Calculates and sets slope for round player level summary.
 */
function calculateSlope($roundPlayerLevelSummary) {
	$numPropagators = 0;
	$propagatorLevel = 0;	
	$propagatorResult = 0;
	
	// Fixed 1500 for now
	$roundPlayerLevelSummary->slopeZeroLevel = 1500;

	// If no propagators
	if (!$roundPlayerLevelSummary->propagatorPlayers) {
		$roundPlayerLevelSummary->slope = 0;
		return;
	}
		
	// Sum levels and rounds
	foreach ($roundPlayerLevelSummary->propagatorPlayers as $propagator) {
		$propagatorResult += $propagator->result;			
		$propagatorLevel += $propagator->playerLevel;
		$numPropagators++;
	}
	
	// Sanity check if we came here without proper data
	if ($numPropagators < 1 || $propagatorLevel < 1 || $propagatorResult < 1) {
		$roundPlayerLevelSummary->slope = 0;
		return;
	}
	
	$avgPropagatorLevel = $propagatorLevel / $numPropagators;
	$avgPropagatorResult = $propagatorResult / $numPropagators;
	
	$slope = ($avgPropagatorLevel - $roundPlayerLevelSummary->slopeZeroLevel) / $avgPropagatorResult;
	$roundPlayerLevelSummary->slope = $slope;
	
	return;
}

/*
 * Calculates a player level for round result
 * 
 */
function calulateRoundResultLevel($roundPlayerLevelSummary, $result) {
	return round($roundPlayerLevelSummary->slope * $result + $roundPlayerLevelSummary->slopeZeroLevel);
}


function toPlayersLevels($rows) {
	$currentPlayer = -1;
	// Array with player as key and value is array of levels
	$results = array();
	$playerLevelSummary;
	// Collect all player levels to array by player
	foreach ($rows as $row) {
		// Create new player levels if player changed
		if ($row['Player'] != $currentPlayer) {			
			$currentPlayer = $row['Player'];
			$playerLevelSummary = new PlayerLevelSummary();
			$playerLevelSummary->firstName = $row['firstname'];
			$playerLevelSummary->lastName = $row['lastname'];
			$playerLevelSummary->player = $currentPlayer;
			$results[$currentPlayer] = $playerLevelSummary;
		}
		$playerLevel = new PlayerLevel($row['id'], $row['Level'], $row['Type'], $row['Time'], $row['RoundResult']);
		$playerLevelSummary->pushToAllLevels($playerLevel);
	}
	
	// Select used levels
	foreach ($results as $playerLevelSummary) {
		selectUsedLevels($playerLevelSummary);
	}
	
	// Calculate player level value. Average of levels that are used for now.
	foreach ($results as $playerLevelSummary) {
		$levelsToUse = count($playerLevelSummary->usedLevels);
		if ($levelsToUse > 0) {
			$sum = 0;
			foreach ($playerLevelSummary->usedLevels as $usedLevel) {
				$sum += $usedLevel->level;
			}
			if ($sum > 0) {
				$playerLevelSummary->playerLevel = round($sum / $levelsToUse);
			} else {
				$playerLevelSummary->playerLevel = 0;
			}			
		} else {
			$playerLevelSummary->playerLevel = 0;
		}
	}
	
	return $results;
}