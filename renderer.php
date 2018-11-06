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

class block_knowledge_sharing_renderer extends plugin_renderer_base {
    public $for_tag = false;

    public function knowledge_sharing_tree() {
    }

    public function render_tree($tree) {
        global $OUTPUT;
        
        $module = array (
            'name' => 'block_knowledge_sharing',
            'fullpath' => '/blocks/knowledge_sharing/module.js',
            'requires' => array (
                'yui2-treeview' 
            ) 
        );
        if (empty ( $tree )) {
            $html = $this->output->box ( get_string ( 'nofilesavailable', 'repository' ) );
        } else {
            $id = uniqid ();
            $treeid = 'knowledge_sharing_tree_' . $id;
            $searchid = 'knowledge_sharing_tree_search' . $id;
            
            $this->page->requires->js_call_amd ( 'block_knowledge_sharing/lib', 'init_tree',
                    array (
                        $treeid,
                        $searchid 
                    ) );
            
            $html = html_writer::empty_tag ( 'input',
                    array (
                        'id' => $searchid,
                        'name' => $searchid,
                        'type' => 'search' 
                    ) );
            
            $icon = html_writer::img ( new moodle_url ( '/blocks/knowledge_sharing/pix/tag.png' ), '',
                    array (
                        'class' => 'group',
                        'title' => get_string ( 'tag-group', 'block_knowledge_sharing' ) 
                    ) );
            
            $html .= html_writer::link ( '', $icon,
                    array (
                        'id' => 'group_tag',
                        'class' => 'group' 
                    ) );
            
            $icon = html_writer::img ( new moodle_url ( '/blocks/knowledge_sharing/pix/category.png' ), '',
                    array (
                        'class' => 'group',
                        'title' => get_string ( 'category-group', 'block_knowledge_sharing' ) 
                    ) );
            
            $html .= html_writer::link ( '', $icon,
                    array (
                        'id' => 'group_category',
                        'class' => 'group' 
                    ) );
            
            $html .= html_writer::start_div ( 'root', array (
                'id' => $treeid 
            ) );
            $html .= $this->tree2html ( $tree );
            $html .= html_writer::end_div ();
        }
        
        return ($html);
    }

    public function tree2html($tree, $parent = NULL) {
        $yuiconfig = array ();
        $yuiconfig ['type'] = 'html';
        
        if (empty ( $tree )) {
            return ('');
        }
        
        $result = '<ul>';
        
        foreach ( $tree as $key => $item ) {
            if ($item->type == 'tag') {
                $result .= '<li yuiConfig=\'' . json_encode ( $yuiconfig ) . '\'><div>' . s ( $item->name ) . '</div> ' .
                         $this->tree2html ( $item->module ) . '</li>';
            }
            
            if ($item->type == 'category') {
                $result .= '<li yuiConfig=\'' . json_encode ( $yuiconfig ) . '\'><div>' . s ( $item->name ) .
                         $this->get_add_icon ( $item->id ) . '</div> ' . $this->tree2html ( $item->category ) .
                         $this->tree2html ( $item->course ) . '</li>';
            }
            
            if ($item->type == 'course') {
                $result .= '<li yuiConfig=\'' . json_encode ( $yuiconfig ) . '\'><div>' . s ( $item->name ) .
                         $this->get_edit_icon ( $item->id ) . $this->get_tag_edit_icon ( $item->id ) . '</div> ' .
                         $this->tree2html ( $item->section, $item->id ) . '</li>';
            }
            
            if ($item->type == 'section') {
                $result .= '<li yuiConfig=\'' . json_encode ( $yuiconfig ) . '\'><div>' . s ( $item->name ) .
                         $this->get_edit_icon ( $parent, $item->id ) . '</div> ' . $this->tree2html ( $item->module ) . '</li>';
            }
            
            if ($item->type == 'module') {
                $image = $this->output->pix_icon ( $item->icon, '', '',
                        array (
                            'class' => 'icon' 
                        ) );
                
                $result .= '<li yuiConfig=\'' . json_encode ( $yuiconfig ) . '\'><div class="knowledge_sharing_module" data-module="' .
                         $item->id . '">' . $image . s ( $item->name ) . $this->get_view_icon ( $item->modtype, $item->id ) .
                         '</div></li>';
            }
        }
        
        $result .= '</ul>';
        
        return ($result);
    }

    protected function get_edit_icon($course, $sectionid = NULL) {
        $icon = $this->output->pix_icon ( 'i/manual_item', '', '',
                array (
                    'class' => 'edit',
                    'title' => get_string ( 'edit-content', 'block_knowledge_sharing' ) 
                ) );
        
        if (isset ( $sectionid )) {
            $edit = html_writer::link ( 
                    new moodle_url ( '/course/view.php',
                            array (
                                'id' => $course,
                                'sectionid' => $sectionid 
                            ) ), $icon );
        } else {
            $edit = html_writer::link ( 
                    new moodle_url ( '/course/view.php', array (
                        'id' => $course 
                    ) ), $icon );
        }
        
        return ($edit);
    }

    protected function get_tag_edit_icon($course) {
        $edit = '';
        
        if ($this->for_tag) {
            $icon = $this->output->pix_icon ( 'e/anchor', '', '',
                    array (
                        'class' => 'edit',
                        'title' => get_string ( 'edit-tag', 'block_knowledge_sharing' ) 
                    ) );
            
            $edit = html_writer::link ( 
                    new moodle_url ( '/course/edit.php', array (
                        'id' => $course 
                    ) ), $icon );
        }
        
        return ($edit);
    }

    protected function get_add_icon($parent) {
        $icon = $this->output->pix_icon ( 'a/create_folder', '', '',
                array (
                    'class' => 'edit',
                    'title' => get_string ( 'add-category', 'block_knowledge_sharing' ) 
                ) );
        
        $add = html_writer::link ( 
                new moodle_url ( '/course/editcategory.php', array (
                    'parent' => $parent 
                ) ), $icon );
        
        return ($add);
    }

    protected function get_view_icon($type, $id) {
        $icon = $this->output->pix_icon ( 'i/import', '', '',
                array (
                    'class' => 'edit',
                    'title' => get_string ( 'view-resource', 'block_knowledge_sharing' ) 
                ) );
        
        $add = html_writer::link ( new moodle_url ( "/mod/$type/view.php", array (
            'id' => $id 
        ) ), $icon );
        
        return ($add);
    }
}
