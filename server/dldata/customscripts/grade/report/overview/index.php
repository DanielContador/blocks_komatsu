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
global $OUTPUT;

/**
 * The gradebook overview report
 *
 * @package   gradereport_overview
 * @copyright 2007 Nicolas Connault
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once '../../../config.php';
require_once $CFG->libdir . '/gradelib.php';
require_once $CFG->dirroot . '/grade/lib.php';
require_once $CFG->dirroot . '/grade/report/overview/lib.php';

$courseid = optional_param('id', SITEID, PARAM_INT);
$userid = optional_param('userid', $USER->id, PARAM_INT);

$PAGE->set_url(new moodle_url('/grade/report/overview/index.php', ['id' => $courseid, 'userid' => $userid]));

if (!$course = $DB->get_record('course', ['id' => $courseid])) {
    print_error('invalidcourseid');
}

// Totara: added ability to redirect user out of this page if the course is not a legacy course.
$hook = new \gradereport_overview\hook\index_view($courseid);
$hook->execute();

require_login(null, false);
$PAGE->set_course($course);

$context = context_course::instance($course->id);
$systemcontext = context_system::instance();
$personalcontext = null;

// If we are accessing the page from a site context then ignore this check.
if ($courseid != SITEID) {
    require_capability('gradereport/overview:view', $context);
}

if (empty($userid)) {
    require_capability('moodle/grade:viewall', $context);

} else {
    if (!$DB->get_record('user', ['id' => $userid, 'deleted' => 0]) or isguestuser($userid)) {
        print_error('invaliduserid');
    }
    $personalcontext = context_user::instance($userid);
}

if (isset($personalcontext) && $courseid == SITEID) {
    $PAGE->set_context($personalcontext);
} else {
    $PAGE->set_context($context);
}
if ($userid == $USER->id) {
    $settings = $PAGE->settingsnav->find('mygrades', null);
    $settings->make_active();
} else if ($courseid != SITEID && $userid) {
    // Show some other navbar thing.
    $user = $DB->get_record('user', ['id' => $userid], '*', MUST_EXIST);
    $PAGE->navigation->extend_for_user($user);
}

$access = grade_report_overview::check_access($systemcontext, $context, $personalcontext, $course, $userid);

if (!$access) {
    // no access to grades!
    print_error('nopermissiontoviewgrades', 'error', $CFG->wwwroot . '/course/view.php?id=' . $courseid);
}

/// return tracking object
$gpr = new grade_plugin_return(['type' => 'report', 'plugin' => 'overview', 'courseid' => $course->id, 'userid' => $userid]);

/// last selected report session tracking
if (!isset($USER->grade_last_report)) {
    $USER->grade_last_report = [];
}
$USER->grade_last_report[$course->id] = 'overview';

// First make sure we have proper final grades.
if (\core_course\local\grade_helper::does_course_need_regrade($courseid)) {
    // Allow async regrading.
    if (\core_course\local\grade_helper::use_async_course_regrade($courseid)) {
        core\task\grade_regrade_final_grades_task::enqueue($courseid);
        if (has_capability("moodle/grade:manage", $context)) {
            // Notify that grades are stale.
            core\notification::info(get_string("course_regrade_needed", "totara_core"));
        }
    } else {
        grade_regrade_final_grades($courseid);
    }
}

if (has_capability('moodle/grade:viewall', $context) && $courseid != SITEID) {
    // Please note this would be extremely slow if we wanted to implement this properly for all teachers.
    $groupmode = groups_get_course_groupmode($course);   // Groups are being used
    $currentgroup = groups_get_course_group($course, true);

    if (!$currentgroup) {      // To make some other functions work better later
        $currentgroup = null;
    }

    $isseparategroups = ($course->groupmode == SEPARATEGROUPS and !has_capability('moodle/site:accessallgroups', $context));

    if ($isseparategroups and (!$currentgroup)) {
        // no separate group access, can view only self
        $userid = $USER->id;
        $user_selector = false;
    } else {
        $user_selector = true;
    }

    if (empty($userid)) {
        // Add tabs
        print_grade_page_head($courseid, 'report', 'overview');

        groups_print_course_menu($course, $gpr->get_return_url('index.php?id=' . $courseid, ['userid' => 0]));

        if ($user_selector) {
            $renderer = $PAGE->get_renderer('gradereport_overview');
            echo $renderer->graded_users_selector('overview', $course, $userid, $currentgroup, false);
        }
        // do not list all users

    } else { // Only show one user's report
        $report = new grade_report_overview($userid, $gpr, $context);
        print_grade_page_head($courseid, 'report', 'overview', get_string('pluginname', 'gradereport_overview') .
            ' - ' . fullname($report->user), false, false, true, null, null, $report->user);
        groups_print_course_menu($course, $gpr->get_return_url('index.php?id=' . $courseid, ['userid' => 0]));

        if ($user_selector) {
            $renderer = $PAGE->get_renderer('gradereport_overview');
            echo $renderer->graded_users_selector('overview', $course, $userid, $currentgroup, false);
        }

        if ($currentgroup and !groups_is_member($currentgroup, $userid)) {
            echo $OUTPUT->notification(get_string('groupusernotmember', 'error'));
        } else {
            if ($report->fill_table()) {
                echo '<br />' . $report->print_table(true);
            }
        }
    }
} else { // Non-admins and users viewing from the site context can just see their own report.

    // Create a report instance
    $report = new grade_report_overview($userid, $gpr, $context);

    if (!empty($report->studentcourseids)) {
        // If the course id matches the site id then we don't have a course context to work with.
        // Display a standard page.
        if ($courseid == SITEID) {
            $PAGE->set_pagelayout('dlreport');
            $header = get_string('grades', 'grades') . ' - ' . fullname($report->user);
            $PAGE->set_title($header);
            $PAGE->set_heading(fullname($report->user));

            if ($USER->id != $report->user->id) {
                $PAGE->navigation->extend_for_user($report->user);
                if ($node = $PAGE->settingsnav->get('userviewingsettings' . $report->user->id)) {
                    $node->forceopen = true;
                }
            } else if ($node = $PAGE->settingsnav->get('usercurrentsettings', navigation_node::TYPE_CONTAINER)) {
                $node->forceopen = true;
            }

            echo $OUTPUT->header();
            if ($report->courses) {
                echo html_writer::tag('h3', get_string('coursesiamtaking', 'grades'));
            }
            if ($report->fill_table(true, true)) {
                echo $report->print_table(true);
            }
        } else { // We have a course context. We must be navigating from the gradebook.
            /*print_grade_page_head($courseid, 'report', 'overview', get_string('pluginname', 'gradereport_overview')
                . ' - ' . fullname($report->user));*/

            //print_grade_page_head para poder cambiar el layout

            //Variables de la función para mantener la lógica
            $active_type = 'report';
            $active_plugin = 'overview';
            $heading = get_string('pluginname', 'gradereport_overview') . ' - ' . fullname($report->user);
            $return = false;
            $buttons = false;
            $shownavigation = true;
            $headerhelpidentifier = null;
            $headerhelpcomponent = null;
            $user = null;

            // Put a warning on all gradebook pages if the course has modules currently scheduled for background deletion.
            require_once($CFG->dirroot . '/course/lib.php');
            if (course_modules_pending_deletion($courseid)) {
                \core\notification::add(get_string('gradesmoduledeletionpendingwarning', 'grades'),
                    \core\output\notification::NOTIFY_WARNING);
            }

            if ($active_type === 'preferences') {
                // In Moodle 2.8 report preferences were moved under 'settings'. Allow backward compatibility for 3rd party grade reports.
                $active_type = 'settings';
            }

            $plugin_info = grade_get_plugin_info($courseid, $active_type, $active_plugin);

            // Determine the string of the active plugin
            $stractive_plugin = ($active_plugin) ? $plugin_info['strings']['active_plugin_str'] : $heading;
            $stractive_type = $plugin_info['strings'][$active_type];

            if (empty($plugin_info[$active_type]->id) || !empty($plugin_info[$active_type]->parent)) {
                $title = $PAGE->course->fullname . ': ' . $stractive_type . ': ' . $stractive_plugin;
            } else {
                $title = $PAGE->course->fullname . ': ' . $stractive_plugin;
            }

            if ($active_type == 'report') {
                $PAGE->set_pagelayout('incourse');
            } else {
                $PAGE->set_pagelayout('admin');
            }
            $PAGE->set_title(get_string('grades') . ': ' . $stractive_type);
            $PAGE->set_heading($title);
            if ($buttons instanceof single_button) {
                $buttons = $OUTPUT->render($buttons);
            }
            $PAGE->set_button($buttons);
            if ($courseid != SITEID) {
                grade_extend_settings($plugin_info, $courseid);
            }

            // Set the current report as active in the breadcrumbs.
            if ($active_plugin !== null && $reportnav = $PAGE->settingsnav->find($active_plugin, navigation_node::TYPE_SETTING)) {
                $reportnav->make_active();
            }

            $returnval = $OUTPUT->header();

            if (!$return) {
                echo $returnval;
            }

            // Guess heading if not given explicitly
            if (!$heading) {
                $heading = $stractive_plugin;
            }

            if ($shownavigation) {
                $navselector = null;
                if ($courseid != SITEID &&
                    ($CFG->grade_navmethod == GRADE_NAVMETHOD_COMBO || $CFG->grade_navmethod == GRADE_NAVMETHOD_DROPDOWN)) {
                    // It's absolutely essential that this grade plugin selector is shown after the user header. Just ask Fred.
                    $navselector = print_grade_plugin_selector($plugin_info, $active_type, $active_plugin, true);
                    if ($return) {
                        $returnval .= $navselector;
                    } else if (!isset($user)) {
                        echo $navselector;
                    }
                }

                $output = '';
                // Add a help dialogue box if provided.
                if (isset($headerhelpidentifier)) {
                    $output = $OUTPUT->heading_with_help($heading, $headerhelpidentifier, $headerhelpcomponent);
                } else {
                    if (isset($user)) {
                        $output = $OUTPUT->context_header(
                                [
                                    'heading' => html_writer::link(new moodle_url('/user/profile.php', ['id' => $user->id,
                                        'course' => $courseid]), fullname($user)),
                                    'user' => $user,
                                    'usercontext' => context_user::instance($user->id),
                                ], 2
                            ) . $navselector;
                    } else {
                        $output = $OUTPUT->heading($heading);
                    }
                }

                if ($return) {
                    $returnval .= $output;
                } else {
                    echo $output;
                }

                if ($courseid != SITEID &&
                    ($CFG->grade_navmethod == GRADE_NAVMETHOD_COMBO || $CFG->grade_navmethod == GRADE_NAVMETHOD_TABS)) {
                    $returnval .= grade_print_tabs($active_type, $active_plugin, $plugin_info, $return);
                }
            }

            $returnval .= print_natural_aggregation_upgrade_notice($courseid,
                context_course::instance($courseid),
                $PAGE->url,
                $return);

            if ($return) {
                return $returnval;
            }

            if ($report->fill_table()) {
                echo '<br />' . $report->print_table(true);
            }

            //End
        }
    } else {
        $PAGE->set_pagelayout('dlreport');
        $header = get_string('grades', 'grades') . ' - ' . fullname($report->user);
        $PAGE->set_title($header);
        $PAGE->set_heading(fullname($report->user));
        echo $OUTPUT->header();
    }

    if (count($report->teachercourses)) {
        echo html_writer::tag('h3', get_string('coursesiamteaching', 'grades'));
        $report->print_teacher_table();
    }

    if (empty($report->studentcourseids) && empty($report->teachercourses)) {
        // We have no report to show the user. Let them know something.
        echo $OUTPUT->notification(get_string('noreports', 'grades'), 'notifymessage');
    }
}

grade_report_overview::viewed($context, $courseid, $userid);

echo $OUTPUT->footer();
die();
