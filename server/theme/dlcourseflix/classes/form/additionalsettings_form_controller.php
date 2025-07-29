<?php

namespace theme_dlcourseflix\form;
 
use totara_form\form,
    totara_form\form_controller;
  
class additionalsettings_form_controller extends form_controller {
    /** @var form $form */
    protected $form;
 
    /**
     * @param string|false $idsuffix string for already submitted form, false on initial access
     * @return form
     */
    public function get_ajax_form_instance($idsuffix) {
        // Access control checks.
        require_login();
        require_sesskey();
        require_capability('moodle/site:config', \context_system::instance());
 
        $tenantid = required_param('tenantid', PARAM_INT);
        $theme_config = \theme_config::load('dlcourseflix');
        $theme_settings = new \theme_dlcourseflix_additionalsettings($theme_config, $tenantid);
        $currentdata = $theme_settings->get_rawsettings();
        $this->form = new additionalsettings_form($currentdata);
 
        return $this->form;
    }
    
    /**
     * Process the submitted form.
     *
     * @return array processed data
     */
    public function process_ajax_data() {
        parent::process_ajax_data();
        return array();
    }
}