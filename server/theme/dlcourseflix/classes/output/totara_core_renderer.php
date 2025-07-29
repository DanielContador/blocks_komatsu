<?php
/*
*/

defined('MOODLE_INTERNAL') || die;
require_once($CFG->dirroot . "/totara/core/renderer.php");

class theme_dlcourseflix_totara_core_renderer extends totara_core_renderer {
    /**
     * Render the masthead.
     *
     * @return string the html output
     */
    public function masthead(bool $hasguestlangmenu = true, bool $nocustommenu = false) {
        global $USER, $DB, $PAGE;

        if ($nocustommenu || !empty($this->page->layout_options['nototaramenu']) || !empty($this->page->layout_options['nocustommenu'])) {
            // No totara menu, or the old legacy no custom menu, in which case DO NOT generate the totara menu, its costly.
            $mastheadmenudata = new stdClass;
        } else {
            $menudata = totara_build_menu();
            $mastheadmenu = new totara_core\output\masthead_menu($menudata);
            $mastheadmenudata = $mastheadmenu->export_for_template($this->output);
        }

        $mastheadlogo = new totara_core\output\masthead_logo();

        $mastheaddata = new stdClass();
        $mastheaddata->masthead_lang = $hasguestlangmenu && (!isloggedin() || isguestuser()) ? $this->output->language_select() : '';
        $mastheaddata->masthead_logo = $mastheadlogo->export_for_template($this->output);
        $mastheaddata->masthead_menu = $mastheadmenudata;
        $mastheaddata->masthead_plugins = $this->output->navbar_plugin_output();
        $mastheaddata->masthead_search = $this->output->search_box();
        // Even if we don't have a "navbar" we need this option, due to the poor design of the nonavbar option in the past.
        $mastheaddata->masthead_toggle = $this->output->navbar_button();
        $mastheaddata->masthead_usermenu = $this->output->user_menu();
 
        if (totara_core\quickaccessmenu\factory::can_current_user_have_quickaccessmenu()) {
            $menuinstance = totara_core\quickaccessmenu\factory::instance($USER->id);

            if (!empty($menuinstance->has_possible_items())) {
                $mastheaddata->masthead_quickaccessmenu = true;
            }
        }

        //Get userInfo object, param user->id
        if($USER->id)
        {
            $userinfo = $DB->get_record('user', array('id' => $USER->id));

            //name user logged
            $name = $userinfo->firstname . ' ' . $userinfo->lastname;

            //Profile image user logged
            $user_picture = new user_picture($USER);
            $user_picture->size = 64;
            $picture = $user_picture->get_url($PAGE);

            $mastheaddata->user_info = [ 'image_profile' => $picture,  
                                        'user_name' => $name];
        }

        return $this->render_from_template('totara_core/masthead', $mastheaddata);
    }
}