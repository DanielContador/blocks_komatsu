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
 * @author Aaron Wells <aaronw@catalyst.net.nz>
 * @package totara
 * @subpackage plan
 */

/**
 * A page to handle editing an objective in a plan.
 */

require_once('../../../../config.php');
require_once($CFG->dirroot . '/totara/plan/lib.php');
require_once($CFG->dirroot . '/totara/core/js/lib/setup.php');
require_once($CFG->dirroot . '/totara/plan/components/objective/edit_form.php');

// Check if Learning plans are enabled.
check_learningplan_enabled();

global $USER;

require_login();

///
/// Load parameters
///
$planid = required_param('id', PARAM_INT);
$objectiveid = optional_param('itemid', null, PARAM_INT); // Objective id; 0 if creating a new objective
$deleteflag = optional_param('d', false, PARAM_BOOL);
$deleteyes = optional_param('deleteyes', false, PARAM_BOOL);
$deleteno = optional_param('deleteno', null, PARAM_TEXT);
if ($deleteno == null) {
    $deleteno = false;
} else {
    $deleteno = true;
}

///
/// Load data
///
$context = context_system::instance();
$PAGE->set_context($context);
/*$PAGE->set_pagelayout('report');*/
$PAGE->set_pagelayout('dljob');
$PAGE->requires->css(new moodle_url('/theme/dlcourseflix/style/totara/evidence/style.css'));
$PAGE->requires->css(new moodle_url('/theme/dlcourseflix/style/totara/plan/style.css'));
$PAGE->requires->css(new moodle_url('/theme/dlcourseflix/style/user_profile/style.css'));
$PAGE->requires->css(new moodle_url('/theme/dlcourseflix/style/dlforms.css'));
$PAGE->set_url(new moodle_url('/totara/plan/components/objective/edit.php', array('id' => $planid)));
$plan = new development_plan($planid);

$ownplan = ($USER->id == $plan->userid);
$menuitem = ($ownplan) ? '\totara_plan\totara\menu\learningplans' : '\totara_core\totara\menu\myteam';
$PAGE->set_totara_menu_selected($menuitem);

// Permission checks.
if (!$plan->can_update() && !$plan->can_request_approval()) {
    print_error('error:nopermissions', 'totara_plan');
}

$plancompleted = $plan->status == DP_PLAN_STATUS_COMPLETE;
$componentname = 'objective';
$component = $plan->get_component($componentname);
if ($objectiveid == null) {
    $objective = new stdClass();
    $objective->itemid = 0;
    $objective->description = '';
    $action = 'add';
} else {
    if (!$objective = $DB->get_record('dp_plan_objective', array('id' => $objectiveid))) {
        print_error('error:objectiveidincorrect', 'totara_plan');
    }
    $objective->itemid = $objective->id;
    $objective->id = $objective->planid;
    unset($objective->planid);

    if ($deleteflag) {
        $action = 'delete';
    } else {
        $action = 'edit';
    }
}

$objallurl = $component->get_url();
if ($objectiveid) {
    $objviewurl = "{$CFG->wwwroot}/totara/plan/components/objective/view.php?id={$planid}&amp;itemid={$objectiveid}";
} else {
    $objviewurl = $objallurl;
}


///
/// Permissions check
///
require_capability('totara/plan:accessplan', $context);
if (!$component->can_update_items()) {
    print_error('error:cannotupdateobjectives', 'totara_plan');
}
if ($plancompleted) {
    print_error('plancompleted', 'totara_plan');
}

$objective->descriptionformat = FORMAT_HTML;
$objective = file_prepare_standard_editor($objective, 'description', $TEXTAREA_OPTIONS, $TEXTAREA_OPTIONS['context'],
                                          'totara_plan', 'dp_plan_objective', $objective->itemid);
$mform = $component->objective_form($objectiveid);
$mform->set_data($objective);
if (isset($objective->duedate)) {
    $objective->duedate = userdate($objective->duedate, get_string('datepickerlongyearphpuserdate', 'totara_core'), 99, false);
}

