<?php
defined ( 'MOODLE_INTERNAL' ) || die ();

function xmldb_block_knowledge_sharing_install() {
	$page = new moodle_page ();
	$page->set_context ( context_system::instance () );
	
	$page->blocks->add_regions ( array (
			BLOCK_POS_LEFT 
	), false );
	
	$page->blocks->load_blocks ();
	
	$page->blocks->add_block ( 'knowledge_sharing', BLOCK_POS_LEFT, - 100, true, 'course-view-*' );
	
	return true;
}

?>