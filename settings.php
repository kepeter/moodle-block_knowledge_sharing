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

if ($ADMIN->fulltree) {
    $settings->add ( 
            new admin_setting_heading ( 'block_knowledge_sharing/version',
                    '<h6>' . get_string ( 'version', 'block_knowledge_sharing' ) . $this->release . ' (' . $this->versiondisk .
                    ')</h6>', '' ) );
    
    $categories = core_course_category::make_categories_list ( 'moodle/category:manage' );
    
    $settings->add ( 
            new admin_setting_configselect ( 'block_knowledge_sharing/root',
                    get_string ( 'category_list', 'block_knowledge_sharing' ),
                    get_string ( 'category_list_desc', 'block_knowledge_sharing' ), null, $categories ) );
    
    $settings->add ( 
            new admin_setting_configmultiselect_modules ( 'block_knowledge_sharing/exclude',
                    get_string ( 'exclude', 'block_knowledge_sharing' ), get_string ( 'exclude_desc', 'block_knowledge_sharing' ),
                    null ) );
}
