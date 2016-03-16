<?php
/**
 * Suomen Frisbeegolfliitto Kisakone
 * Copyright 2009-2010 Kisakone projektiryhmä
 * Copyright 2014-2015 Tuomo Tanskanen <tuomo@tanskanen.org>
 *
 * This file contains functionality for managing users
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

require_once 'core/player.php';
require_once 'data/player.php';

/** ****************************************************************************
 * Function for registering a new user.
 *
 * Returns null for success or an Error object in case the registration fails
 *
 * @param string $username  - users username
 * @param string $password  - users password
 * @param string $email     - users email
 * @param string $firstname - users firstname
 * @param string $lastname  - users lastname
 * @param string $gender    - players gender
 * @param int    $pdga      - players pdga
 * @param int    $birthyear - players birthyear
 */
function RegisterPlayer($username, $password, $email, $firstname, $lastname, $gender, $pdga, $birthyear)
{
    $err = null;

    if (isset($gender) && $gender == "female")
        $gender = PLAYER_GENDER_FEMALE;
    else
        $gender = PLAYER_GENDER_MALE;

    $player = new Player(null, $pdga, $gender, $birthyear, $firstname, $lastname, $email);
    $err = $player->ValidatePlayer();
    if (!isset($err)) {
        $player = SetPlayerDetails($player);
        if (is_a($player, "Error"))
            return $player;
    }
    else
        return $err;
    if ($username === null) {
    	$username = generateUserName($player);
    }    
    $user = new User(null, $username, USER_ROLE_PLAYER, $firstname, $lastname, $email, $player->id);
    if ($password === null) {
    	$password = generateRandomPassword(10);
    }
    $err = $user->ValidateUser();
    if (!isset($err)) {
        if ($user->username !== null) {
            $err = $user->SetPassword($password);
            if (is_a($err, "Error"))
                return $err;
        }

        $err = SetUserDetails($user);
        if (is_a($err, "Error"))
            $user = null;
        else
            ChangeUserPassword($user->id, $password);
    }
    else
        return $err;

    return $user;
}

function generateRandomPassword($length = 10) {
	$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$charactersLength = strlen($characters);
	$randomString = '';
	for ($i = 0; $i < $length; $i++) {
		$randomString .= $characters[rand(0, $charactersLength - 1)];
	}
	return $randomString;
}

function generateUserName($player) {
	$generated_username = mb_substr($player->firstname, 0, 3, 'UTF-8') . mb_substr($player->lastname, 0, 5, 'UTF-8') . $player->id;
	$generated_username = normalize_username($generated_username);
	return $generated_username;
}

/** Normalize a string so that it can be compared with others without being too fussy.
 *   e.g. "Ádrèñålînë" would return "adrenaline"
 *   Note: Some letters are converted into more than one letter,
 *   e.g. "ß" becomes "sz", or "ä" becomes "ae"
 *   
 *   This is from: http://stackoverflow.com/questions/11354195/issues-replacing-special-characters-in-php-string
 */
function normalize_username($string) {
	// remove whitespace, leaving only a single space between words.
	$string = preg_replace('/\s+/', ' ', $string);
	// flick diacritics off of their letters
	$string = preg_replace('~&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml|caron);~i', '$1', htmlentities($string, ENT_COMPAT, 'UTF-8'));
	return $string;
}
