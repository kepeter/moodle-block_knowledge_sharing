// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Knowledge Sharing Block
 * 
 * @package block_knowledge_sharing
 * @copyright 2018 Peter Eliyahu Kornfeld
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define([ 'jquery', 'core/tree', 'core/ajax', 'core/url' ], function ($, tree, ajax, url) {
    var drop_taget;
    var block_knowledge_sharing = {};

    var init_tree = function (treeid, searchid) {
        block_knowledge_sharing.treeid = treeid;

        block_knowledge_sharing.activeGroup = 'group_tag';

        block_knowledge_sharing.searchid = searchid;
        block_knowledge_sharing.lastValue = '';

        render();
        dragdrop();
        search();
        setgroup();
        group();
    };

    var setgroup = function () {
        $('#group_tag, #group_category').removeClass('active').addClass('inactive');
        $('#' + block_knowledge_sharing.activeGroup).addClass('active').removeClass('inactive');
    };

    var group = function () {
        $('#group_tag, #group_category').on('click', function (e) {
            e.preventDefault();

            if ($(e.currentTarget).attr('class').indexOf('inactive') >= 0) {
                return;
            }

            var forTag = $(e.currentTarget).attr('id').replace('group_', '') == 'tag';

            refresh(forTag);
        });
    };

    var search = function () {
        $('#' + block_knowledge_sharing.searchid).on('input', function (e) {
            var text = $(this).val().toUpperCase();

            $('#' + block_knowledge_sharing.treeid + ' li').each(function () {
                if ($(this).text().toUpperCase().indexOf(text) > -1) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });
    };

    var render = function () {
        new tree('#' + block_knowledge_sharing.treeid);
    };

    var dragdrop = function () {
        $('.knowledge_sharing_module').on('dragstart', function (e) {
            e = e.originalEvent;

            e.dataTransfer.dropEffect = 'copy';
            e.dataTransfer.setData('text', $(this).attr('data-module'));
        }).on('dragend', function(e) {
            this.drop_target = null;

            $('.dndupload-preview').addClass('dndupload-hidden');
        });

        $('li.section').on('dragenter', function (e) {
            e.stopPropagation();
            e.preventDefault();

            this.drop_target = e.target;

            $(this).find('.dndupload-preview').removeClass('dndupload-hidden');
        }).on('dragover', function (e) {
            e.stopPropagation();
            e.preventDefault();

        }).on('dragleave', function (e) {
            e.stopPropagation();
            e.preventDefault();

            if (this.drop_target === e.target) {
                $(this).find('.dndupload-preview').addClass('dndupload-hidden');
            }
        }).on('drop', function (e) {
            var that = this;

            e.stopPropagation();
            e.preventDefault();

            this.drop_target = null;

            e = e.originalEvent;

            $(that).find('.dndupload-preview').addClass('dndupload-hidden');

            var section = $(that).find('span[data-itemtype="sectionname"]').attr('data-itemid');
            var module = e.dataTransfer.getData('text');

            if (section) {
                addSpinner($(that));

                ajax.call([ {
                    methodname : 'block_knowledge_sharing_external_duplicte',
                    args : {
                        'section' : section,
                        'module' : module,
                        'course' : $('#course').attr('value')
                    }
                } ])[0].done(function (response) {
                    removeSpinner($(that));

                    var result = JSON.parse(response);
                    var ul = $(that).find('ul.section');
                    var il = $.parseHTML(result.module);

                    ul.find('li.dndupload-preview').before(il);

                    M.course_dndupload.add_editing($(il).attr('id'));
                }).fail(function (ex) {
                    removeSpinner($(that));

                    console.log(ex);
                });
            }
        });
    };

    var refresh = function (tag) {
        var root = $('#' + block_knowledge_sharing.treeid);

        root.hide();
        addSpinner(root.parent());

        ajax.call([ {
            methodname : 'block_knowledge_sharing_external_group',
            args : {
                'tag' : tag,
                'course' : $('#course').val()
            }

        } ])[0].done(function (response) {
            removeSpinner(root.parent());

            var result = JSON.parse(response);

            root.html(result.content);

            block_knowledge_sharing.activeGroup = tag ? 'group_category' : 'group_tag';

            render();
            dragdrop();
            search();
            setgroup();

            root.show();

        }).fail(function (ex) {
            removeSpinner(root.parent());
            root.show();

            console.log(ex);
        });
    };

    var addSpinner = function (element) {
        element.addClass('updating');

        var spinner = element.find('img.spinner');

        if (spinner.length) {
            spinner.show();
        } else {
            spinner = $('<img/>').attr('src', url.imageUrl('i/loading_small')).addClass('spinner').addClass('smallicon');
            element.append(spinner);
        }
    };

    var removeSpinner = function (element) {
        element.removeClass('updating');
        element.find('img.spinner').hide();
    };

    return {
        init_tree : init_tree
    };
});
