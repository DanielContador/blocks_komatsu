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
 * The gradebook user report
 *
 * @package   gradereport_user
 * @copyright 2007 Moodle Pty Ltd (http://moodle.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

//require_once '../../../config.php';
require_once $CFG->libdir . '/gradelib.php';
require_once $CFG->dirroot . '/grade/lib.php';
require_once $CFG->dirroot . '/grade/report/user/lib.php';

$courseid = required_param('id', PARAM_INT);
$userid = optional_param('userid', $USER->id, PARAM_INT);
$userview = optional_param('userview', 0, PARAM_INT);

// Totara: added ability to redirect user out of this page if the course is not a legacy course.
$hook = new \gradereport_user\hook\index_view($courseid);
$hook->execute();

$PAGE->set_url(new moodle_url('/grade/report/user/index.php', ['id' => $courseid]));

if ($userview == 0) {
    $userview = get_user_preferences('gradereport_user_view_user', GRADE_REPORT_USER_VIEW_USER);
} else {
    set_user_preference('gradereport_user_view_user', $userview);
}

/// basic access checks
if (!$course = $DB->get_record('course', ['id' => $courseid])) {
    print_error('invalidcourseid');
}
require_login($course);
$PAGE->set_pagelayout('dlreport');

$context = context_course::instance($course->id);
require_capability('gradereport/user:view', $context);

if (empty($userid)) {
    require_capability('moodle/grade:viewall', $context);

} else {
    if (!$DB->get_record('user', ['id' => $userid, 'deleted' => 0]) or isguestuser($userid)) {
        print_error('invaliduser');
    }
}

$access = false;
if (has_capability('moodle/grade:viewall', $context)) {
    //ok - can view all course grades
    $access = true;

} else if ($userid == $USER->id and has_capability('moodle/grade:view', $context) and $course->showgrades) {
    //ok - can view own grades
    $access = true;

} else if (has_capability('moodle/grade:viewall', context_user::instance($userid)) and $course->showgrades) {
    // ok - can view grades of this user- parent most probably
    $access = true;
}

if (!$access) {
    // no access to grades!
    print_error('nopermissiontoviewgrades', 'error', $CFG->wwwroot . '/course/view.php?id=' . $courseid);
}

/// return tracking object
$gpr = new grade_plugin_return(['type' => 'report', 'plugin' => 'user', 'courseid' => $courseid, 'userid' => $userid]);

/// last selected report session tracking
if (!isset($USER->grade_last_report)) {
    $USER->grade_last_report = [];
}
$USER->grade_last_report[$course->id] = 'user';

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

if (has_capability('moodle/grade:viewall', $context)) { //Teachers will see all student reports
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

    $defaultgradeshowactiveenrol = !empty($CFG->grade_report_showonlyactiveenrol);
    $showonlyactiveenrol = get_user_preferences('grade_report_showonlyactiveenrol', $defaultgradeshowactiveenrol);
    $showonlyactiveenrol = $showonlyactiveenrol || !has_capability('moodle/course:viewsuspendedusers', $context);

    $renderer = $PAGE->get_renderer('gradereport_user');

    if ($userview == GRADE_REPORT_USER_VIEW_USER) {
        $viewasuser = true;
    } else {
        $viewasuser = false;
    }

    if (empty($userid)) {
        $gui = new graded_users_iterator($course, null, $currentgroup);
        $gui->require_active_enrolment($showonlyactiveenrol);
        $gui->init();
        // Add tabs
        print_grade_page_head($courseid, 'report', 'user');
        groups_print_course_menu($course, $gpr->get_return_url('index.php?id=' . $courseid, ['userid' => 0]));

        if ($user_selector) {
            echo $renderer->graded_users_selector('user', $course, $userid, $currentgroup, true);
        }

        echo $renderer->view_user_selector($userid, $userview);

        while ($userdata = $gui->next_user()) {
            $user = $userdata->user;
            $report = new grade_report_user($courseid, $gpr, $context, $user->id, $viewasuser);

            $studentnamelink =
                html_writer::link(new moodle_url('/user/profile.php', ['id' => $report->user->id, 'course' => $courseid]),
                    fullname($report->user));
            echo $OUTPUT->heading(get_string('pluginname', 'gradereport_user') . ' - ' . $studentnamelink);

            if ($report->fill_table()) {
                echo '<br />' . $report->print_table(true);
            }
            echo "<p style = 'page-break-after: always;'></p>";
        }
        $gui->close();
    } else { // Only show one user's report
        $report = new grade_report_user($courseid, $gpr, $context, $userid, $viewasuser);

        $studentnamelink =
            html_writer::link(new moodle_url('/user/profile.php', ['id' => $report->user->id, 'course' => $courseid]),
                fullname($report->user));
        /*print_grade_page_head($courseid, 'report', 'user', get_string('pluginname', 'gradereport_user') . ' - ' . $studentnamelink,
            false, false, true, null, null, $report->user);*/

        //print_grade_page_head para poder cambiar el layout

        //Variables de la función para mantener la lógica
        $active_type = 'report';
        $active_plugin = 'user';
        $heading = get_string('pluginname', 'gradereport_user') . ' - ' . $studentnamelink;
        $return = false;
        $buttons = false;
        $shownavigation = true;
        $headerhelpidentifier = null;
        $headerhelpcomponent = null;
        $user = $report->user;

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

        groups_print_course_menu($course, $gpr->get_return_url('index.php?id=' . $courseid, ['userid' => 0]));

        if ($user_selector) {
            $showallusersoptions = true;
            echo $renderer->graded_users_selector('user', $course, $userid, $currentgroup, $showallusersoptions);
        }

        echo $renderer->view_user_selector($userid, $userview);

        if ($currentgroup and !groups_is_member($currentgroup, $userid)) {
            echo $OUTPUT->notification(get_string('groupusernotmember', 'error'));
        } else {
            if ($report->fill_table()) {
                echo '<br />' . $report->print_table(true);
            }
        }
    }
} else { //Students will see just their own report

    // Create a report instance
    $report = new grade_report_user($courseid, $gpr, $context, $userid);

    // print the page
    /*print_grade_page_head($courseid, 'report', 'user',
        get_string('pluginname', 'gradereport_user') . ' - ' . fullname($report->user));*/

    //print_grade_page_head para poder cambiar el layout

    //Variables de la función para mantener la lógica
    $active_type = 'report';
    $active_plugin = 'user';
    $heading = get_string('pluginname', 'gradereport_user') . ' - ' . fullname($report->user);
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

    if ($report->fill_table()) {
        echo '<br />' . $report->print_table(true);
    }
}

if (isset($report)) {
    // Trigger report viewed event.
    $report->viewed();
} else {
    echo html_writer::tag('div', '', ['class' => 'clearfix']);
    echo $OUTPUT->notification(get_string('nostudentsyet'));
}

echo $OUTPUT->footer();
die();