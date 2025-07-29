<?php
/*
 * This file is part of Totara LMS
 *
 * Copyright (C) 2016 onwards Totara Learning Solutions LTD
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author  Petr Skoda <petr.skoda@totaralms.com>
 * @author  Brian Barnes <brian.barnes@totaralms.com>
 * @package theme_roots
 */

/* Developer documentation is in /pix/flex_icons.php file. */

$icons = [
    /* Do not use 'flex-icon-missing' directly, it indicates requested icon was not found */
    
    'block-dock' => [
        'data' => [
            'classes' => 'lucide-arrow-big-right-dash fs-14',
        ],
    ],
    'block-undock' => [
        'data' => [
            'classes' => 'lucide-arrow-big-right-dash fs-14 fs-flip',
        ],
    ],
    'mod_scorm|icon' => [
        'data' =>
            array(
                'classes' => 'lucide-tv-minimal-play',
            ),
    ],
    'mod_feedback|icon' => [
        'data' =>
            array(
                'classes' => 'lucide-book-check',
            ),
    ],
    'mod_certificate|icon' => [
        'data' =>
            array(
                'classes' => 'lucide-scroll-text',
            ),
    ],
    'mod_quiz|icon'  => [
        'data' =>
            array(
                'classes' => 'lucide-square-pen',
            ),
    ],
    'mod_assign|icon'  => [
        'data' =>
            array(
                'classes' => 'lucide-book-open-check',
            ),
    ],
    'mod_glossary|icon'  => [
        'data' =>
            array(
                'classes' => 'lucide-library-big',
            ),
    ],
    'core|f/mpeg'  => [
        'data' =>
            array(
                'classes' => 'lucide-youtube',
            ),
    ],
    'core|f/wmv'  => [
        'data' =>
            array(
                'classes' => 'lucide-youtube',
            ),
    ],
    'theme_dlcourseflix|expanded'  => [
        'data' =>
            array(
                'classes' => 'lucide-minus',
            ),
    ],
    'theme_dlcourseflix|collapsed'  => [
        'data' =>
            array(
                'classes' => 'lucide-plus',
            ),
    ],
];
