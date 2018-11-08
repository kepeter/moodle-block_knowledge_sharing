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

$functions = array (
    'block_knowledge_sharing_external_duplicte' => array (
        'classname'   => 'block_knowledge_sharing_external',
        'methodname'  => 'duplicate',
        'classpath'   => '/blocks/knowledge_sharing/classes/external.php',
        'description' => 'Duplicate course module.',
        'type'        => 'write',
        'ajax'        => true 
    ),
    'block_knowledge_sharing_external_group'    => array (
        'classname'   => 'block_knowledge_sharing_external',
        'methodname'  => 'group',
        'classpath'   => '/blocks/knowledge_sharing/classes/external.php',
        'description' => 'Renders tree according to group option.',
        'type'        => 'read',
        'ajax'        => true 
    ) 
);