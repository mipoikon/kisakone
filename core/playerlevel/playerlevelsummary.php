<?php
/**
 * Suomen Frisbeegolfliitto Kisakone
 * Copyright 2009-2010 Kisakone projektiryhmä
 * Copyright 2014-2015 Tuomo Tanskanen <tuomo@tanskanen.org>
 * Copyright 2016 Mikko Poikonen
 *
 * Player levels details.
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


/* *****************************************************************************
 * This class summarizes player level details
 */
class PlayerLevelSummary
{
	// The player
	var $player;
	// The overall calculated player level
    var $playerLevel;
    // Player level entries that were used for calculation
    var $usedLevels;
    // All player level entries
	var $allLevels; 
	// Number of throws on round where this summary is used. May be null if summary is not round-related
	var $result;

    /** ************************************************************************
     * Class constructor
     */
    function PlayerLevelSummary($playerLevel = null, $usedLevels = null, $allLevels = null, $result = null)
    {
		$this->playerLevel = $playerLevel;
        $this->usedLevels = $usedLevels;
        $this->allLevels = $allLevels;
        $this->result = $result;

        return;
	}
	
	function pushToAllLevels($playerLevel) {
		if ($this->allLevels == null) {
			$this->allLevels = array();
		}
		array_push($this->allLevels, $playerLevel);
	}
	
	function pushToUsedLevels($playerLevel) {
		if ($this->usedLevels == null) {
			$this->usedLevels = array();
		}
		array_push($this->usedLevels, $playerLevel);
	}	
}
