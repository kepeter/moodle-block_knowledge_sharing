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
        $this->content->text = $renderer->render_tree ( 
                block_knowledge_sharing_load_knowledge_tree ( $config->root, $config->exclude,
                        isset ( $config->no_capability_check ) ? $config->no_capability_check : FALSE ) );
        
        return ($this->content);
    }
}
