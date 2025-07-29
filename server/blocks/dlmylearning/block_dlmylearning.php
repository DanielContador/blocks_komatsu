<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 *
 * @package   block_dlmylearning
 * @copyright 1999 onwards Martin Dougiamas (http://dougiamas.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
// use totara_core\user_learning\item_helper as learning_item_helper;

class block_dlmylearning extends block_base {
    function init() {
        $this->title = get_string('pluginname', 'block_dlmylearning');
    }

    function has_config() {
        return true;
    }

    /**
     * @throws coding_exception
     */
    function get_content() {
        global $USER, $OUTPUT;

        if ($this->content !== null) {
            return $this->content;
        }

        if (empty($this->content)) {
            $this->content = new stdClass();
        }

        // Obtener configuraciones del bloque.
        $showexpiringcourses = $this->config->showexpiringcourses ?? false;
        $showcourses = $this->config->showcourses ?? false;
        $showprograms = $this->config->showprograms ?? false;
        $showcompletedprograms = $this->config->showcompletedprograms ?? false;
        $showprogramscourses = $this->config->showprogramcourses ?? false;
        $shownotprogresscourses = $this->config->withoutprogresscourses ?? false;
        $showcompletedcoursesblock = $this->config->completedcoursesblock ?? false;
        $blocksOrder = $this->config->order ??
                       'gridInProgress,gridNotProgress,gridCompleted,gridCourses,gridPrograms';

        //El client VueJs no interpreta 0/1 como verdadero o falso
        $showexpiringcourses == 1 ? $showexpiringcourses = true : $showexpiringcourses = false;
        $showcourses == 1 ? $showcourses = true : $showcourses = false;
        $showprograms == 1 ? $showprograms = true : $showprograms = false;
        $showcompletedprograms == 1 ? $showcompletedprograms = true : $showcompletedprograms = false;
        $showprogramscourses == 1 ? $showprogramscourses = true : $showprogramscourses = false;
        $shownotprogresscourses == 1 ? $shownotprogresscourses = true : $shownotprogresscourses = false;
        $showcompletedcoursesblock == 1 ? $showcompletedcoursesblock = true : $showcompletedcoursesblock = false;

        //Limpiar el oreden y obtener las variables
        $blocksOrder = trim($blocksOrder);
        $defaultOrder = array(
            0 => 'gridInProgress',
            1 => 'gridNotProgress',
            2 => 'gridCompleted',
            3 => 'gridCourses',
            4 => 'gridPrograms'
        );
        $clearOrder = [];
        $order = explode(',', $blocksOrder);

        foreach ($order as $item) {
            $clearOrder[] = trim($item);
        }

        //Cambiar valores introducidos por el usuario a grids
        foreach ($clearOrder as &$cleared) {
            switch ($cleared) {
                case 'Cursos en proceso':
                    $cleared = 'gridInProgress';
                    break;
                case 'Cursos no iniciados':
                    $cleared = 'gridNotProgress';
                    break;
                case 'Cursos finalizados':
                    $cleared = 'gridCompleted';
                    break;
                case 'Mis cursos':
                    $cleared = 'gridCourses';
                    break;
                case 'Mis programas':
                    $cleared = 'gridPrograms';
            }
        }

        //var_dump($clearOrder);

        if (sizeof($clearOrder) < sizeof($defaultOrder)) {
            $missingItems = array_diff($defaultOrder, $clearOrder);
            $clearOrder = array_merge($clearOrder, $missingItems);
        }

        // Obtener datos dependiendo de las configuraciones
        $data = [];
        if ($showexpiringcourses) {
            $showexpiringcourses_label = $this->config->showexpiringcourseslabel ??
                                         get_string('showexpiringcourses', 'block_dlmylearning');
        }

        if ($showcourses) {
            $showcourses_label = $this->config->showcourseslabel ??
                                 get_string('my_courses', 'block_dlmylearning');
        }

        if ($showprograms) {
            $showprograms_label = $this->config->showprogramcourseslabel ??
                                  get_string('my_programs', 'block_dlmylearning');
        }

        if ($shownotprogresscourses) {
            $notprogresscourses_label = $this->config->showwithoutprogresscourseslabel ??
                                        get_string('withoutprogresscourses', 'block_dlmylearning');
        }

        if ($showcompletedcoursesblock) {
            $completedcoursesblock_label = $this->config->completedcourseslabel ??
                                           get_string('completedcourses', 'block_dlmylearning');
        }

        //Construir los bloques para el cliente con el orden de la configuración
        $blockBase = new \stdClass();
        $blockBase->displayCondition = '';
        $blockBase->label = '';
        $blockBase->design = '';
        $blockBase->handler = '';
        $blockBase->params = '';

        $displayConditions = array(
            'gridInProgress'  => $showexpiringcourses,
            'gridNotProgress' => $shownotprogresscourses,
            'gridCompleted'   => $showcompletedcoursesblock,
            'gridCourses'     => $showcourses,
            'gridPrograms'    => $showprograms
        );

        $labels = array(
            'gridInProgress'  => $showexpiringcourses_label ?? '',
            'gridNotProgress' => $notprogresscourses_label ?? '',
            'gridCompleted'   => $completedcoursesblock_label ?? '',
            'gridCourses'     => $showcourses_label ?? '',
            'gridPrograms'    => $showprograms_label ?? ''
        );

        $handler = array(
            'gridInProgress'  => 'handleInProgressLoaded',
            'gridNotProgress' => 'handleNotProgressLoaded',
            'gridCompleted'   => 'handleCompletedLoaded',
            'gridCourses'     => 'handleCompletedLoaded',
            'gridPrograms'    => 'handleProgramsLoaded'
        );

        foreach ($clearOrder as $element) {
            $blockBase->displayCondition = $displayConditions[$element];
            $blockBase->label = $labels[$element];
            $blockBase->design = $element;
            $blockBase->handler = $handler[$element];
            if ($element == 'gridCourses') {
                $blockBase->params = array('programscourses' => $showprogramscourses);
            } else if ($element == 'gridPrograms') {
                $blockBase->params = array('completedprograms' => $showcompletedprograms);
            } else {
                $blockBase->params = array();
            }

            $data[] = $blockBase;
            $blockBase = new stdClass();
        }

        // Pasar los datos al componente Vue.
        $component = new \totara_tui\output\component('dl/pages/MyLearningblock', [
            'props' => json_encode([
                                       'inprogress_label'            => $showexpiringcourses_label ?? '',
                                       'completed_label'             => $showcourses_label ?? '',
                                       'programs_label'              => $showprograms_label ?? '',
                                       'notprogresscourses_label'    => $notprogresscourses_label ?? '',
                                       'completedcoursesblock_label' => $completedcoursesblock_label ?? '',
                                       'showprogramscourses'         => $showprogramscourses,
                                       'showcompletedprograms'       => $showcompletedprograms,
                                       'notprogressblock'            => $shownotprogresscourses,
                                       'completedcoursesblock'       => $showcompletedcoursesblock,
                                       'programsblock'               => $showprograms,
                                       'coursesblock'                => $showcourses,
                                       'onprogressblock'             => $showexpiringcourses,
                                       'data'                        => $data,
                                   ]),
        ]);

        $this->content->text = $OUTPUT->render($component);

        return $this->content;
    }

    public function get_user_inprogress($userid) {
        global $DB, $CFG;

        if (empty($CFG->disable_visibility_maps)) {
            $visibility = \totara_core\visibility_controller::course()->sql_where_visible($userid, 'c');
            $visibilitysql = '';
            $visibilityparams = [];
            if (!$visibility->is_empty()) {
                $visibilitysql = ' AND ' . $visibility->get_sql();
                $visibilityparams = $visibility->get_params();
            }
        } else {
            [$visibilitysql, $visibilityparams] = totara_visibility_where($userid, 'c.id', 'c.visible', 'c.audiencevisible');
            $visibilitysql = 'AND ' . $visibilitysql;
        }

        // Parámetros para la consulta
        $params = ['userid' => $userid];
        $params = array_merge($params, $visibilityparams);

        // Campos a recuperar
        $fields = ['id', 'fullname', 'shortname', 'startdate', 'enddate'];
        $coursefields = 'c.' . join(',c.', $fields);

        // Consulta SQL para obtener todos los cursos inscritos
        $sql = "SELECT DISTINCT $coursefields, ccats.name as category
            FROM {enrol} e
            JOIN {user_enrolments} ue ON ue.enrolid = e.id
            JOIN {course} c ON c.id = e.courseid
            JOIN {context} ctx ON ctx.instanceid = c.id AND ctx.contextlevel = " . CONTEXT_COURSE . "
            JOIN {course_categories} ccats ON c.category = ccats.id
            WHERE ue.userid = :userid
            {$visibilitysql}
            ORDER BY c.fullname";
        $inprogress = $DB->get_records_sql($sql, $params);

        return $inprogress;
    }

    public function get_user_courses($userid) {
        global $DB, $CFG;

        $params = ['userid' => $userid];

        $fields = ['id',
                   'shortname', 'fullname',
                   'summary', 'category',
        ];

        $coursefields = 'c.' . join(',c.', $fields);

        // agregar filtro de categorias
        $sql = "SELECT DISTINCT $coursefields 
                    FROM {course_completions} cc
                    JOIN {user} u ON (u.id  = cc.userid AND u.deleted = 0)
                    JOIN {course} c ON cc.course = c.id
                    JOIN {context} ctx ON ctx.instanceid = c.id AND ctx.contextlevel = " . CONTEXT_COURSE . "
                    WHERE cc.userid = :userid";

        $coursesbyuser = $DB->get_records_sql($sql, $params, 0, $CFG->historical_items_limitnum);

        return $coursesbyuser;
    }

    public function get_user_programs($userid) {
        global $DB;

        // Obtener todos los programas en los que el usuario est� inscrito.
        $sql = "SELECT p.id, p.fullname
            FROM {prog} p
            JOIN {prog_completion} pc ON pc.programid = p.id
            WHERE pc.userid = :userid";
        $params = ['userid' => $userid];

        return $DB->get_records_sql($sql, $params);
    }

    public function instance_allow_multiple() {
        return true;
    }
}
