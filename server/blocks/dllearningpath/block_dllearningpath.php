<?php

use blocks\dllearningpath\classes\learningpaths as learningpaths;

require_once __DIR__ . '/classes/learningpaths.php';

class block_dllearningpath extends block_base {
    public function init() {
        $this->title = get_string('pluginname', 'block_dllearningpath');
        $this->config = new stdClass();
        $this->content = new stdClass();
    }

    public function has_config() {
        return true;
    }

    public function get_content() {
        global $PAGE;
        $output = $PAGE->get_renderer('core');

        if (!empty(get_object_vars($this->content))) {
            return $this->content;
        }

        $this->content->text = '';

        $body = new stdClass();
        $learningPaths = new learningpaths();
        if ($learningPaths->get_user_path() == 0 || !$learningPaths->get_body_stages()) {
            $this->content->text = '';
            return $this->content;
        }
        $body->odd = $learningPaths->get_odd();
        $body->even = $learningPaths->get_even();
        $body->stages = $learningPaths->get_body_stages();

        if (sizeof($body->stages) == 1) {
            $body->single = "style= position:static";
        }

        $cont = 0;
        $tmp_even = [];
        $prevCompleted = array();
        foreach ($body->stages as $stage) {
            if ($cont % 2 == 0) {
                $stage->class = 'odd-vector';

                if ($learningPaths->is_stage_completed($stage)) {
                    $stage->active = $stage->active_stage;
                    $prevCompleted[] = true;
                } else {
                    $stage->active = false;
                    $prevCompleted[] = false;
                }

                if ($cont == 0) {
                    $stage->active_elipse = true; // El primero siempre está activo
                } else if ($prevCompleted[$cont - 1]) {
                    $stage->active_elipse = true; // Si la etapa anterior está completa
                } else {
                    $stage->active_elipse = false;
                }

                $stage->elipse = "bottom:82px";
                $tmp_even[] = $stage;
            } else {
                $stage->class = 'even-vector';

                if ($learningPaths->is_stage_completed($prevCompleted[$cont - 1])) {
                    $stage->active = $stage->active_stage;
                } else {
                    $stage->active = false;
                }

                if ($cont == 0) {
                    $stage->active_elipse = true; // El primero siempre está activo
                } else if ($prevCompleted[$cont - 1]) {
                    $stage->active_elipse = true; // Si la etapa anterior está completa
                } else {
                    $stage->active_elipse = false;
                }

                $stage->elipse = "top:80px;";
                $tmp_even[] = $stage;
            }
            $cont++;
        }

        unset($tmp_even[array_key_last($tmp_even)]);
        $body->stages = $tmp_even;
        $this->content->text = $output->render_from_template('block_dllearningpath/body', $body);

        return $this->content;
    }

    public function instance_allow_multiple() {
        return false;
    }

}