if ($deleteyes) {
    require_sesskey();
    if (!$component->delete_objective($objectiveid)) {
        print_error('error:objectivedeleted', 'totara_plan');
    } else {
        \core\notification::success(get_string('objectivedeleted', 'totara_plan'));
        redirect($objallurl);
    }
} else if ($deleteno) {
    redirect($objallurl);
} else if ($mform->is_cancelled()) {

    if ($action == 'add') {
        redirect($objallurl);
    } else {
        redirect($objviewurl);
    }

} if ($data = $mform->get_data()) {
    // A New objective
    if (empty($data->itemid)) {
        $result = $component->create_objective(
                $data->fullname,
                isset($data->description) ? $data->description : null,
                isset($data->priority) ? $data->priority : null,
                !empty($data->duedate) ? $data->duedate : null,
                isset($data->scalevalueid) ? $data->scalevalueid : null
        );
        if (!$result) {
            print_error('error:objectiveupdated', 'totara_plan');
        }
        $data->itemid = $result;
        $notification = get_string('objectivecreated', 'totara_plan');

        $data = file_postupdate_standard_editor($data, 'description', $TEXTAREA_OPTIONS, $TEXTAREA_OPTIONS['context'], 'totara_plan', 'dp_plan_objective', $data->itemid);
        $DB->set_field('dp_plan_objective', 'description', $data->description, array('id' => $data->itemid));
    } else {
        $data = file_postupdate_standard_editor($data, 'description', $TEXTAREA_OPTIONS, $TEXTAREA_OPTIONS['context'], 'totara_plan', 'dp_plan_objective', $data->itemid);

        $record = new stdClass();
        $record->id = $data->itemid;
        $record->planid = $data->id;
        $record->fullname = $data->fullname;
        $record->description = $data->description;
        $record->priority = isset($data->priority)?$data->priority:null;
        $record->duedate = !empty($data->duedate) ? $data->duedate : null;
        $record->scalevalueid = $data->scalevalueid;
        $record->approved = $component->approval_status_after_update();
        $record->timemodified = time();

        $DB->update_record('dp_plan_objective', $record);

        \totara_plan\event\component_updated::create_from_component($plan, 'objective', $record->id, $record->fullname)->trigger();

        // Only send notificaitons when plan not draft
        if ($plan->status != DP_PLAN_STATUS_UNAPPROVED) {
            // Check for changes and send alerts accordingly
            $updated = false;
            foreach (array('fullname', 'description', 'priority', 'duedate', 'approved') as $attribute) {
                if ($record->$attribute != $objective->$attribute) {
                    $updated = $attribute;
                    break;
                }
            }
            // updated?
            if ($updated) {
                $component->send_edit_alert($record, $updated);
            }
            // status?
            if ($record->scalevalueid != $objective->scalevalueid) {
                $component->send_status_alert($record);
            }
        }
        $notification = get_string('objectiveupdated', 'totara_plan');
    }
    \core\notification::success($notification);
    redirect($objviewurl);
}

///
/// Display page
///
$fullname = $plan->name;
$pagetitle = format_string(get_string('learningplan', 'totara_plan').': '.$fullname);
dp_get_plan_base_navlinks($plan->userid);
$PAGE->navbar->add($fullname, new moodle_url('/totara/plan/view.php', array('id' => $planid)));
$PAGE->navbar->add(get_string("{$component->component}plural", 'totara_plan'), new moodle_url('/totara/plan/component.php', array('id' => $planid, 'c' =>'objective')));

switch($action) {
    case 'add':
        $PAGE->navbar->add(get_string('addnewobjective', 'totara_plan'));
        break;
    case 'delete':
        $PAGE->navbar->add(get_string('deleteobjective', 'totara_plan', format_string($objective->fullname)));
        break;
    case 'edit':
        $PAGE->navbar->add(get_string('editobjective', 'totara_plan', format_string($objective->fullname)));
        break;
}

$PAGE->set_title($pagetitle);
$PAGE->set_heading($SITE->fullname);
dp_display_plans_menu($plan->userid,$plan->id,$plan->role);

echo $OUTPUT->header();

// Plan page content
echo $OUTPUT->container_start('', 'dp-plan-content');
print $plan->display_plan_message_box();
print $plan->display_tabs($componentname);

switch($action) {
    case 'add':
        echo $OUTPUT->heading(get_string('addnewobjective', 'totara_plan'));
        $mform->display();
        break;
    case 'delete':
        echo $OUTPUT->heading(get_string('deleteobjective', 'totara_plan'));
        $component->display_objective_detail($objectiveid, $plan->can_update());
        require_once($CFG->dirroot . '/totara/plan/components/evidence/evidence.class.php');
        $evidence = new dp_evidence_relation($plan->id, $componentname, $objectiveid);
        echo $evidence->display_delete_warning();
        echo $OUTPUT->confirm(get_string('deleteobjectiveareyousure', 'totara_plan'),
                new moodle_url('/totara/plan/components/objective/edit.php',
                    array('id' => $planid, 'itemid' => $objectiveid, 'deleteyes' => 'Yes', 'sesskey' => sesskey())),
                new moodle_url('/totara/plan/components/objective/edit.php',
                        array('id' => $planid, 'itemid' => $objectiveid, 'deleteno' => 'No')));
        break;
    case 'edit':
        echo $OUTPUT->heading(get_string('editobjective', 'totara_plan', $objective->fullname));
        $mform->display();
        break;
}

echo $OUTPUT->container_end();

echo $OUTPUT->footer();
die();