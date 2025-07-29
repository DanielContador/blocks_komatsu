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

class block_dllearningpath_add_learning_path_form extends moodleform {
    protected $defaultData;

    public function __construct($defaultData = []) {
        $this->defaultData = $defaultData;
        parent::__construct();
    }

    // Define the form.
    public function definition() {
        $mform = $this->_form;

        foreach ($this->defaultData as $key => $value) { // if have default data, set it
            $mform->setDefault($key, $value);
        }

        // Add a text field.
        $mform->addElement('text', 'name', get_string('name', 'block_dllearningpath'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', get_string('required', 'block_dllearningpath'), 'required', 'null', 'client');

        // Add a select box.
        $cohortsgroups = blocks\dllearningpath\locallib\dllearning_paths_get_cohorts();
        //$mform->addElement('select', 'group_select', get_string("groups"), $coursegroups)->setMultiple(true);
        $options = array(
            'multiple' => true,
            'noselectionstring' => get_string('no_selection_cohort', 'block_dllearningpath'),
        );
        $mform->addElement('autocomplete', 'cohorts', get_string("cohort", 'block_dllearningpath'), $cohortsgroups, $options);
        $mform->addRule('cohorts', get_string('required', 'block_dllearningpath'), 'required', 'null', 'client');

        $mform->addElement('hidden', 'update', 'false');
        $mform->setType('update', PARAM_INT);
        // Add standard buttons.
        $this->add_action_buttons();
    }
}
