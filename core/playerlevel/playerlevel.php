<?php
/**
 * Suomen Frisbeegolfliitto Kisakone
 * Copyright 2009-2010 Kisakone projektiryhmä
 * Copyright 2014-2015 Tuomo Tanskanen <tuomo@tanskanen.org>
 * Copyright 2016 Mikko Poikonen
 *
 * Player level details.
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


// Level types
// Seed is a level value that is predefined for a player
define('LEVEL_TYPE_SEED', 'seed');
// Round is a level value that was calculated from competition
define('LEVEL_TYPE_ROUND', 'round');


/* *****************************************************************************
 * This class contains single player level
 */
class PlayerLevel
{
	// Id
	var $id;
	// Level value, e.g. 930
    var $level;
    // Type of level value
    var $type;
    // Time when level was created
	var $time;
	// Round id. Null if type is seed
	var $round;
	// First name of player
	var $firstName;
	// Last name of player
	var $lastName;
	
	
	
    /** ************************************************************************
     * Class constructor
     */
    function PlayerLevel($id, $level, $type, $time, $round = null)
    {
		$this->id = $id;
        $this->level = $level;
        $this->type = $type;
        $this->time = $time;
        $this->round = $round;

        return;
	}
}
