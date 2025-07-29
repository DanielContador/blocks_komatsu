<?php


namespace theme_dlcourseflix\form;

use totara_form\form\element\text;
use totara_form\form\element\textarea;
use totara_form\form\group\section;
use totara_form\form\element\action_button;
use totara_form\form\element\select;
use totara_form\form\clientaction\onchange_reload;
use totara_form\form\element\filemanager;


class additionalsettings_form extends \totara_form\form {
        
    protected function definition() {
        global $DB, $USER;

        $additionalsettings_section = $this->model->add(new section('generalhdr', get_string('dlcourseflixthemeextrasettings', 'theme_dlcourseflix')));
        
        $tenants = $DB->get_records('tenant', array());
        $tenant_options = array(0 => get_string('sitebranding', 'theme_dlcourseflix'));
        foreach($tenants as $tenant) {
            $tenant_options[$tenant->id] = $tenant->name;
        }
        
        $tenant = $additionalsettings_section->add(
            new select('tenantid', get_string('brand', 'theme_dlcourseflix'), $tenant_options)
        );

        $current_tenant = $tenant->get_data()['tenantid'];
        $this->model->add_clientaction(new onchange_reload($tenant));

        // Load config from tenant
        $additionalsettings_section->add(
            new textarea('tenant_'.$current_tenant.'_column1', get_string('column1', 'theme_dlcourseflix'), PARAM_CLEANHTML)
        );

        $additionalsettings_section->add(
            new textarea('tenant_'.$current_tenant.'_column2', get_string('column2', 'theme_dlcourseflix'), PARAM_CLEANHTML)
        ); 

        $additionalsettings_section->add(
            new textarea('tenant_'.$current_tenant.'_column3', get_string('column3', 'theme_dlcourseflix'), PARAM_CLEANHTML)
        ); 

        $additionalsettings_section->add(new filemanager('tenant_'.$current_tenant.'_footerlogo_filemanager', get_string('footerlogo', 'theme_dlcourseflix'), [
                                                        'accept' => ['web_image'],
                                                        'maxfiles' => 1,
                                                        'subdirs' => 0,
                                                        'context' => \context_system::instance()
                                                    ]));

        $acttion_button = $additionalsettings_section->add(new action_button('saveconfig', get_string('savechanges'), action_button::TYPE_SUBMIT));
    }


    protected function validation(array $data, array $files) {
        $errors = [];

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
        return new additionalsettings_form_controller;
    }
}
