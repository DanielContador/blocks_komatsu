<?php
/*
 * This file is part of Totara Learn
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
 * @author Aleksandr Baishev <aleksandr.baishev@totaralearning.com>
 * @package totara_competency
 */

use totara_competency\controllers\profile\index;

require_once('../../../config.php');
require_login();

$id = optional_param('id', null, PARAM_INT);
$PAGE->set_pagelayout('dlcompetencies');
$PAGE->set_context(context_system::instance());
$PAGE->set_url('/totara/competency/profile/index.php', array('id' => $id));

$pagetitle = get_string('header_competencies', 'totara_competency');
$PAGE->navbar->add($pagetitle);
$PAGE->set_title($pagetitle);

$PAGE->set_heading($SITE->fullname);

echo $OUTPUT->header();

echo html_writer::tag('h2', get_string('competencies', 'totara_criteria'), array('class' => 'fw-bold'));

echo $OUTPUT->footer();
die;
