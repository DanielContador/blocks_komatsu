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
 * This page shows all course enrolment options for current user.
 *
 * @package    core_enrol
 * @copyright  2010 Petr Skoda {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../../config.php');
require_once("$CFG->libdir/formslib.php");

$id = required_param('id', PARAM_INT);
$returnurl = optional_param('returnurl', 0, PARAM_LOCALURL);

// Totara: Add the ability to redirect user out of here if this $id is a non-course.
$hook = new \totara_core\hook\enrol_index_page($id);
$hook->execute();

if (!isloggedin()) {
    $referer = get_local_referer();
    if (empty($referer)) {
        // A user that is not logged in has arrived directly on this page,
        // they should be redirected to the course page they are trying to enrol on after logging in.
        $SESSION->wantsurl = "$CFG->wwwroot/course/view.php?id=$id";
    }
    // do not use require_login here because we are usually coming from it,
    // it would also mess up the SESSION->wantsurl
    redirect(get_login_url());
}

$course = $DB->get_record('course', array('id'=>$id), '*', MUST_EXIST);
$context = context_course::instance($course->id, MUST_EXIST);

// Everybody is enrolled on the frontpage
if ($course->id == SITEID) {
    redirect("$CFG->wwwroot/");
}

if (!totara_course_is_viewable($course->id)) {
    print_error('coursehidden');
}

$PAGE->requires->css('/local/dl_selfenrol_info/styles.css');

$PAGE->set_course($course);
$PAGE->set_pagelayout('selfenrol');
$PAGE->set_url('/enrol/index.php', array('id'=>$course->id));

// do not allow enrols when in login-as session
if (\core\session\manager::is_loggedinas() and $USER->loginascontext->contextlevel == CONTEXT_COURSE) {
    print_error('loginasnoenrol', '', $CFG->wwwroot.'/course/view.php?id='.$USER->loginascontext->instanceid);
}

// get all enrol forms available in this course
$enrols = enrol_get_plugins(true);
$enrolinstances = enrol_get_instances($course->id, true);
$forms = array();
foreach($enrolinstances as $instance) {
    if (!isset($enrols[$instance->enrol])) {
        continue;
    }
    $form = $enrols[$instance->enrol]->enrol_page_hook($instance);
    if ($form) {
        $forms[$instance->id] = array('enroltype' => 'enrol_'.$instance->enrol, 'form' => $form);
        // $forms[$instance->id+1] = array('enroltype' => 'enrol_'.$instance->enrol, 'form' => $form);
        // $forms[$instance->id+2] = array('enroltype' => 'enrol_'.$instance->enrol, 'form' => $form);
    }
}

// Check if user already enrolled
if (is_enrolled($context, $USER, '', true)) {
    $default_destination = "$CFG->wwwroot/course/view.php?id=$course->id";
    if (!empty($SESSION->wantsurl)) {
        // Check there hasn't been a user navigation earlier that inadvertently changed the SESSION->wantsurl value to
        // contain a different course id.
        $check_url = new moodle_url($SESSION->wantsurl);
        $session_course_id = $check_url->get_param('id');
        if ($session_course_id != $course->id && $check_url->get_path() === '/course/view.php') {
            $SESSION->wantsurl = $default_destination;
        }
        $destination = $SESSION->wantsurl;
        unset($SESSION->wantsurl);
    } else {
        $destination =  $default_destination;
    }
    redirect($destination);   // Bye!
}

$PAGE->set_title($course->shortname);
$PAGE->set_heading($course->fullname);

echo $OUTPUT->header();
// echo $OUTPUT->heading(get_string('enrolmentoptions','enrol'));

$courserenderer = $PAGE->get_renderer('core', 'course');
// echo $courserenderer->course_info_box($course);

//TODO: find if future enrolments present and display some info

$selfenrolrenderer = $PAGE->get_renderer('local_dl_selfenrol_info');
echo $selfenrolrenderer->render_course_banner($course->id, $forms);

if (!$forms) {
    // Totara: ignore the wanted URL, most likely we cannot go there without enrolment.
    unset($SESSION->wantsurl);
    if (isguestuser()) {
        echo get_string('noguestaccess', 'enrol') . ' ' . html_writer::link(get_login_url(), get_string('login', 'core'), array('class' => 'btn btn-default'));
    } else if ($returnurl) {
        notice(get_string('notenrollable', 'enrol'), $returnurl);
    } else {
        $url = get_local_referer(false);
        if (empty($url)) {
            $url = new moodle_url('/index.php');
        }
        notice(get_string('notenrollable', 'enrol'), $url);
    }
}

echo $OUTPUT->footer();
die;