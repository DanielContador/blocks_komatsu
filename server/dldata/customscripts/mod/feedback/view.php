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
 * the first page to view the feedback
 *
 * @author Andreas Grabs
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package mod_feedback
 */
require_once(__DIR__ . '/../../../../config.php');
require_once($CFG->dirroot . '/mod/feedback/lib.php');

$id = required_param('id', PARAM_INT);
$courseid = optional_param('courseid', false, PARAM_INT);
$PAGE->requires->css(new moodle_url('/theme/dlcourseflix/style/mod_feedback/style.css'));

$current_tab = 'view';

list($course, $cm) = get_course_and_cm_from_cmid($id, 'feedback');
require_course_login($course, true, $cm);
$feedback = $PAGE->activityrecord;

$feedbackcompletion = new mod_feedback_completion($feedback, $cm, $courseid);

$context = context_module::instance($cm->id);

if ($course->id == SITEID) {
    $PAGE->set_pagelayout('incourse');
}
$PAGE->set_url('/mod/feedback/view.php', array('id' => $cm->id));
$PAGE->set_title($feedback->name);
$PAGE->set_heading($course->fullname);
$iswebview = $PAGE->pagelayout == 'webview';

// Check access to the given courseid.
if ($courseid and $courseid != SITEID) {
    require_course_login(get_course($courseid)); // This overwrites the object $COURSE .
}

// Check whether the feedback is mapped to the given courseid.
if (!has_capability('mod/feedback:edititems', $context) &&
    !$feedbackcompletion->check_course_is_mapped()) {
    echo $OUTPUT->header();
    echo $OUTPUT->notification(get_string('cannotaccess', 'mod_feedback'));
    echo $OUTPUT->footer();
    exit;
}

// Trigger module viewed event.
$feedbackcompletion->trigger_module_viewed();

/// Print the page header
echo $OUTPUT->header();

/// Print the main part of the page
///////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////

/*$previewimg = $OUTPUT->pix_icon('t/preview', get_string('preview'));
$previewlnk = new moodle_url('/mod/feedback/print.php', array('id' => $id));
if ($courseid) {
    $previewlnk->param('courseid', $courseid);
}
$preview = html_writer::link($previewlnk, $previewimg);

echo $OUTPUT->heading(get_string('preview') . $preview);*/

echo self_completion_form($cm, $course);

// Print the tabs.
require('tabs.php');

// Show description.

if ($feedbackcompletion->can_complete() && $feedbackcompletion->can_submit()) {

    $feedbackRenderer = $PAGE->get_renderer('core');
    $feedbackContext = new stdClass();
    $feedbackContext->name = $feedback->name;
    $feedbackContext->anonymous = $feedback->anonymous;
    $completeurl = new moodle_url('/mod/feedback/complete.php',
        ['id' => $id, 'courseid' => $courseid]);
    if ($startpage = $feedbackcompletion->get_resume_page()) {
        $completeurl->param('gopage', $startpage);
        $feedbackContext->completeUrl = $completeurl->out(false);
    } else {
        $feedbackContext->completeUrl = $completeurl->out(false);
    }

    if (empty($feedback->intro)) {
        $desc = '<p>El objetivo es recolectar información relevante sobre el aprendizaje 
        y grado de satisfacción de cada participante bajo el modelo de capacitación 
        vía E-Learning. Para nosotros es importante contar con tu opinión en cada 
        una de las preguntas detalladas a continuación.</p>';
    } else {
        $desc = $feedback->intro;
    }

    $feedbackContext->desc = $desc;

    echo $feedbackRenderer->render_from_template('theme_dlcourseflix/mod_feedback_view', $feedbackContext);

    /*echo html_writer::start_tag('div', array('class' => 'dl-feedback-init d-flex'));

    echo html_writer::start_tag('div', array('class' => 'dl-feedback-desc-box'));
    echo html_writer::tag('h2', get_string('submitevaluation', 'feedback'), array('class' => 'dl-submitevaluation-label'));
    $options = (object)array('noclean' => true);
    echo format_module_intro('feedback', $feedback, $cm->id);

    // Display a link to complete feedback or resume.
    $completeurl = new moodle_url('/mod/feedback/complete.php',
            ['id' => $id, 'courseid' => $courseid]);
    if ($startpage = $feedbackcompletion->get_resume_page()) {
        $completeurl->param('gopage', $startpage);
        $label = get_string('resume', 'feedback');
    } else {
        $label = get_string('start', 'feedback');
    }
    echo html_writer::start_tag('div', array('class' => 'dl-complete-feedback'));
    echo html_writer::link($completeurl, $label);
    echo $OUTPUT->flex_icon('theme_dl|arrow-right');
    echo html_writer::end_tag('div');
    echo html_writer::end_tag('div');

    echo html_writer::start_tag('div', array('class' => 'dl-feedback-img-box'));
    echo '<img src="'.$OUTPUT->image_url('initfeedback', 'local_modextracontent').'" />';
    echo html_writer::end_tag('div');

    echo html_writer::end_tag('div');*/

}

