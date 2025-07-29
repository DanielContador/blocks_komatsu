<?php

require_once('../../config.php');
require_once($CFG->dirroot . '/totara/form/classes/form.php');
require_once($CFG->dirroot . '/local/dl_selfenrol_info/constants.php');

use local_dl_selfenrol_info\form\dynamic_fields_form;
use local_dl_selfenrol_info\selfenrol_manager;

require_login();

$courseid = required_param('courseid', PARAM_INT);
$action = optional_param('action', '', PARAM_TEXT);

if ($action != '') {
    try {
        $enrolmethod = required_param('enrolmethod', PARAM_TEXT);
        $enroltype = required_param('enroltype', PARAM_TEXT);
        $dlactionbutton = required_param('dlactionbutton', PARAM_TEXT);

        set_config("dl_$enrolmethod", "$enroltype", 'local_dl_selfenrol_info');
        set_config("dlbutton_$enrolmethod", "$dlactionbutton", 'local_dl_selfenrol_info');
        redirect(new moodle_url('/enrol/index.php', array('id' => $courseid)), 'Cambios actualizados');
    } catch (moodle_exception $e) {
        die($e->getMessage());
    }
}

$PAGE->set_url(new moodle_url('/local/dl_selfenrol_info/index.php', ['courseid' => $courseid]));
$coursecontext = context_course::instance($courseid); // Get the context of the course
$PAGE->set_context($coursecontext);
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('pluginname', 'local_dl_selfenrol_info'));
$PAGE->set_heading(get_string('pluginname', 'local_dl_selfenrol_info'));

echo $OUTPUT->header();

$currentdata['courseid'] = $courseid;
$currentdata['whatlearn'] = selfenrol_manager::get_whatlearn_data($courseid);
$content_data = selfenrol_manager::get_content_data($courseid);
$currentdata['numfields'] = $content_data ? count($content_data) : 0;

if ($content_data) {
    foreach ($content_data as $index => $content) {
        $currentdata["content_title_$index"] = $content['content_title'];
        $currentdata["content_text_$index"] = $content['content_text'];
    }
}

$mform = new dynamic_fields_form($currentdata);

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/local/dl_selfenrol_info/add.php', ['courseid' => $courseid]));
} else if ($formdata = $mform->get_data()) {
    $whatlearn = $formdata->whatlearn;
    $content_data = [];
    $numfields = $formdata->numfields;

    for ($i = 0; $i < $numfields; $i++) {
        $content_data[] = [
            'content_title' => $formdata->{"content_title_$i"},
            'content_text'  => $formdata->{"content_text_$i"}
        ];
    }

    // Use the manager to add or update records
    selfenrol_manager::save_whatlearn_data($courseid, $whatlearn);
    selfenrol_manager::save_content_data($courseid, $content_data);

    // Redirect or display a success message
    redirect(new moodle_url('/local/dl_selfenrol_info/add.php', ['courseid' => $courseid]),
             get_string('datainserted', 'local_dl_selfenrol_info'), null, \core\output\notification::NOTIFY_SUCCESS);
} else {
    echo $mform->render();
}

echo $OUTPUT->footer();
