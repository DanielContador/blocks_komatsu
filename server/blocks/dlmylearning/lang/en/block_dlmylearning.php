<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 *
 * @package   block_dlmylearning
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['dlmylearning:addinstance'] = 'Add a new My learning block';
$string['dlmylearning:myaddinstance'] = 'Add a new My learning to Dashboard';
$string['dlmylearning'] = 'My learning block';
$string['pluginname'] = 'My learning';
$string['userinfo'] = 'My learning';
$string['showexpiringcourses'] = 'Courses on process';
$string['showexpiringcourseslabel'] = 'Custom in Progress Label';
$string['showcourses'] = 'My Courses';
$string['completedcourses'] = 'Completed courses';
$string['showcompletedcourseslabellabel'] = 'Completed courses label';
$string['showcompletedcoursesblock'] = 'Completed courses';
$string['showwithoutprogresscourses'] = 'Not started courses';
$string['showwithoutprogresscourseslabel'] = 'Not started courses label';
$string['withoutprogresscourses'] = 'No started courses';
$string['showcertificates'] = 'Show My Certificates';
$string['showprograms'] = 'My Programs';
$string['showsectionyes'] = 'Yes';
$string['showsectionnot'] = 'No';
$string['my_courses'] = 'My Courses';
$string['mycourseslabel'] = 'My Courses label';
$string['my_programs'] = 'My Programs';
$string['myprogramslabel'] = 'My Programs label';
$string['my_certificates'] = 'My Certifications';
$string['courses'] = 'courses';
$string['of'] = 'of';
$string['search_courses'] = 'Search Courses';
$string['search_programs'] = 'Search Programs';
$string['search_certificates'] = 'Search Certifications';
$string['courses'] = 'Courses';
$string['programs'] = 'Programs';
$string['certifications'] = 'Certifications';
$string['showmodule'] = 'Show Module';
$string['gridformat'] = 'Grid Format';
$string['generalgrid'] = 'Generic Grid';
$string['coursespecificgrid'] = 'Courses Grid';
$string['programspecificgrid'] = 'Programs Grid';

$string['pending'] = 'Pending';
$string['expiring'] = 'Due Soon';
$string['expired'] = 'Expired';
$string['current'] = 'Current';
$string['historical'] = 'Historical';
$string['modulestatus'] = 'Module Items Status';

$string['coursespending'] = 'Pending Courses';
$string['expiringcourses'] = 'Expiring Courses';
$string['currentcourses'] = 'Current courses';
$string['historicalcourses'] = 'Historical Courses';

$string['programspending'] = 'Pending Programs';
$string['expiringprograms'] = 'Expiring Programs';
$string['currentprograms'] = 'Current Programs';
$string['historicalprograms'] = 'Historical Programs';

$string['countcards'] = 'Count of Grid Items';
$string['countcards_help'] = 'Text Help';

$string['recentcourses'] = 'Recent Courses';
$string['recentprograms'] = 'Recent Programs';
$string['recentcertifications'] = 'Recent Certifications';

$string['gotocourses'] = 'See courses';
$string['gotoprograms'] = 'See programs';
$string['gotocertifications'] = 'See certifications';
$string['stateinprogress'] = 'In progress';
$string['statecomplete'] = 'Complete';
$string['statenotstarted'] = 'Not started';

$string['maxitems'] = 'Max number of items';
$string['showcompletedcourses'] = 'Show completed courses';
$string['showcoursestimeend'] = 'Show overdue courses';
$string['showprogramscompleted'] = 'Show completed programs';
$string['showprogramstimedue'] = 'Show overdue programs';
$string['showcompletedprograms'] = 'Show completed programs';

$string['unlimited'] = 'Unlimited';
$string['showprogramcourses'] = 'Show program courses';
$string['showprogramcourses_help'] =
    'Show courses belonging to current programs where the user is assigned but their enrollment is different than by program (Example: By Audience).';
$string['orderblocks_help'] = 'Define the order in which the blocks appear';
$string['orderblocks'] = 'Order blocks';
$string['blockedition'] = '<h3>Blocks edition</h3> <br> <hr>';

//Help
$string['showexpiringcourses_help'] = 'Controls the visibility and name of the block "Courses on process". 
A course is considered on process when its progress percentage is between 1% and 99%, and its status is 
"in progress".';
$string['showcourses_help'] = 'Controls the visibility and name of the block "My courses". This block combines the blocks 
"Not started courses" and "Completed courses", with the additional option to show or hide courses belonging to programs.';
$string['withoutprogresscourses_help'] = 'Controls the visibility and name of the block "Not started courses". 
A course is considered not started when its progress percentage is 0% and its status is "Not started".';
$string['showcompletedcoursesblock_help'] = 'Controls the visibility and name of the block "Completed courses". 
A course is considered completed when its progress percentage is 100% and its status is "completed".';
$string['showprograms_help'] = 'Controls the visibility and name of the block "My programs". This block shows 
all the user\'s programs, with the additional option to show or hide completed programs.';