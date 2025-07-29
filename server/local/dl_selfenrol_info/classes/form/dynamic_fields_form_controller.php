<?php
namespace local_dl_selfenrol_info\form;

use totara_form\form_controller;
use totara_form\form;

class dynamic_fields_form_controller extends form_controller {
    /** @var form $form */
    protected $form;

    /**
     * Obtiene la instancia del formulario.
     * 
     * @param string|false $idsuffix string para la instancia del formulario, false en el primer acceso
     * @return form
     */
    public function get_ajax_form_instance($idsuffix) {
        // Comprobación de permisos
        require_login();
        require_sesskey();
        
        $courseid = optional_param('courseid', 0, PARAM_INT);        
        $currentdata['courseid'] = $courseid;
        $mform = new dynamic_fields_form($currentdata);
        // Crear la instancia del formulario
        $this->form = $mform;

        return $this->form;
    }

    /**
     * Procesa los datos enviados por el formulario.
     * 
     * @return array datos procesados
     */
    public function process_ajax_data() {
        parent::process_ajax_data();

        // Lógica adicional para procesar los datos si es necesario.
        return array();
    }
}
