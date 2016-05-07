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
 * This class summarizes player level details for a round
 */
class RoundPlayerLevelSummary
{
	// Round
	var $roundId;
	// All player level details
    var $allPlayers;
    // Propagator player details
    var $propagatorPlayers;
    // Calculated slope
	var $slope;
	// Slope zero point
	var $slopeZeroLevel;

    /** ************************************************************************
     * Class constructor
     */
    function RoundPlayerLevelSummary($roundId = null, $allPlayers = null, $propagatorPlayers = null, $slope = null, $slopeZeroLevel = null)
    {
		$this->roundId = $roundId;
        $this->allPlayers = $allPlayers;
        $this->propagatorPlayers = $propagatorPlayers;
        $this->slope = $slope;
        $this->slopeZeroLevel = $slopeZeroLevel;

        return;
	}
	
	function pushToAllPlayers($playerLevelSummary) {
		if ($this->allPlayers == null) {
			$this->allPlayers = array();
		}
		$this->allPlayers[$playerLevelSummary->player] = $playerLevelSummary;
	}
	
	function pushToPropagatorPlayers($playerLevelSummary) {
		if ($this->propagatorPlayers == null) {
			$this->propagatorPlayers = array();
		}
		$this->propagatorPlayers[$playerLevelSummary->player] = $playerLevelSummary;
	}	
}
