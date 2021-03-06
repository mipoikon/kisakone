<?php
/*
 * Suomen Frisbeegolfliitto Kisakone
 * Copyright 2009-2010 Kisakone projektiryhmä
 * Copyright 2014-2015 Tuomo Tanskanen <tuomo@tanskanen.org>
 *
 * Tournament details page
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
/**
 * Initializes the variables and other data necessary for showing the matching template
 * @param Smarty $smarty Reference to the smarty object being initialized
 * @param Error $error If input processor encountered a minor error, it will be present here
 */

require_once 'core/tournament.php';

function InitializeSmartyVariables(&$smarty, $error)
{
    language_include('users');
    $tournament = GetTournamentDetails($_GET['id']);

    if (!$tournament)
        return Error::NotFound('tournament');

    $eph = array();
    $numev = $tournament->GetNumEvents();
    for ($ind = 0; $ind < $numev; ++$ind)
        $eph[] = true;

    $smarty->assign('eventplaceholders', $eph);
    $smarty->assign('tournament', $tournament);

    if (@$_GET['edit'] && IsAdmin())
        $smarty->assign('edit', true);
}

/**
 * Determines which main menu option this page falls under.
 * @return String token of the main menu item text.
 */
function getMainMenuSelection()
{
    return 'tournaments';
}
