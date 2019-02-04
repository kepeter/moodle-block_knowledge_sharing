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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * Knowledge Sharing Block
 *
 * @package block_knowledge_sharing
 * @copyright 2018 Peter Eliyahu Kornfeld
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined ( 'MOODLE_INTERNAL' ) || die ();

require_once ($CFG->dirroot . '/lib/externallib.php');
require_once ($CFG->dirroot . '/blocks/knowledge_sharing/locallib.php');

class block_knowledge_sharing_external extends external_api {

    public static function duplicate_parameters() {
        return new external_function_parameters ( 
                array (
                    'section' => new external_value ( PARAM_INT, 'course section id', VALUE_REQUIRED ),
                    'module'  => new external_value ( PARAM_INT, 'course module id', VALUE_REQUIRED ),
                    'course'  => new external_value ( PARAM_INT, 'course id', VALUE_REQUIRED ) 
                ) );
    }

    public static function duplicate($section, $module, $course) {
        global $DB, $PAGE;
        
        $params = array (
            'section' => $section,
            'module'  => $module,
            'course'  => $course 
        );
        $params = self::validate_parameters ( self::duplicate_parameters (), $params );
        
        require_sesskey ();
        require_login ();
        require_login ( $course );
        
        $module = $DB->get_record ( 'course_modules', array (
            'id' => $module
        ) );
        
        $module->modname = $DB->get_field ( 'modules', 'name', array (
            'id' => $module->module
        ), MUST_EXIST );
        
        $module = get_coursemodule_from_id ( $module->modname, $module->id, $module->course );
        
        $section = $DB->get_record ( 'course_sections', array (
            'id' => $section
        ) );
        
        $course = $DB->get_record ( 'course', array (
            'id' => $course
        ) );
        
        $module->course = $course->id;
        $newmodule = duplicate_module ( $course, $module );
        
        $DB->set_field ( $newmodule->modname, 'name', $module->name, [
            'id' => $newmodule->instance
        ] );
        $newmodule->name = $module->name;
        
        course_add_cm_to_section ( $course, $newmodule->id, $section->section );
        
        $courserenderer = $PAGE->get_renderer ( 'core', 'course' );
        $completioninfo = new completion_info ( $course );
        
        $resp->section = $section->id;
        $resp->module = $courserenderer->course_section_cm_list_item ( $course, $completioninfo, $newmodule, null );
        
        return json_encode ( $resp );
    }

    public static function duplicate_returns() {
        return new external_value ( PARAM_RAW, 'rendering info for newly added course module' );
    }

    public static function group_parameters() {
        return new external_function_parameters ( 
                array (
                    'tag'     => new external_value ( PARAM_BOOL, 'group by tag', VALUE_REQUIRED ),
                    'course'  => new external_value ( PARAM_INT, 'course id', VALUE_REQUIRED )
                ) );
    }

    public static function group($tag, $course) {
        global $PAGE;
        
        $params = array (
            'tag'     => $tag,
            'course'  => $course 
        );
        $params = self::validate_parameters ( self::group_parameters (), $params );
        
        require_sesskey ();
        require_login ();
        require_login ( $course );
        
        $config = get_config ( 'block_knowledge_sharing' );
        
        if ($tag) {
            $tree = block_knowledge_sharing_load_knowledge_tree_for_tag ( $config->root, $config->exclude );
        } else {
            $tree = block_knowledge_sharing_load_knowledge_tree ( $config->root, $config->exclude );
        }
        
        $renderer = $PAGE->get_renderer ( 'block_knowledge_sharing' );
        $renderer->for_tag = $tag;
        
        return json_encode ( 
                array (
                    'format'  => $tag ? 'tag' : 'category',
                    'content' => $renderer->tree2html ( $tree ) 
                ) );
    }

    public static function group_returns() {
        return new external_value ( PARAM_RAW, 'rendering info for tree' );
    }
}
