<?php
global $DB;
require_once('add_learning_path_form.php');
require_once __DIR__ . '/locallib.php';

global $PAGE, $SITE, $OUTPUT;
require_once(__DIR__ . '/../../config.php');

$context = context_system::instance();
$path_id = optional_param('path_id', -1, PARAM_INT);

if (!has_capability('moodle/site:config', $context)) {
    throw new Exception('Unautorized');
}

$baseurl = new moodle_url('/blocks/dllearningpath/add_learning_path.php');
$pageurl = new moodle_url($baseurl);

$PAGE->set_url($pageurl);
$PAGE->set_context($context);
$PAGE->set_heading(format_string($SITE->fullname));
$PAGE->set_title(get_string('pluginname', 'block_dllearningpath'));
$PAGE->set_pagelayout('admin');

if ($path_id == -1) {
    $mform = new block_dllearningpath_add_learning_path_form();
} else {
    $defaultData = new stdClass();
    $edit_path = $DB->get_record('dl_learning_path', ['id' => $path_id], 'name, cohorts');
    $defaultData->name = $edit_path->name;
    $defaultData->cohorts = json_decode($edit_path->cohorts);
    $defaultData->update = $path_id;
    $mform = new block_dllearningpath_add_learning_path_form($defaultData);
}

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/blocks/dllearningpath/list_learning_path.php'));
} else if ($data = $mform->get_data()) {
    if (isset($data->submitbutton)) {
        unset($data->submitbutton);
        $data->status = 1;
        $data->timemodified = time();
        if (!empty($data->cohorts)) {
            $data->cohorts = json_encode($data->cohorts);
        } else {
            $data->cohorts = '';
        }

        try {
            if ($data->update != 0) {
                $data->id = $data->update;
                $path_id = $data->update;
                $old_cohorts = $DB->get_record('dl_learning_path', ['id' => $path_id], 'cohorts');
                \blocks\dllearningpath\locallib\dllearning_paths_update_cohorts($path_id, $old_cohorts->cohorts ?? '', $data->cohorts);

                $DB->update_record('dl_learning_path', $data);
            } else {
                $data->timecreated = time();
                $path_id = $DB->insert_record('dl_learning_path', $data);
            }

            \blocks\dllearningpath\locallib\dllearning_paths_enrol($data->cohorts, $path_id);

        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    redirect(new moodle_url('/blocks/dllearningpath/add_learning_path.php', ['path_id' => $path_id]),
        get_string('saved', 'block_dllearningpath'));
}

echo $OUTPUT->header();

$render = $PAGE->get_renderer('core');
$context = new stdClass();
$context->add_path_active = 'active';
$context->add_stage_active = '';
$context->path_link = '#';
$context->pluginname = get_string('pluginname', 'block_dllearningpath');
$context->add_stage_string = get_string('stages', 'block_dllearningpath');
if ($path_id == -1) {
    $context->is_disabled = 'disabled';
    $context->stage_link = new moodle_url('');
} else {
    $context->stage_link = new moodle_url('/blocks/dllearningpath/list_learning_stage.php', ['path_id' => $path_id]);
}

echo $render->render_from_template('block_dllearningpath/add_header', $context);

echo "<h2>" . get_string('add_path', 'block_dllearningpath') . "</h2>";

$mform->display();

echo $OUTPUT->footer();

