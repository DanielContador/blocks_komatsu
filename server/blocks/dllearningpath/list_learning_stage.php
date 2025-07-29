<?php

global $DB, $PAGE, $SITE, $OUTPUT, $CFG;

use totara_core\advanced_feature;

require_once('../../config.php');
require_once($CFG->dirroot . '/totara/reportbuilder/lib.php');

$path_id = required_param('path_id', PARAM_INT);
$delete_id = optional_param('delete', 0, PARAM_INT);

require_login();

$context = context_system::instance();

if (!has_capability('moodle/site:config', $context)) {
    throw new Exception('Unautorized');
}

$baseurl = new moodle_url('/blocks/dllearningpath/list_learning_stage.php');
$pageurl = new moodle_url($baseurl);

$PAGE->set_url($pageurl);
$PAGE->set_context($context);
$PAGE->set_heading(format_string($SITE->fullname));
$PAGE->set_title(get_string('pluginname', 'block_dllearningpath'));
$PAGE->set_pagelayout('admin');

echo $OUTPUT->header();

if ($delete_id !== 0) {
    try {
        // Eliminar las etapas
        $DB->delete_records('dl_learning_path_stage', ['id' => $delete_id]);
    } catch (Exception $e) {
        var_dump($e->getMessage());
    }
}

//Render the block header
$header_render = $PAGE->get_renderer('core');
$context = new stdClass();
$context->add_path_active = '';
$context->add_stage_active = 'active';
$context->path_link = new moodle_url('/blocks/dllearningpath/add_learning_path.php', array('path_id' => $path_id));
$context->stage_link = '#';
$context->pluginname = get_string('pluginname', 'block_dllearningpath');
$context->add_stage_string = get_string('stages', 'block_dllearningpath');

echo $header_render->render_from_template('block_dllearningpath/add_header', $context);

//Render the reportbuilder
$renderer = $PAGE->get_renderer('totara_reportbuilder');
$debug = optional_param('debug', 0, PARAM_INT);
$sid = optional_param('sid', '0', PARAM_INT);

/* Define the "Team Members" embedded report */

$shortname = 'dllearning_stage';

$config = (new rb_config())->set_sid($sid)->set_embeddata([]);
if (!$report = reportbuilder::create_embedded($shortname, $config, false)) {
    print_error('error:couldnotgenerateembeddedreport', 'totara_reportbuilder');
}

$logurl = $PAGE->url->out_as_local_url();

\totara_reportbuilder\event\report_viewed::create_from_report($report)->trigger();
$PAGE->requires->js('/blocks/dllearningpath/amd/src/delete.js');
$report->include_js();

echo html_writer::tag('div', $renderer->single_button(
    new \moodle_url('/blocks/dllearningpath/add_learning_stage.php', array('path_id' => $path_id)),
    get_string('add_stage', 'block_dllearningpath')
), ['class' => 'pull-right']);

// This must be done after the header and before any other use of the report.
list($reporthtml, $debughtml) = $renderer->report_html($report, $debug);
echo $debughtml;

$report->display_restrictions();
$heading = $renderer->result_count_info($report);
echo $OUTPUT->heading($heading);
echo $renderer->print_description($report->description, $report->_id);
// Print saved search options and filters.
$report->display_saved_search_options();
$report->display_search();
$report->display_sidebar_search();

echo $reporthtml;
echo $OUTPUT->footer();