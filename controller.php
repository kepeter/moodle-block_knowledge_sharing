<?php
require_once ('../../config.php');

require_once ($CFG->dirroot . '/course/lib.php');
require_once ($CFG->dirroot . '/lib/coursecatlib.php');
require_once ($CFG->dirroot . '/lib/moodlelib.php');
require_once ($CFG->dirroot . '/lib/filelib.php');

require_once (__DIR__ . '/locallib.php');

function upload() {
	global $DB, $PAGE;
	
	$section = required_param ( 'section', PARAM_INT );
	$module = required_param ( 'module', PARAM_INT );
	$course = required_param ( 'course', PARAM_INT );
	
	require_login ( $course );
	
	$module = $DB->get_record ( 'course_modules', array (
			'id' => $module 
	) );
	
	$module->modname = $DB->get_field ( 'modules', 'name', array (
			'id' => $module->module 
	), MUST_EXIST );
	
	$section = $DB->get_record ( 'course_sections', array (
			'id' => $section 
	) );
	
	$course = $DB->get_record ( 'course', array (
			'id' => $course 
	) );
	
	$module = duplicate_module ( $course, $module );
	
	course_add_cm_to_section ( $course, $module->id, $section->section );
	
	$courserenderer = $PAGE->get_renderer ( 'core', 'course' );
	$completioninfo = new completion_info ( $course );
	
	$resp->section = $section->id;
	$resp->module = $courserenderer->course_section_cm_list_item ( $course, $completioninfo, $module, null );
	
	echo json_encode ( $resp );
}

function group() {
	global $PAGE;
	
	$tag = required_param ( 'tag', PARAM_BOOL );
	
	$config = get_config ( 'block_knowledge_sharing' );
	
	if ($tag) {
		$tree = load_knowledge_tree_for_tag ( $config->root, $config->exclude );
	} else {
		$tree = load_knowledge_tree ( $config->root, $config->exclude );
	}
	
	$renderer = $PAGE->get_renderer ( 'block_knowledge_sharing' );
	$renderer->for_tag = $tag;
	
	echo json_encode ( array (
			'format' => $tag ? 'tag' : 'category',
			'content' => $renderer->tree2html ( $tree ) 
	) );
}

global $PAGE;

$method = required_param ( 'method', PARAM_ALPHA );

require_sesskey ();
require_login ();

$PAGE->set_url ( '/blocks/knowledge_sharing/controller.php' );

$method ();

?>