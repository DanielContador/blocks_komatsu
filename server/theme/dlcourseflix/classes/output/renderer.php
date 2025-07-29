<?php
/*
*/

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . "/totara/core/renderer.php");
require_once($CFG->dirroot . "/user/lib.php");

class theme_dlcourseflix_renderer extends theme_legacy_renderer {

    public function get_footer_logo_url() {
        global $OUTPUT;
        $footer_logo_url = $OUTPUT->image_url('footermark', 'theme_dlcourseflix');
        if ($footer_logo_url) {
            return $footer_logo_url->out();
        }
        return '';
    }

    public function get_white_logo_url() {
        global $USER;
        $theme_config = \theme_config::load('dlcourseflix');
        $tenant_id = empty($USER->tenantid) ? 0 : $USER->tenantid;
        if (!$tenant_id && (!isloggedin() || isguestuser())) {
            $tenant_id = \core\theme\helper::get_prelogin_tenantid();
        }

        $theme_settings = new theme_dlcourseflix_additionalsettings($theme_config, $tenant_id);
        return $theme_settings->get_currentfile_url('footerlogo');
    }

    public function dl_footer() {
        global $OUTPUT, $CFG, $PAGE, $USER;

        $theme_config = \theme_config::load('dlcourseflix');
        $tenant_id = empty($USER->tenantid) ? 0 : $USER->tenantid;
        if (!$tenant_id && (!isloggedin() || isguestuser())) {
            $tenant_id = \core\theme\helper::get_prelogin_tenantid();
        }

        $output = '';
        /*$output .= '<div id="page-footer" class="clearfix d-flex w-sm-100">';*/
        $output .= '<div id="page-footer" class="w-sm-100">';

        if (class_exists('theme_dlcourseflix_additionalsettings')) {
            $theme_settings = new theme_dlcourseflix_additionalsettings($theme_config, $tenant_id);
            $output .= '<div class="row dl-center">';
            $footer_logo = $this->get_footer_logo_url();
            // $footer_logo = $theme_settings->get_currentfile_url('footerlogo');
            $output .= '<img src="' . $footer_logo . '" alt="footer-logo" title="Footer Logo"/>';
            $output .= '</div>';

            $column1_property = $theme_settings->get_property('additional', 'column1');
            $column2_property = $theme_settings->get_property('additional', 'column2');
            $column3_property = $theme_settings->get_property('additional', 'column3');

            $output .= '<div class="footer-column">';
                $output .= '<div class="row">';
                    if (!empty($column1_property)) {
                        $output .= '<div id="column1-footer" class="dl-column-footer col-lg-4 col-xs-12">';
                        $output .= '<span>' . $column1_property['value'] . '</span>';
                        $output .= '</div>';
                    }

                    if (!empty($column2_property) && $column2_property['value']) {

                        $output .= '<div id="column2-footer" class="dl-column-footer col-lg-4 col-xs-12">';
                        $output .= '<span>' . $column2_property['value'] . '</span>';
                        $output .= '</div>';
                    }

                    if (!empty($column3_property) && $column3_property['value']) {

                        $output .= '<div id="column3-footer" class="dl-column-footer col-lg-4 col-xs-12">';
                        $output .= '<span>' . $column3_property['value'] . '</span>';
                        $output .= '</div>';
                    }
                $output .= '</div>';
            $output .= '</div>';
        }
        $output .= '</div>';
        return $output;
    }

    /**
     * Devuelve el contenido de la categoría del curso.
     *
     * @param int $categoryid ID de la categoría.
     * @param string $viewtype Tipo de vista (curso o programa).
     * @return string HTML del contenido de la categoría del curso.
     */
    public function course_category_content($categoryid, $viewtype) {
        global $DB, $OUTPUT;

        // Obtener la categoría del curso.
        $category = $DB->get_record('course_categories', array('id' => $categoryid), '*', MUST_EXIST);

        // Obtener los cursos de la categoría.
        $courses = $DB->get_records('course', array('category' => $categoryid));

        // Comenzar a construir el contenido HTML.
        $content = html_writer::start_div('course-category-content');
        $content .= html_writer::tag('h2', format_string($category->name));

        // Iterar sobre los cursos y agregar a la salida.
        foreach ($courses as $course) {
            $courseurl = new \moodle_url('/course/view.php', array('id' => $course->id));
            $coursename = format_string($course->fullname);
            $content .= html_writer::div(html_writer::link($courseurl, $coursename), 'course-item');
        }

        $content .= html_writer::end_div();

        return $content;
    }

    /**
     * Render the masthead.
     *
     * @return string the html output
     */
    public function dl_masthead1(bool $hasguestlangmenu = true, bool $nocustommenu = false, string $search_box = '') {
        global $USER, $PAGE, $DB;

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

        //Para las búsquedas por formularios
        if ($search_box === "form" ) {
            $mastheaddata->has_search_box_form = true;
            $mastheaddata->searchurl = $PAGE->url->get_path();
            $mastheaddata->method = 'POST';
            $mastheaddata->params = array(
                array('name' => 'id', 'value' => $_POST['id'] ?? $PAGE->url->param('id')),
                array('name' => 'mode', 'value' => 'search'),
                array('name' => 'fullsearch', 'value' => 1)
            );
            $mastheaddata->searchboxtext = 'hook';
            $mastheaddata->hook = $_POST['hook'] ?? '';
        }
        else if ($search_box === "js") {
            $mastheaddata->has_search_box_js = true;
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

    /**
     * Render the masthead.
     *
     * @return string the html output
     */
    public function dl_masthead2(bool $hasguestlangmenu = true, bool $nocustommenu = false, string $search_box = '') {
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

        if(isset($mastheaddata->masthead_logo))
        {
            $mastheaddata->masthead_logo['logourl'] = $this->get_white_logo_url();
        }

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

        //Para las búsquedas por formularios
        if ($search_box === "form" ) {
            $mastheaddata->has_search_box_form = true;
            $mastheaddata->searchurl = $PAGE->url->get_path();
            $mastheaddata->method = 'POST';
            $mastheaddata->params = array(
                array('name' => 'id', 'value' => $_POST['id'] ?? $PAGE->url->param('id')),
                array('name' => 'mode', 'value' => 'search'),
                array('name' => 'fullsearch', 'value' => 1)
            );
            $mastheaddata->searchboxtext = 'hook';
            $mastheaddata->hook = $_POST['hook'] ?? '';
        }
        else if ($search_box === "js") {
            $mastheaddata->has_search_box_js = true;
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
