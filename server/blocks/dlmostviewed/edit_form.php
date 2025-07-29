<?php

/**
 * Formulario para editar instancias del bloque dlmostviewed.
 *
 * @package    block_dlmostviewed
 * @copyright  2025 dl
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 o posterior
 */

class block_dlmostviewed_edit_form extends block_edit_form {

    protected function specific_definition($mform) {
        $mform->addElement('text', 'config_coursecount', get_string('coursecount', 'block_dlmostviewed'));
        $mform->setType('config_coursecount', PARAM_INT);
        $mform->setDefault('config_coursecount', 4);
        $mform->addRule('config_coursecount', get_string('numericonly', 'block_dlmostviewed'), 'numeric', null, 'client');
        $mform->addRule('config_coursecount', get_string('maximumallowed', 'block_dlmostviewed'), 'regex', '/^[2-9]$|^10$/', 'client');
    }
}
