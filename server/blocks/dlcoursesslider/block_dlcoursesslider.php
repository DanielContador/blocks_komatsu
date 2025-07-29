<?php

/**
 *
 * @package   block_coursesslider
 * @copyright dl
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_dlcoursesslider extends block_base {
    /**
     * @throws coding_exception
     */
    function init() {
        $this->title = get_string('pluginname', 'block_dlcoursesslider');
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
        $slider = new \totara_tui\output\component('dl/pages/CoursesSlider', $props);
        $this->content->text = $OUTPUT->render($slider);
        return $this->content;
    }

    function has_config(): bool {
        return true;
    }
}