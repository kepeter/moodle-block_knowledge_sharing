<?php
defined ( 'MOODLE_INTERNAL' ) || die ();

require_once ($CFG->dirroot . '/blocks/knowledge_sharing/locallib.php');

class block_knowledge_sharing extends block_base {

	public function init() {
		$this->blockname = 'block_knowledge_sharing';
		$this->title = get_string ( 'pluginname', 'block_knowledge_sharing' );
	}

	public function instance_allow_multiple() {
		return (false);
	}

	public function instance_allow_config() {
		return (false);
	}

	function has_config() {
		return (true);
	}

	public function get_content() {
		global $DB;
		
		if (! $this->page->user_is_editing ()) {
			return (null);
		}
		
		if ($this->content !== null) {
			return ($this->content);
		}
		
		$config = get_config ( 'block_knowledge_sharing' );
		$renderer = $this->page->get_renderer ( 'block_knowledge_sharing' );
		
		$this->content = new stdClass ();
		$this->content->text = $renderer->render_tree ( load_knowledge_tree ( $config->root, $config->exclude ) );
		
		return ($this->content);
	}
}

?>