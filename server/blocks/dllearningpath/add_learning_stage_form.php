<?php
global $CFG;

/**
 * Block edit form class for the block_dllearningpath plugin.
 *
 * @package   block_dllearningpath
 * @copyright 2024, DL
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/formslib.php');
require_once __DIR__ . '/locallib.php';

class block_dllearningpath_add_learning_stage_form extends moodleform {

    protected $defaultData;

    public function __construct($defaultData = []) {
        $this->defaultData = $defaultData;
        parent::__construct();
    }

    // Define the form.

    /**
     * @throws coding_exception
     * @throws dml_exception
     */
    public function definition() {
        $mform = $this->_form;

        foreach ($this->defaultData as $key => $value) { // if have default data, set it
            $mform->setDefault($key, $value);
        }

        $mform->addElement('text', 'name', get_string('name', 'block_dllearningpath'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', get_string('required', 'block_dllearningpath'), 'required', 'null', 'client');
        $mform->addRule('name', get_string('maxsizeallowed', 'block_dllearningpath'), 'maxlength', 30, 'client');

        // Add a select box.
        $courses = blocks\dllearningpath\locallib\dllearning_paths_get_courses();
        $options = [
            'multiple' => true,
            'noselectionstring' => get_string('no_selection_course', 'block_dllearningpath'),
        ];
        $mform->addElement('autocomplete', 'course_sets', get_string("courses"), $courses, $options);
        $mform->addRule('course_sets', get_string('required', 'block_dllearningpath'), 'required', 'null', 'client');

        // Add a select box.
        $paths = blocks\dllearningpath\locallib\dllearning_paths_get_path();
        $mform->addElement('select', 'learning_path_id', get_string("pluginname", 'block_dllearningpath'), $paths);

        $mform->addElement('hidden', 'update', 'false');
        $mform->setType('update', PARAM_INT);
        $mform->addElement('hidden', 'hidden_learning_path_id', '');
        $mform->setType('hidden_learning_path_id', PARAM_INT);
        $mform->disabledIf('learning_path_id', 'update', 'neq', 'false');
        // Add standard buttons.
        $this->add_action_buttons();
        $mform->setDefault('hidden_learning_path_id', $this->defaultData->learning_path_id ?? '');
    }
}