//show some infos to the feedback
if (has_capability('mod/feedback:edititems', $context)) {

    echo $OUTPUT->heading(get_string('overview', 'feedback'), 3);

    //get the groupid
    $groupselect = groups_print_activity_menu($cm, $CFG->wwwroot . '/mod/feedback/view.php?id=' . $cm->id, true);
    $mygroupid = groups_get_activity_group($cm);

    echo $groupselect . '<div class="clearer">&nbsp;</div>';
    $summary = new mod_feedback\output\summary($feedbackcompletion, $mygroupid, true);
    echo $OUTPUT->render_from_template('mod_feedback/summary', $summary->export_for_template($OUTPUT));

    if ($pageaftersubmit = $feedbackcompletion->page_after_submit()) {
        echo $OUTPUT->heading(get_string("page_after_submit", "feedback"), 3);
        echo $OUTPUT->box($pageaftersubmit, 'generalbox feedback_after_submit');
    }
}

if (!has_capability('mod/feedback:viewreports', $context) &&
    $feedbackcompletion->can_view_analysis()) {
    $analysisurl = new moodle_url('/mod/feedback/analysis.php', array('id' => $id));
    echo '<div class="mdl-align"><a href="' . $analysisurl->out() . '">';
    echo get_string('completed_feedbacks', 'feedback') . '</a>';
    echo '</div>';
}

if (has_capability('mod/feedback:mapcourse', $context) && $feedback->course == SITEID) {
    echo $OUTPUT->box_start('generalbox feedback_mapped_courses');
    echo $OUTPUT->heading(get_string("mappedcourses", "feedback"), 3);
    echo '<p>' . get_string('mapcourse_help', 'feedback') . '</p>';
    $mapurl = new moodle_url('/mod/feedback/mapcourse.php', array('id' => $id));
    echo '<p class="mdl-align">' . html_writer::link($mapurl, get_string('mapcourses', 'feedback')) . '</p>';
    echo $OUTPUT->box_end();
}

if ($feedbackcompletion->can_complete()) {
    echo $OUTPUT->box_start('generalbox boxaligncenter');
    if (!$feedbackcompletion->is_open()) {
        // Feedback is not yet open or is already closed.
        echo $OUTPUT->notification(get_string('feedback_is_not_open', 'feedback'));
        if (!$iswebview) { // Hide this button on webviews.
            echo $OUTPUT->continue_button(course_get_url($courseid ?: $course->id));
        }
    } else if (!$feedbackcompletion->can_submit()) {
        // Feedback was already submitted.
        echo $OUTPUT->notification(get_string('this_feedback_is_already_submitted', 'feedback'));
        if (!$iswebview) { // Hide this button on webviews.
            $OUTPUT->continue_button(course_get_url($courseid ?: $course->id));
        }
    }
    echo $OUTPUT->box_end();
}

echo $OUTPUT->footer();
exit();
