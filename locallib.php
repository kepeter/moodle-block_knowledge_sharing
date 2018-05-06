<?php
defined ( 'MOODLE_INTERNAL' ) || die ();

require_once ($CFG->dirroot . '/course/lib.php');
require_once ($CFG->dirroot . '/lib/coursecatlib.php');
require_once ($CFG->dirroot . '/lib/moodlelib.php');
require_once ($CFG->dirroot . '/tag/classes/tag.php');

function _tree_by_tag(&$new, $root) {
	foreach ( $root as $item ) {
		if ($item->type != 'module') {
			_tree_by_tag ( $new, $item );
		} else {
			foreach ( $item->tags as $key => $tag ) {
				if (! array_key_exists ( $key, $new )) {
					$new [$key]->id = $key;
					$new [$key]->name = $tag;
					$new [$key]->type = 'tag';
					
					$new [$key]->module = array ();
				}
				
				$new [$key]->module [$item->id] = $item;
			}
		}
	}
}

function load_knowledge_tree($root, $exclude) {
	$exclude = explode ( ',', $exclude );
	
	$category = coursecat::get ( $root );
	$children = $category->get_children ();
	
	$cat->id = $category->id;
	$cat->name = $category->name;
	$cat->type = 'category';
	
	$cat->course = get_courses ( $category->id, 'fullname', 'c.id, c.fullname as name, c.visible' );
	
	foreach ( $cat->course as $id => $course ) {
		if ($course->visible) {
			unset ( $course->visible );
			
			$cat->course [$id]->type = 'course';
			
			$mod_info = get_fast_modinfo ( $id );
			$sections = $mod_info->get_section_info_all ();
			$mods = $mod_info->get_cms ();
			
			$section_count = 0;
			
			foreach ( $sections as $section ) {
				$section->name = get_section_name ( $section->course, $section->section );
				
				if ($section->visible && ! empty ( $section->name )) {
					$cat->course [$id]->section [$section->id]->id = $section->id;
					$cat->course [$id]->section [$section->id]->name = $section->name;
					$cat->course [$id]->section [$section->id]->type = 'section';
					
					$module_count = 0;
					
					foreach ( $mods as $cmid => $cm ) {
						if ($cm->visible && ($cm->section == $section->id) && ! in_array ( $cm->module, $exclude )) {
							$cat->course [$id]->section [$section->id]->module [$cmid]->id = $cm->id;
							$cat->course [$id]->section [$section->id]->module [$cmid]->name = $cm->name;
							$cat->course [$id]->section [$section->id]->module [$cmid]->modtype = $cm->modname;
							$cat->course [$id]->section [$section->id]->module [$cmid]->type = 'module';
							$cat->course [$id]->section [$section->id]->module [$cmid]->tags = core_tag_tag::get_item_tags_array ( 'core', 'course_modules', $cm->id );
							$cat->course [$id]->section [$section->id]->module [$cmid]->icon = $cm->get_icon_url ()->out ();
							
							$module_count ++;
						}
					}
					
					$section_count ++;
					
					if ($module_count == 0) {
						unset ( $cat->course [$id]->section [$section->id] );
						
						$section_count --;
					}
				}
			}
			
			if ($section_count == 0) {
				unset ( $cat->course [$id] );
			}
		} else {
			unset ( $cat->course [$id] );
		}
	}
	
	foreach ( $children as $child ) {
		$cat->category [$child->id] = load_knowledge_tree ( $child->id, $exclude );
	}
	
	return (array (
			$cat->id => $cat 
	));
}

function load_knowledge_tree_for_tag($root, $exclude) {
	$new = array ();
	
	_tree_by_tag ( $new, load_knowledge_tree ( $root, $exclude ) );
	
	return ($new);
}

?>