<?php

namespace theme_dlcourseflix\controllers;

use context;
use moodle_url;
use totara_mvc\controller;
use totara_mvc\tui_view;


class program_index extends controller {
	/**
	* @return tui_view
	* @throws \coding_exception
	*/
	public function action(): tui_view {
		global $OUTPUT;

		$imagebanner = $OUTPUT->image_url('program_index_banner', 'theme_dlcourseflix');
		$categoryid = $this->get_optional_param('categoryid', 0, PARAM_INT);
		$this->set_url(new moodle_url('/totara/program/index.php',  $categoryid ? ['id' => $categoryid] : null));
		// Return the Tui page component we want to render when we use this controller
		return tui_view::create('dl/pages/ProgramsView', ['categoryid' => $categoryid,
														'bannerImage' => $imagebanner->out()]);
	}
	protected function setup_context(): context {
		return \context_system::instance();
	}
}