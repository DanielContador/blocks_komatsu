<?php
/*
 * This file is part of Totara LMS
 *
 * Copyright (C) 2010 onwards Totara Learning Solutions LTD
 * Copyright (C) 1999 onwards Martin Dougiamas
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Russell England <russell.england@catalyst-eu.net>
 * @package totara
 * @subpackage reportbuilder
 */

use totara_core\advanced_feature;

// use MoodleExcelFormat;

defined('MOODLE_INTERNAL') || die();

global $CFG;

/**
 * A report builder source for Certifications
 */
class rb_source_dllearning_path extends rb_base_source {
    use \core_course\rb\source\report_trait;
    use \totara_job\rb\source\report_trait;
    use \totara_reportbuilder\rb\source\report_trait;

    /**
     * Constructor
     */
    public function __construct(rb_global_restriction_set $globalrestrictionset = null) {

        // Remember the active global restriction set.
        $this->globalrestrictionset = $globalrestrictionset;

        $this->base = '{dl_learning_path}';
        $this->joinlist = $this->define_joinlist();
        $this->columnoptions = $this->define_columnoptions();
        $this->filteroptions = $this->define_filteroptions();
        $this->contentoptions = $this->define_contentoptions();
        $this->paramoptions = $this->define_paramoptions();
        $this->defaultcolumns = $this->define_defaultcolumns();
        $this->defaultfilters = $this->define_defaultfilters();
        $this->sourcetitle = get_string('sourcetitle', 'rb_source_dllearning_path');
        $this->sourcesummary = get_string('sourcesummary', 'rb_source_dllearning_path');
        $this->sourcelabel = get_string('sourcelabel', 'rb_source_dllearning_path');
        parent::__construct();
    }

    /**
     * Global report restrictions are implemented in this source.
     *
     * @return boolean
     */
    public function global_restrictions_supported() {
        return true;
    }

    //
    //
    // Methods for defining contents of source
    //
    //

    /**
     * Creates the array of rb_join objects required for this->joinlist
     *
     * @return array
     * @global object $CFG
     */
    protected function define_joinlist() {//$name, $type, $table, $conditions, $relation=null, $dependencies='base'
        $joinlist = array();
        return $joinlist;
    }

    /**
     * Creates the array of rb_column_option objects required for
     * $this->columnoptions
     *
     * @return array
     */
    protected function define_columnoptions() { // ($type, $value, $name, $field, $options = array())
        $columnoptions = array(
            new rb_column_option(
                'template',
                'name',
                get_string('name', 'block_dllearningpath'),
                'name'
            ),
            new rb_column_option(
                'template',
                'cohorts',
                get_string('cohort', 'block_dllearningpath'),
                'cohorts',
                array('displayfunc' => 'cohorts_name')
            ),
            new rb_column_option(
                'template',
                'count_stage',
                get_string('stages', 'block_dllearningpath'),
                '(SELECT COUNT(learning_path_id) FROM {dl_learning_path_stage} ps WHERE ps.learning_path_id = base.id)'
            ),
            new rb_column_option(
                'template',
                'actions',
                get_string('actions', 'totara_reportbuilder'),
                'name, base.id',
                array(
                    'displayfunc' => 'template_actions',
                    'noexport' => false,
                    'nosort' => true,
                    'graphable' => false,
                    'extrafields' => array(
                        'name' => 'base.name',
                    )
                )
            )
        );

        return $columnoptions;
    }

    /**
     * Creates the array of rb_filter_option objects required for $this->filteroptions
     *
     * @return array
     */
    protected function define_filteroptions() {
        $filteroptions = array();
        return $filteroptions;
    }

    /**
     * Creates the array of rb_content_option object required for $this->contentoptions
     *
     * @return array
     */
    protected function define_contentoptions() {
        $contentoptions = array();
        return $contentoptions;
    }

    protected function define_paramoptions() {
        $paramoptions = array();
        return $paramoptions;
    }

    protected function define_defaultcolumns() {
        $defaultcolumns = array();
        return $defaultcolumns;
    }

    protected function define_defaultfilters() {
        $defaultfilters = array();
        return $defaultfilters;
    }

    function rb_display_cohorts_name($row) {
        global $DB;
        $cohorts = json_decode($row);
        $cohort_name = '';
        if (!empty($cohorts)) {
            $cont = sizeof($cohorts);
            foreach ($cohorts as $cohort) {
                $separator = $cont == 1 ? '' : ', ';
                $cont--;
                $cohort_name .= $DB->get_record('cohort', array('id' => (int) $cohort), 'name')->name . $separator;
            }
        }
        return $cohort_name;
    }

    function rb_display_template_actions($item, $row) {
        global $USER, $OUTPUT;

        $buttons = array();

        // Add edit action icon but prevent editing of admins by non-admin users.
        if (is_siteadmin($USER) || has_capability('moodle/site:config', context_system::instance())) {
            $title = get_string('editrecord', 'totara_reportbuilder', $row->name);
            $buttons[] = \html_writer::link(
                new \moodle_url('/blocks/dllearningpath/add_learning_path.php', array('path_id' => $item)),
                $OUTPUT->flex_icon('settings', array('alt' => $title)),
                array('title' => $title)
            );

            $title = get_string('deleterecord', 'totara_reportbuilder', $row->name);
            $buttons[] = \html_writer::link(
                new \moodle_url('/blocks/dllearningpath/list_learning_path.php', array('delete' => $item)),
                $OUTPUT->flex_icon('delete', array('alt' => $title)),
                array('title' => $title, 'class' => 'delete_learning_path')
            );
        }

        if ($buttons) {
            return implode('', $buttons);
        } else {
            return '';
        }
    }
}
