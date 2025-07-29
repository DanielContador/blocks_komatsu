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
 * @package    block_dlcustomheader
 * @copyright  dl
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Orlando Noa <onoa@dl.cl>
 */
class block_dlcustomheader_edit_form extends block_edit_form {
    /**
     * @throws coding_exception
     */
    protected function specific_definition($mform) {
        $mform->addElement('textarea', 'config_custom_text', get_string('customtext', 'block_dlcustomheader'));
        $mform->setType('config_custom_text', PARAM_TEXT);
    }
}
