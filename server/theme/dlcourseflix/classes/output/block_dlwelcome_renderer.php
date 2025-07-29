<?php

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot. "/lib/outputrenderers.php" );

class block_dlwelcome_renderer extends plugin_renderer_base {
    public function init() {
        $this->title = get_string('pluginname', 'block_dlwelcome');
    }

    function has_config() {
        return true;
    }

    #TODO: Verify empty values
    function get_content() {
        global $CFG, $OUTPUT, $USER, $PAGE;
        require_once($CFG->libdir . '/filelib.php');
       
        if ($this->content !== NULL) {
            return $this->content;
        }

        $filteropt = new stdClass;
        $filteropt->overflowdiv = false;
        $filteropt->noclean = true;

        $text = '';
		$regards = '';
		$image = '';
        $this->content = new stdClass;
        $this->content->footer = '';
        if (isset($this->config->text)) {
            // rewrite url
            $this->config->text = file_rewrite_pluginfile_urls($this->config->text, 'pluginfile.php', $this->context->id, 'block_dlwelcome', 'content', NULL);
            // Default to FORMAT_HTML which is what will have been used before the
            // editor was properly implemented for the block.
            $format = FORMAT_PLAIN;
            // Check to see if the format has been properly set on the config
            if (isset($this->config->format)) {
                $format = $this->config->format;
            }
            $text = format_text($this->config->text, $format, $filteropt);
        } else {            
            $text = '';
        }			
		
		
		if (isset($this->config->regards)) {
			$regards = $this->config->regards;
		}

		if (isset($this->config->image) && $this->config->image!=0) {					
			$itemid = $this->config->image;			
			
			if ($itemid) {
				$file = $this->get_file_by_id($itemid);						
				if ($file) {
					$image = moodle_url::make_draftfile_url($file->itemid, $file->filepath, $file->filename);					
				}
				else{
					$image = $OUTPUT->image_url('welcome_img', 'block_dlwelcome');
				}
			}
		}
		else{
			$image = $OUTPUT->image_url('welcome_img', 'block_dlwelcome');			
		}
        
        $context = new stdClass();
        		
		if(!$regards || $regards=='')
		{
			$regards = get_string('welcomelabel', 'block_dlwelcome');
		}
		
		$bgcolor = get_config('block_dlwelcome', 'bgcolor');
		if($bgcolor)
		{			
			//$PAGE->requires->js_init_code('(document.getElementsByClassName("block  block_dlwelcome")[0]).style.backgroundColor="'.$bgcolor.'";');			
		}
		else{
			$bgcolor = "inherit";		
		}
		$context->bgcolor = $bgcolor;
		
		$txtcolor = get_config('block_dlwelcome', 'txtcolor');
		if(!$txtcolor)
		{
			$txtcolor = "inherit";
		}
		$context->txtcolor = $txtcolor;
		
		$welcome_label = "¡".$regards." ".$USER->firstname."!";
        $context->welcome_label = $welcome_label;
        $context->text = $text;
		$context->regards = $regards;        
		$context->image_url = $image;        
        $output = $OUTPUT->render_from_template('block_dlwelcome/welcome_banner', $context);
        $this->content->text = $output;
		/*if($bgcolor)
		{
			$this->content->text.="<style>.block.block_dlwelcome { background-color:"." ".$bgcolor." important!;}</style>";
		}*/
        return $output;
    }

    /**
     * Serialize and store config data
     */
    function instance_config_save($data, $nolongerused = false) {
        global $DB, $CFG;
        require_once($CFG->libdir . '/filelib.php');			

        $config = clone($data);
        // Move embedded files into a proper filearea and adjust HTML links to match
        $config->text = file_save_draft_area_files($data->text['itemid'], $this->context->id, 'block_dlwelcome', 'content', 0, array('subdirs'=>true), $data->text['text']);
		
        $config->format = FORMAT_PLAIN;														

        parent::instance_config_save($config, $nolongerused);
    }

    public function hide_header() { 
        return true;
    }

	protected function get_file_by_id($itemid) {
		global $DB;
    
		if ($itemid) {
			return $DB->get_record_sql('SELECT * from {files} WHERE itemid = :itemid AND filename <> :name AND source IS NOT NULL', ['itemid' => $itemid,'name' => '.']);
		}
    
		return null;
	}
}