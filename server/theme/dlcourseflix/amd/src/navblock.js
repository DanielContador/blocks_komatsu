/*
 * This file is part of Totara Learn
 *
 * Copyright (C) 2018 onwards Totara Learning Solutions LTD
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
 * @author Yuliya Bozhko <yuliya.bozhko@totaralearning.com>
 *
 * @package block_course_navigation
 */

/**
 * Load the navigation tree javascript.
 *
 * @module     block_course_navigation/navblock
 * @package    core
 * @copyright  2015 John Okely <john@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery', 'core/yui', 'theme_dlcourseflix/dl_tree'], function($, Y, DlTree) {
    return {
        init: function() {
            var blockElement = $('.block_course_navigation');
            var instanceid = blockElement.data('instanceid');

            if (instanceid) {
                var navTree = new DlTree(".block_course_navigation .dl_block_tree");
                navTree.finishExpandingGroup = function(item) {
                    DlTree.prototype.finishExpandingGroup.call(this, item);
                    Y.use('moodle-core-event', function() {
                        Y.Global.fire(M.core.globalEvents.BLOCK_CONTENT_UPDATED, {
                            instanceid: instanceid
                        });
                    });
                };
                navTree.collapseGroup = function(item) {
                    DlTree.prototype.collapseGroup.call(this, item);
                    Y.use('moodle-core-event', function() {
                        Y.Global.fire(M.core.globalEvents.BLOCK_CONTENT_UPDATED, {
                            instanceid: instanceid
                        });
                    });
                };
            }
        }
    };
});
