<?php
defined ( 'MOODLE_INTERNAL' ) || die ();

require_once ($CFG->dirroot . '/lib/coursecatlib.php');

if ($ADMIN->fulltree) {
	$settings->add ( new admin_setting_heading ( 'block_knowledge_sharing/version', '<h6>' . get_string ( 'version', 'block_knowledge_sharing' ) . $this->release . ' (' . $this->versiondisk . ')</h6>', '' ) );
	
	$categories = coursecat::make_categories_list ( 'moodle/category:manage' );
	
	$settings->add ( new admin_setting_configselect ( 'block_knowledge_sharing/root', get_string ( 'category_list', 'block_knowledge_sharing' ), get_string ( 'category_list_desc', 'block_knowledge_sharing' ), null, $categories ) );
	
	$settings->add ( new admin_setting_configmultiselect_modules ( 'block_knowledge_sharing/exclude', get_string ( 'exclude', 'block_knowledge_sharing' ), get_string ( 'exclude_desc', 'block_knowledge_sharing' ), null ) );
}

?>
