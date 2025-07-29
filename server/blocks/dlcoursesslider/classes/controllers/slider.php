<?php

namespace blocks\dlcoursesslider\classes\controllers;

use context;
use moodle_url;
use totara_mvc\controller;
use totara_mvc\tui_view;

class slider extends controller {
    /**
     * @return tui_view
     * @throws \coding_exception
     */
    public function action(): tui_view {
        $this->set_url(new moodle_url('/block/coursesslider/block_coursesslider.php.php'));

        // Return the Tui page component we want to render when we use this controller
        return tui_view::create('dl/pages/CoursesSlider');
    }

    protected function setup_context(): context {
        return \context_system::instance();
    }
}

