<?php
/*
 * This file is part of Totara LMS
 *
 * Copyright (C) 2010 onwards Totara Learning Solutions LTD
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Yuliya Bozhko <yuliya.bozhko@totaralms.com>
 * @package totara
 * @subpackage program
 */

require_once('../../config.php');
require_once($CFG->dirroot . '/totara/program/lib.php');
require_once($CFG->libdir. '/coursecatlib.php');
use theme_dlcourseflix\controllers\program_index;

$viewtype = optional_param('viewtype', 'course', PARAM_TEXT);
$categoryid = optional_param('categoryid', 0, PARAM_INT);

if ($CFG->forcelogin) {
    require_login();
}

$PAGE->set_context(\context_system::instance());

$PAGE->set_url('/totara/program/index.php', array('viewtype' => $viewtype, 'categoryid' => $categoryid));

$PAGE->set_pagelayout('coursecategory');
// $PAGE->set_pagetype('course-index');
$pagetitle = get_string('myprograms', 'theme_dlcourseflix'); // Asegurarse de que la cadena de texto esté en el idioma correcto

// $PAGE->navbar->remove(-1);
$PAGE->navbar->add($pagetitle);
$PAGE->set_title($pagetitle);
$PAGE->set_heading($SITE->fullname);

(new program_index())->process();
die;