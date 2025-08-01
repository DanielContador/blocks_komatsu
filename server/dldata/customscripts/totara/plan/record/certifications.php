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
 * @author Russell England <russell.england@catalyst-eu.net>
 * @package totara
 * @subpackage totara_plan
 */

/**
 * Displays certifications for the current user
 *
 */

use totara_core\advanced_feature;

require_once('../../../config.php');
require_once($CFG->dirroot.'/totara/reportbuilder/lib.php');
require_once($CFG->dirroot.'/totara/plan/lib.php');
require_once($CFG->dirroot . '/totara/program/lib.php');

require_login();

if (advanced_feature::is_disabled('recordoflearning')) {
    print_error('error:recordoflearningdisabled', 'totara_plan');
}

// Check if certifications are enabled.
check_certification_enabled();

$sid = optional_param('sid', '0', PARAM_INT);
$certifid = optional_param('certifid', null, PARAM_INT);
$history = optional_param('history', null, PARAM_BOOL);
$userid = optional_param('userid', $USER->id, PARAM_INT); // Which user to show.
$format = optional_param('format', '', PARAM_TEXT); // Export format.
$rolstatus = optional_param('status', 'all', PARAM_ALPHANUM);
$debug  = optional_param('debug', 0, PARAM_INT);
// Set status.
if (!in_array($rolstatus, array('active', 'completed','all'))) {
    $rolstatus = 'all';
}
// Set user.
if (!$user = $DB->get_record('user', array('id' => $userid))) {
    print_error('error:usernotfound', 'totara_plan');
}
// Set certification.
if (!empty($certifid) && (!$certification = $DB->get_record('prog', array('certifid' => $certifid), 'fullname'))) {
    print_error(get_string('error:incorrectcertifid', 'totara_certification', null, $certifid));
}

$context = context_system::instance();

$pageparams = array(
    'userid' => $userid,
    'status' => $rolstatus
);
if ($certifid) {
    $pageparams['certifid'] = $certifid;
}
if ($history) {
    $pageparams['history'] = $history;
}
if ($format) {
    $pageparams['format'] = $format;
}
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/totara/plan/record/certifications.php', $pageparams));
$PAGE->set_pagelayout('dljob');
$PAGE->requires->css(new moodle_url('/theme/dlcourseflix/style/totara/evidence/style.css'));
$PAGE->requires->css(new moodle_url('/theme/dlcourseflix/style/totara/plan/style.css'));
$PAGE->requires->css(new moodle_url('/theme/dlcourseflix/style/user_profile/style.css'));
$PAGE->requires->css(new moodle_url('/theme/dlcourseflix/style/dlforms.css'));

/** @var totara_reportbuilder_renderer $renderer */
$renderer = $PAGE->get_renderer('totara_reportbuilder');

if ($USER->id != $userid) {
    $strheading = get_string('recordoflearningforname', 'totara_core', fullname($user, true));
} else {
    $strheading = get_string('recordoflearning', 'totara_core');
}
// Get subheading name for display.
$strsubheading = get_string($rolstatus.'certificationssubhead', 'totara_plan');

$shortname = 'plan_certifications';
$data = array(
    'userid' => $userid,
);
if ($rolstatus !== 'all') {
    $data['rolstatus'] = $rolstatus;
}
if ($history) {
    $shortname = 'plan_certifications_history';
    if (!empty($certifid)) {
        $data['certifid'] = $certifid;
        $strsubheading = get_string('certificationshistoryforsubhead', 'totara_plan', $certification->fullname);
    } else {
        $strsubheading = get_string('certificationshistorysubhead', 'totara_plan');
    }
}
// Set report.
$config = (new rb_config())->set_sid($sid)->set_embeddata($data);
if (!$report = reportbuilder::create_embedded($shortname, $config)) {
    print_error('error:couldnotgenerateembeddedreport', 'totara_reportbuilder');
}

$logurl = $PAGE->url->out_as_local_url();
if ($format != '') {
    $report->export_data($format);
    die;
}

\totara_reportbuilder\event\report_viewed::create_from_report($report)->trigger();

$report->include_js();

// Display the page.
$ownplan = $USER->id == $userid;
$usertype = ($ownplan) ? 'learner' : 'manager';
if ($usertype == 'manager') {
    if (advanced_feature::is_enabled('myteam')) {
        $menuitem = 'myteam';
        $url = new moodle_url('/my/teammembers.php');
        $PAGE->navbar->add(get_string('team', 'totara_core'), $url);
    } else {
        $menuitem = null;
        $url = null;
    }
} else {
    $menuitem = null;
    $url = null;
}
$PAGE->navbar->add($strheading, new moodle_url('/totara/plan/record/index.php', array('userid' => $userid)));
$PAGE->navbar->add($strsubheading);
$PAGE->set_title($strheading);
$PAGE->set_button($report->edit_button());
$PAGE->set_heading($SITE->fullname);

$menuitem = ($ownplan) ? '\totara_plan\totara\menu\recordoflearning' : '\totara_core\totara\menu\myteam';
$PAGE->set_totara_menu_selected($menuitem);
dp_display_plans_menu($userid, 0, $usertype, 'certifications', $rolstatus);

echo $OUTPUT->header();

// This must be done after the header and before any other use of the report.
list($reporthtml, $debughtml) = $renderer->report_html($report, $debug);
echo $debughtml;

echo $OUTPUT->container_start('', 'dp-plan-content');
echo $OUTPUT->heading($strheading.': '.$strsubheading);

$currenttab = 'certifications';
dp_print_rol_tabs($rolstatus, $currenttab, $userid);

$report->display_restrictions();

echo $renderer->print_description($report->description, $report->_id);

// Print saved search options and filters.
$report->display_saved_search_options();
$report->display_search();
$report->display_sidebar_search();

echo $renderer->result_count_heading($report, $renderer->showhide_button($report));

echo $reporthtml;
// Export button.
$renderer->export_select($report, $sid);

echo $OUTPUT->container_end();
echo $OUTPUT->footer();
die();