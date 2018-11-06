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
