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
 *
 * @package   block_dlcustomheader
 * @copyright dl
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_dlcustomheader extends block_base {
    /**
     * @var stdClass
     */
    private $images;

    /**
     * @throws coding_exception
     */
    function init() {
        $this->title = get_string('pluginname', 'block_dlcustomheader');
        $this->content = new stdClass();
        $this->config = new stdClass();
        $this->images = array();
        $this->content->text = '';
    }

    function has_config(): bool {
        return true;
    }

    /**
     * @throws coding_exception
     * @throws \core\exception\moodle_exception
     */
    function get_content(): ?stdClass {
        global $OUTPUT, $CFG;

        if ($this->content->text != '') {
            return $this->content;
        }

        $image = new stdClass();
        $imgUrl = new moodle_url($CFG->wwwroot . '/blocks/dlcustomheader/pix/default.png');
        $image->text = $this->config->custom_text ?? get_string('defaultText', 'block_dlcustomheader');
        $image->src = $imgUrl->out('false');
        $this->images[] = $image;

        $isSlide = false;
        $editingBlocks = $this->page->user_is_editing();

        $component = new \totara_tui\output\component('dl/pages/DlCustomHeader', [
            'props' => json_encode(
                [
                    'isSlide'       => $isSlide,
                    'images'        => $this->images,
                    'editingBlocks' => $editingBlocks
                ]),
        ]);

        $this->content->text = $OUTPUT->render($component);
        return $this->content;
    }

    function instance_can_be_collapsed(): bool {
        return false;
    }
}
