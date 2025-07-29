<?php

defined('MOODLE_INTERNAL') || die();

use plugin_renderer_base;
use local_dl_selfenrol_info\selfenrol_manager;

class local_dl_selfenrol_info_renderer extends plugin_renderer_base {
    /**
     * Render the course banner using the Mustache template.
     *
     * @param int $courseid
     * @param array $forms
     * @return string
     */
    public function render_course_banner($courseid, $forms) {
        global $DB;

        // Fetch course data
        $course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
        $whatlearn = selfenrol_manager::get_whatlearn_data($courseid);
        $content = selfenrol_manager::get_content_data($courseid);

        // Prepare data for the template
        $courseduration = get_string('undefinedduration', 'theme_dlcourseflix');
        $field_duration = $DB->get_record('course_info_field', ['shortname' => 'duracion']);
        if ($field_duration) {
            $get_duration = $DB->get_record('course_info_data',
                                            ['fieldid'  => isset($field_duration->id) ? $field_duration->id : null,
                                             'courseid' => $courseid]);
            if ($get_duration) {
                $courseduration = $get_duration->data;
            }
        }

        $adminForm = "<form style='display: none' id='edit-{{pluginenrol}}' method='post' 
                      action='/local/dl_selfenrol_info/add.php?courseid=$courseid&action=update'>
                      <input type='hidden' name='enrolmethod' value='{{pluginenrol}}'>
                      <label for='dlenroltype'>Nombre del método</label>
                      <input style='display: inline; width: unset' class='form-control' required name='enroltype' id='dlenroltype' type='text'>
                      <label for='dlactionbutton'>Texto del boton</label>
                      <input style='display: inline; width: unset'  class='form-control' required name='dlactionbutton' id='dlactionbutton' type='text'>
                      <input type='submit' value='Actualizar'>
                      </form>";

        $adminScript = "<script>
                        let icon{{pluginenrol}} = document.getElementById('ico-{{pluginenrol}}');
                        let form{{pluginenrol}} = document.getElementById('edit-{{pluginenrol}}');

                        if (icon{{pluginenrol}}) {
                        icon{{pluginenrol}}.addEventListener('click', function() {
                        if (form{{pluginenrol}}) {
                        if (form{{pluginenrol}}.style.display === 'none'){
                        form{{pluginenrol}}.style.display = 'block';
                        }else{
                        form{{pluginenrol}}.style.display = 'none';
                        }
                        }
                        });
                        }
                        </script>";

        foreach ($forms as &$form) {
            $enroltype = $form['enroltype'];
            $adminForm = str_replace('{{pluginenrol}}', $form['enroltype'], $adminForm);
            $form['enroltype'] = get_config("local_dl_selfenrol_info", "dl_$enroltype") ??
                                 get_string('pluginname', $form['enroltype']);
            $butonText = get_config('local_dl_selfenrol_info', "dlbutton_$enroltype");
            $form['form'] = str_replace('Matricularme', $butonText, $form['form']);

            if (has_capability('moodle/course:update', context_course::instance($courseid))) {
                $form['form'] .= $adminForm;
                $form['editForm'] =
                    "<i style='cursor: pointer' id='ico-$enroltype' class='icon fa fa-edit' aria-hidden='true'></i>";
                $adminScript = str_replace('{{pluginenrol}}', $enroltype, $adminScript);
                $form['form'] .= $adminScript;
            }
        }

        $data = [
            'bannerimage'    => course_get_image($course->id), // Add logic to fetch the banner image URL if available
            'coursefullname' => $course->fullname,
            'courseduration' => $courseduration, // Add logic to fetch the course duration if available
            'coursesummary'  => strip_tags($course->summary),
            'courseurl'      => new moodle_url('/course/view.php', ['id' => $courseid]),
            'forms'          => array_values($forms), // Pass the forms to the template
            'content'        => $content, // Add content data to the template
            'whatlearn'      => $whatlearn, // Add whatlearn data to the template
        ];

        // Merge content data if available
        if ($content) {
            $data = array_merge($data, $content);
        }

        return $this->render_from_template('local_dl_selfenrol_info/selfenrol_view', $data);
    }
}
