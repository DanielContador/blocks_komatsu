<?php

namespace theme_dlcourseflix\controllers;

use context;
use moodle_url;
use totara_mvc\controller;
use totara_mvc\tui_view;


class course_catalog extends controller {
	/**
	* @return tui_view
	* @throws \coding_exception
	*/
	public function action(): tui_view {
		$this->set_url(new moodle_url('/totara/catalog/index.php'));
		// Return the Tui page component we want to render when we use this controller
		return tui_view::create('dl/pages/CourseCatalog');
		// return tui_view::create('tui/components/layouts/LayoutOneColumn');
	}
	protected function setup_context(): context {
		return \context_system::instance();
	}
}
