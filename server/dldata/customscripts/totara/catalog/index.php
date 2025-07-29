<?php
/*
 * This file is part of Totara LMS
 *
 * Copyright (C) 2018 onwards Totara Learning Solutions LTD
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
 * @author Nathan Lewis <nathan.lewis@totaralearning.com>
 * @package totara_catalog
 */

require_once('../../config.php');

use theme_dlcourseflix\controllers\course_catalog;

global $CFG, $OUTPUT, $PAGE;

require_login();

$pageurl = new moodle_url('/totara/catalog/index.php');
// Set grid catalog as homepage for user when user home page preference is enabled.
if (optional_param('setdefaulthome', 0, PARAM_BOOL)) {
    if (!empty($CFG->allowdefaultpageselection) && $CFG->catalogtype === 'totara' && !isguestuser()) {
        require_sesskey();
        set_user_preference('user_home_page_preference', HOMEPAGE_TOTARA_GRID_CATALOG);
        \core\notification::success(get_string('userhomepagechanged', 'totara_dashboard'));
        redirect($pageurl);
    }
}

$heading = get_string('catalog_heading', 'totara_catalog');
$PAGE->set_title($heading);
$PAGE->set_heading($heading);
$PAGE->set_pagelayout('catalog');

(new course_catalog())->process();
exit;