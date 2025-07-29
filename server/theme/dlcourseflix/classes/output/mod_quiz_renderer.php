<?php
defined('MOODLE_INTERNAL') || die();

// Asegúrate de incluir el renderer original.
require_once($CFG->dirroot . '/mod/quiz/renderer.php');

class theme_dlcourseflix_mod_quiz_renderer extends mod_quiz_renderer
{

    // Método sobrescrito para renderizar individualmente cada botón.
    protected function render_quiz_nav_question_button(quiz_nav_question_button $button)
    {
        $classes = array('wizard-step');
        if ($button->currentpage) {
            $classes[] = 'active';
        }
        $stepcontent = html_writer::tag('span', $button->number, array('class' => 'wizard-step-number'));
        if ($button->flagged) {
            $stepcontent .= html_writer::tag('span', get_string('flagged', 'question'), array('class' => 'wizard-flag'));
        }
        $tagattributes = array(
            'class' => implode(' ', $classes),
            'id' => $button->id,
            'title' => $button->statestring,
            'data-quiz-page' => $button->page
        );
        if ($button->url) {
            $output = html_writer::tag('a', $stepcontent, array_merge($tagattributes, array('href' => $button->url)));
        } else {
            $output = html_writer::tag('span', $stepcontent, $tagattributes);
        }

        // Agregar la barra de carga (progress bar)
        // Se define la clase en función del estado:
        // Si es la pregunta actual se usa "progress-current" para aplicar un fondo 50% blanco y 50% #3A376F (a través de CSS)
        // Si ya pasó, se asigna "progress-passed" para que la barra sea totalmente blanca.
        $progressclass = '';
        switch ($button->statestring) {
            case 'Respuesta guardada':
                $progressclass = 'progress-passed';
                break;
            case 'Pregunta actual':
                $progressclass = 'progress-current';
                break;
            case 'Correcta':
                $progressclass = 'progress-passed';
                break;
            case 'Incorrecta':
                $progressclass = 'progress-passed';
                break;
            case 'Sin contestar':
                $progressclass = 'progress-not-passed';
                break;
            default:
                $progressclass = 'progress-not-passed';
                break;
        }
        if (sizeof($classes) > 1) {
            $progressclass = 'progress-current';
        }

        // Se genera el contenedor para la barra con los estilos de posicionamiento y dimensiones indicados.
        $progressBar = html_writer::tag('div', '',
            array(
                'class' => 'progress-container ' . $progressclass,
                'style' => 'width: 65px; height: 4px; top: 8.94px; left: 372.65px; border-radius: 26px;'
            )
        );

        return $output . $progressBar;
    }


    public function summary_page_controls($attemptobj)
    {
        $output = '';
        $buttons = ''; // Variable para agrupar los botones.
        // Botón "Return to attempt" si el intento está en progreso.
        if ($attemptobj->get_state() == quiz_attempt::IN_PROGRESS) {
            $button = new single_button(
                new moodle_url($attemptobj->attempt_url(null, $attemptobj->get_currentpage())),
                get_string('returnattempt', 'quiz')
            );
            // Genera el contenedor para el botón y lo agrega a la variable $buttons.
            $buttons .= $this->container(
                $this->container($this->render($button), 'controls'),
                'submitbtns mdl-align'
            );
        }
        // Botón "Submit all and finish".
        $options = array(
            'attempt' => $attemptobj->get_attemptid(),
            'finishattempt' => 1,
            'timeup' => 0,
            'slots' => '',
            'cmid' => $attemptobj->get_cmid(),
            'sesskey' => sesskey(),
        );
        $button = new single_button(
            new moodle_url($attemptobj->processattempt_url(), $options),
            get_string('submitallandfinish', 'quiz')
        );
        $button->id = 'responseform';
        if ($attemptobj->get_state() == quiz_attempt::IN_PROGRESS) {
            $button->add_action(new confirm_action(get_string('confirmclose', 'quiz'), null,
                get_string('submitallandfinish', 'quiz')));
        }
        // Generación del mensaje de acuerdo a la fecha de entrega y estado del intento.
        /*$duedate = $attemptobj->get_due_date();
        $message = '';
        if ($attemptobj->get_state() == quiz_attempt::OVERDUE) {
            $message = get_string('overduemustbesubmittedby', 'quiz', userdate($duedate));
        } else if ($duedate) {
            $message = get_string('mustbesubmittedby', 'quiz', userdate($duedate));
        }
        // Se inserta el mensaje encima de los botones, si existe.
        if (!empty($message)) {
            // Utilizamos html_writer::tag para generar la etiqueta <p>.
            $output .= html_writer::tag('p', $message, array('class' => 'mesaje-attempt'));
        }*/
        // Agrega el botón de finalizar intento a la variable $buttons.
        $buttons .= $this->container(
            $this->container($this->render($button), 'controls'),
            'submitbtns mdl-align'
        );
        // Envuelve los botones en una DIV con la clase btn-group-quiz.
        $output .= $this->container($buttons, 'btn-group-quiz');
        // Se agrega el countdown timer (si se requiere que esté fuera de la agrupación de botones).
        $output .= $this->countdown_timer($attemptobj, time());
        return $output;
    }

    /**
     * Outputs the navigation block panel
     *
     * @param quiz_nav_panel_base $panel instance of quiz_nav_panel_base
     */
    public function navigation_panel(quiz_nav_panel_base $panel)
    {
        global $COURSE, $DB;

        $course_title = "";
        if ($COURSE->id && $COURSE->id != 1) {
            $course_title = $DB->get_field('course', 'fullname', array('id' => $COURSE->id));
        }

        $output = '';
        // Recuperamos el título desde $this->page->activity->name
        /*if ($course_title) {
            $activitytitle = '<h2>'.$course_title.'</h2>';
            $output .= html_writer::tag('div', $activitytitle, array('class' => 'activity-title'));
        }*/
        $userpicture = $panel->user_picture();
        if ($userpicture) {
            $fullname = fullname($userpicture->user);
            if ($userpicture->size === true) {
                $fullname = html_writer::div($fullname);
            }
            $output .= html_writer::tag('div', $this->render($userpicture) . $fullname,
                array('id' => 'user-picture', 'class' => 'clearfix'));
        }

        $output .= $panel->render_before_button_bits($this);

        $bcc = $panel->get_button_container_class();
        $output .= html_writer::start_tag('div', array('class' => "qn_buttons clearfix $bcc"));
        foreach ($panel->get_question_buttons() as $button) {
            $output .= $this->render($button);
        }
        $output .= html_writer::end_tag('div');

        $output .= html_writer::tag('div', $panel->render_end_bits($this),
            array('class' => 'othernav'));

        $this->page->requires->with_origin('shell', function () {
            $this->page->requires->js_init_call('M.mod_quiz.nav.init', null, false,
                quiz_get_js_module());
        });

        return $output;
    }
}
