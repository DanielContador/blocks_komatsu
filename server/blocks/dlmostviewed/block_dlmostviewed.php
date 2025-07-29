<?php

/**
 *
 * @package   block_dlmostviewed
 * @copyright dl
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_dlmostviewed extends block_base {
    /**
     * @throws coding_exception
     */
    function init() {
        $this->title = get_string('pluginname', 'block_dlmostviewed');
        $this->content = new stdClass();
        $this->content->text = '';
    }

    function get_content(): ?stdClass {

        global $OUTPUT;
        if ($this->content->text !== '') {
            return $this->content;
        }

        $props = array('props' =>
                           json_encode(
                               array(
                                   'coursesCount' => $this->config->coursecount ?? 4
                               ))
        );
        $mostviewed = new \totara_tui\output\component('dlmostviewed/pages/MostViewed', $props);
        $this->content->text = $OUTPUT->render($mostviewed);
        return $this->content;
    }

    function has_config(): bool {
        return true;
    }
}