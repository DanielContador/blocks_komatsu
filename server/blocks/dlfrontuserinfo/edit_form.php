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
 * Form for editing badges block instances.
 *
 * @package    block_dlfrontuserinfo
 * @copyright  2012 onwards Totara Learning Solutions Ltd {@link http://www.totaralms.com/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Rey Manuel Lazo Brito
 */

class block_dlfrontuserinfo_edit_form extends block_edit_form {

    protected function specific_definition($mform) {

        $options = array('1' => get_string('showbadgesyes', 'block_dlfrontuserinfo'), '0' => get_string('showbadgesnot', 'block_dlfrontuserinfo'));

        $mform->addElement('select', 'config_showbadges', get_string('showbadges', 'block_dlfrontuserinfo'), $options);
        $mform->setDefault('config_showbadges', '1');
        $mform->setType('config_showbadges', PARAM_INT);        
    }
}