<?php

global $DB, $PAGE, $SITE, $OUTPUT;
require_once('add_learning_stage_form.php');
require_once(__DIR__ . '/../../config.php');
require_once __DIR__ . '/locallib.php';

$path_id = optional_param('path_id', -1, PARAM_INT);
$stage_id = optional_param('stage_id', -1, PARAM_INT);

$context = context_system::instance();

if (!has_capability('moodle/site:config', $context)) {
    throw new Exception('Unautorized');
}

$baseurl = new moodle_url('/blocks/dllearningpath/add_learning_stage.php');
$pageurl = new moodle_url($baseurl, array('path_id' => $path_id, 'stage_id' => $stage_id));

$PAGE->set_url($pageurl);
$PAGE->set_context($context);
$PAGE->set_heading(format_string($SITE->fullname));
$PAGE->set_title(get_string('pluginname', 'block_dllearningpath'));
$PAGE->set_pagelayout('admin');

if ($stage_id == -1) {
    $mform = new block_dllearningpath_add_learning_stage_form();
} else {
    $defaultData = new stdClass();
    $edit_stage = $DB->get_record('dl_learning_path_stage', array('id' => $stage_id), 'name, course_sets, learning_path_id');
    $defaultData->name = $edit_stage->name;
    $defaultData->course_sets = json_decode($edit_stage->course_sets);
    $defaultData->learning_path_id = $edit_stage->learning_path_id;
    $defaultData->update = $stage_id;
}

if ($path_id !== -1 && !isset($defaultData)) {
    $defaultData = new stdClass();
    $defaultData->learning_path_id = $path_id;
}

if (isset($defaultData)) {
    $mform = new block_dllearningpath_add_learning_stage_form($defaultData);
} else {
    $mform = new block_dllearningpath_add_learning_stage_form();
}

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/blocks/dllearningpath/list_learning_path.php'));
} else if ($data = $mform->get_data()) {
    if (isset($data->submitbutton)) {
        unset($data->submitbutton);
        if (empty($data->course_sets)){
            redirect(new moodle_url('/blocks/dllearningpath/add_learning_stage.php', array('path_id' => $data->hidden_learning_path_id)),
                get_string('courserequiered', 'block_dllearningpath'), null, 'error');
        }
        $data->course_sets = json_encode($data->course_sets);
        $data->sort = blocks\dllearningpath\locallib\dllearning_paths_get_order($data->learning_path_id);

        if ($data->update != 0) {
            $data->id = $data->update;
            unset($data->sort);
            $DB->update_record('dl_learning_path_stage', $data);
        } else {
            $data->timecreated = time();
            $DB->insert_record('dl_learning_path_stage', $data);
        }

    }
    redirect(new moodle_url('/blocks/dllearningpath/list_learning_stage.php', array('path_id' => $data->hidden_learning_path_id)),
        get_string('saved', 'block_dllearningpath'));
}

echo $OUTPUT->header();

echo "<h2>" . get_string('add_stage', 'block_dllearningpath') . "</h2>";

$mform->display();

echo $OUTPUT->footer();

