<?php
/*
*/
//namespace theme_dl\output;

defined('MOODLE_INTERNAL') || die;
require_once($CFG->dirroot . "/course/renderer.php");
require_once($CFG->dirroot . "/mod/quiz/lib.php");
require_once($CFG->dirroot . '/mod/quiz/accessmanager.php');
require_once($CFG->dirroot . '/mod/quiz/attemptlib.php');

class theme_dlcourseflix_core_course_renderer extends \core_course_renderer {

    /**
     * Renders HTML to display one course module in a course section
     *
     * This includes link, content, availability, completion info and additional information
     * that module type wants to display (i.e. number of unread forum posts)
     *
     * This function calls:
     * {@link core_course_renderer::course_section_cm_name()}
     * {@link core_course_renderer::course_section_cm_text()}
     * {@link core_course_renderer::course_section_cm_availability()}
     * {@link core_course_renderer::course_section_cm_completion()}
     * {@link course_get_cm_edit_actions()}
     * {@link core_course_renderer::course_section_cm_edit_actions()}
     *
     * @param stdClass $course
     * @param completion_info $completioninfo
     * @param cm_info $mod
     * @param int|null $sectionreturn
     * @param array $displayoptions
     * @return string
     */
    public function course_section_cm($course, &$completioninfo, cm_info $mod, $sectionreturn, $displayoptions = array()) {
        $output = '';

        // We return empty string (because course module will not be displayed at all)
        // if:
        // 1) The activity is not visible to users
        // and
        // 2) The 'availableinfo' is empty, i.e. the activity was
        //     hidden in a way that leaves no info, such as using the
        //     eye icon.
        if (!$mod->uservisible && empty($mod->availableinfo)) {
            return $output;
        }

        $indentclasses = 'mod-indent';
        if (!empty($mod->indent)) {
            $indentclasses .= ' mod-indent-'.$mod->indent;
            if ($mod->indent > 15) {
                $indentclasses .= ' mod-indent-huge';
            }
        }

        $output .= html_writer::start_tag('div');

        if ($this->page->user_is_editing()) {
            $output .= course_get_cm_move($mod, $sectionreturn);
        }

        $output .= html_writer::start_tag('div', array('class' => 'mod-indent-outer'));

        // This div is used to indent the content.
        $output .= html_writer::div('', $indentclasses);

        // Start a wrapper for the actual content to keep the indentation consistent
        $output .= html_writer::start_tag('div');

        $modicons = '';
        if ($this->page->user_is_editing()) {
            $editactions = course_get_cm_edit_actions($mod, $mod->indent, $sectionreturn);
            $modicons .= ' '. $this->course_section_cm_edit_actions($editactions, $mod, $displayoptions);
            $modicons .= $mod->afterediticons;
        }

        $modicons .= $this->course_section_cm_completion($course, $completioninfo, $mod, $displayoptions);

        if (!empty($modicons)) {
            $output .= html_writer::span($modicons, 'actions');
        }

        // Display the link to the module (or do nothing if module has no url)
        $cmname = $this->course_section_cm_name($mod, $displayoptions);

        if (!empty($cmname)) {
            // Start the div for the activity title, excluding the edit icons.
            $activityclasses = 'activityinstance';
            $output .= html_writer::start_tag('div', array('class' => $activityclasses));
            $output .= $cmname;


            // Module can put text after the link (e.g. forum unread)
            $output .= $mod->afterlink;

            // Se obtiene el contexto del módulo y se comprueba la capacidad para ver actividades ocultas
            $modcontext = context_module::instance($mod->id);
            $viewhiddenactivities = has_capability('moodle/course:viewhiddenactivities', $modcontext);

            if (!$mod->available || ($mod->available && $viewhiddenactivities)) {
                // If there is content but NO link (eg label), then display the
                // content here (BEFORE any icons). In this case cons must be
                // displayed after the content so that it makes more sense visually
                // and for accessibility reasons, e.g. if you have a one-line label
                // it should work similarly (at least in terms of ordering) to an
                // activity.
                $moduleoutput = '';
                $contentpart = $this->course_section_cm_text($mod, $displayoptions);
                $url = $mod->url;
                if (!empty($url)) {
                    $moduleoutput .= $contentpart;
                }
                $moduleoutput .= $this->course_section_cm_availability($mod, $displayoptions);
                if ($moduleoutput) {
                    // Se genera un ID único para el contenido colapsable
                    $collapseId = 'collapse_mod_' . $mod->id;

                    // Se crea el botón que actuará como disparador del contenido colapsable
                        $toggleButton = html_writer::start_tag('a', array(

                            'class' => 'collapsible dl-flex-adaptable',
                            'id'    => 'toggle_' . $collapseId
                        ));
                        // Se incluye un icono (asumiendo uso de Font Awesome) y el texto "Más"
                        $toggleButton .= '<span class="icon-toggle"><i class="fa fa-plus"></i></span> 
                                          <span class="icon-text">Más</span>';
                        $toggleButton .= html_writer::end_tag('a');

                        // Se crea el contenedor para el contenido que se mostrará/ocultará, inicialmente oculto
                        $collapsibleContent = html_writer::start_tag('div', array(
                            'id'    => $collapseId,
                            'class' => 'content'
                        ));
                        $collapsibleContent .= $moduleoutput;
                        $collapsibleContent .= html_writer::end_tag('div');

                    // Se agrega el botón y el contenido al output final
                    $output .= $toggleButton;
                    $output .= $collapsibleContent;
                }
            }

            // Adding the "Ir" button with a language string
            if($mod->uservisible && $mod->url) {	
                $buttonText = get_string('go_to_activity', 'format_dlformatcourseflix');
                $output .= html_writer::link($mod->url, $buttonText, array('class' => 'btn btn-secundary go-to-activity'));
            }
            else {
                $buttonText = get_string('notavailable', 'theme_dlcourseflix');
                $output .= html_writer::tag('span', $buttonText, array('class' => 'dl-notavailable-label'));
            }

            // Closing the tag which contains everything but edit icons. Content part of the module should not be part of this.
            $output .= html_writer::end_tag('div'); // .activityinstance
        }

        // If there is content but NO link (eg label), then display the
        // content here (BEFORE any icons). In this case cons must be
        // displayed after the content so that it makes more sense visually
        // and for accessibility reasons, e.g. if you have a one-line label
        // it should work similarly (at least in terms of ordering) to an
        // activity.
        $contentpart = $this->course_section_cm_text($mod, $displayoptions);
        $url = $mod->url;
        if (empty($url)) {
            $output .= $contentpart;
        }

        $output .= html_writer::end_tag('div'); // $indentclasses

        // End of indentation div.
        $output .= html_writer::end_tag('div');

        $output .= html_writer::end_tag('div');



        return $output;
    }
}
