<?php
namespace local_dl_selfenrol_info\form;

use totara_form\form;
use totara_form\form\element\text;
use totara_form\form\element\textarea;
use totara_form\form\element\select;
use totara_form\form\group\section;
use totara_form\form\element\static_html;
use totara_form\form\element\action_button;
use totara_form\form\element\hidden;
use totara_form\form\clientaction\onchange_reload;

class dynamic_fields_form extends form {
    protected function definition() {
        // Add a hidden field for courseid
        $this->model->add(new hidden('courseid', PARAM_INT));

        // Section 1: "Qué aprenderás?" (What you'll learn)
        $section1 = $this->model->add(new section('whatlearnsection', get_string('whatlearn', 'local_dl_selfenrol_info')));
        $section1->set_expanded(true);
        $section1->add(new textarea('whatlearn', get_string('text', 'local_dl_selfenrol_info'), PARAM_CLEANHTML));

        // Section 2: "Contenido" (Content) with dynamic fields container and add content button
        $section2 = $this->model->add(new section('content', get_string('content', 'local_dl_selfenrol_info')));
        $section2->set_expanded(true);
        
        // Add a select field for numfields with options from 1 to 10
        $numfields_options = array_combine(range(1, 10), range(1, 10));
        $numfields = $section2->add(new select('numfields', get_string('numfields', 'local_dl_selfenrol_info'), $numfields_options));
        $this->model->add_clientaction(new onchange_reload($numfields));

        $numfields_value = $numfields->get_data()['numfields'] ?? 0;

        // Add the dynamic fields based on the submitted data
        for ($i = 0; $i < $numfields_value; $i++) {
            $section2->add(new static_html("header_$i", '<h4>'.get_string('content_field', 'local_dl_selfenrol_info').'</h4>', null));
            $section2->add(new text("content_title_$i", get_string('content_title', 'local_dl_selfenrol_info')." ".($i+1), PARAM_TEXT));
            $section2->add(new textarea("content_text_$i", get_string('content_text', 'local_dl_selfenrol_info')." ".($i+1), PARAM_CLEANHTML));
        }

        // Add a global submit button to the form
        $this->model->add(new action_button('addselfenroldata', get_string('save', 'totara_core'), action_button::TYPE_SUBMIT));
    }

    protected function validation(array $data, array $files) {
        $errors = parent::validation($data, $files);
        
        // Form submission fails unless $errors is empty.
        return $errors;
    }

    /**
     * Returns true if the form should be initialised in JS.
     *
     * @return bool
     */
    public static function initialise_in_js() {
        return true;
    }

    /**
     * Returns class responsible for form handling.
     * This is intended especially for ajax processing.
     *
     * @return null|form_controller
     */
    public static function get_form_controller() {
        return new dynamic_fields_form_controller();
    }
}
