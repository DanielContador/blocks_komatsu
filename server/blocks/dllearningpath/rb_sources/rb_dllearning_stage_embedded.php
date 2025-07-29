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
class rb_dllearning_stage_embedded extends rb_base_embedded {
    public $url, $source, $fullname, $filters, $columns;
    public $contentmode, $embeddedparams;
    public $hidden, $accessmode, $accesssettings, $shortname;
    public $defaultsortcolumn, $defaultsortorder;

    public function __construct($data) {
        $this->url = '/blocks/dllearningpath/list_learning_stage.php';
        $this->source = 'dllearning_stage'; // Source report not database table
        $this->defaultsortcolumn = 'base_order';
        $this->shortname = 'dllearning_stage';
        $this->fullname = get_string('stage_list', 'block_dllearningpath');
        $this->columns = array(
            array(
                'type' => 'template',
                'value' => 'order',
            ),
            array(
                'type' => 'template',
                'value' => 'name',
            ),
            array(
                'type' => 'template',
                'value' => 'courses',
            ),
            array(
                'type' => 'template',
                'value' => 'actions',
            )
        );

        // no restrictions
        $this->contentmode = REPORT_BUILDER_CONTENT_MODE_NONE;

        $this->embeddedparams = array();

        parent::__construct();
    }

    public function is_capable($reportfor, $report) {
        return has_capability('moodle/course:update', \context_system::instance());
    }
}

