<?php

/**
 * Formulario para editar instancias del bloque dlcoursesslider.
 *
 * @package    block_dlcoursesslider
 * @copyright  2025 dl
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 o posterior
 */

class block_dlcoursesslider_edit_form extends block_edit_form {

    protected function specific_definition($mform) {
        $mform->addElement('text', 'config_coursecount', get_string('coursecount', 'block_dlcoursesslider'));
        $mform->setType('config_coursecount', PARAM_INT);
        $mform->setDefault('config_coursecount', 4);
        $mform->addRule('config_coursecount', get_string('numericonly', 'block_dlcoursesslider'), 'numeric', null, 'client');
        $mform->setDefault('config_coursecount', 4);
        $mform->addRule('config_coursecount', get_string('maximumallowed', 'block_dlcoursesslider'), 'regex', '/^[2-9]$|^10$/',
                        'client');
    }
}
