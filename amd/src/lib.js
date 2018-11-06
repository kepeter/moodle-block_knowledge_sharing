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
define([ 'jquery', 'core/ajax' ], function ($, Ajax) {
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
        Y.one('#' + block_knowledge_sharing.treeid).ancestor().all('#group_tag, #group_category').removeClass('active').addClass(
                'inactive');

        Y.one('#' + block_knowledge_sharing.treeid).ancestor().one('#' + block_knowledge_sharing.activeGroup).addClass('active')
                .removeClass('inactive');
    };

    var group = function () {
        Y.one('#' + block_knowledge_sharing.treeid).ancestor().all('#group_tag, #group_category').on('click', function (e) {
            e.preventDefault();

            if (e.currentTarget.getAttribute('class').indexOf('inactive') >= 0) {
                return;
            }

            var forTag = e.currentTarget.get('id').replace('group_', '') == 'tag';

            refresh(forTag);
        });
    };

    var search = function () {
        Y.use('autocomplete-base', 'autocomplete-filters', function (Y) {
            var TreeFilter = Y.Base.create('treeFilter', Y.Base, [ Y.AutoCompleteBase ], {
                initializer : function () {
                    this._bindUIACBase();
                    this._syncUIACBase();
                }
            }),

            filter = new TreeFilter({
                inputNode : '#' + block_knowledge_sharing.searchid,
                minQueryLength : 0,
                queryDelay : 0,

                source : (function () {
                    var results = [];

                    Y.all('#' + block_knowledge_sharing.treeid + '>div>div div.ygtvitem').each(function (node) {
                        results.push({
                            node : node,
                            text : node.get('text')
                        });
                    });

                    return results;
                }()),

                resultTextLocator : 'text',
                resultFilters : 'phraseMatch'
            });

            filter.on('results', function (e) {
                Y.all('#' + block_knowledge_sharing.treeid + '>div>div div.ygtvitem').addClass('hidden');

                Y.Array.each(e.results, function (result) {
                    result.raw.node.removeClass('hidden');
                });
            });
        });
    };

    var render = function () {
        Y.use('yui2-treeview', function (Y) {
            var tree = new Y.YUI2.widget.TreeView(block_knowledge_sharing.treeid);

            tree.subscribe('clickEvent', function (node, event) {
                return false
            });

            tree.render();
            tree.expandAll();
        });
    };

    var dragdrop = function () {
        Y.use('dd', 'io', function (Y) {
            var modules = Y.Node.all('div.knowledge_sharing_module');

            modules.each(function (item, index) {
                var drag = new Y.DD.Drag({
                    node : item
                }).plug(Y.Plugin.DDProxy, {
                    cloneNode : true,
                    moveOnEnd : false
                });

                drag.on('drag:enter', function (e) {
                    e.drop.get('node').one('.dndupload-preview').removeClass('dndupload-hidden');
                });

                drag.on('drag:exit', function (e) {
                    e.drop.get('node').one('.dndupload-preview').addClass('dndupload-hidden');
                });

                drag.on('drag:drophit', function (e) {
                    var target = e.drop.get('node');

                    target.one('.dndupload-preview').addClass('dndupload-hidden');

                    var section = target.one('span[data-itemtype="sectionname"]').getAttribute('data-itemid');
                    var module = e.drag.get('node').getAttribute('data-module');

                    if (section) {
                        var spinner = M.util.add_spinner(Y, target);

                        spinner.show();

                        Ajax.call([ {
                            methodname : 'block_knowledge_sharing_external_duplicte',
                            args : {
                                'section' : section,
                                'module' : module,
                                'course' : Y.one('#course').get('value')
                            }

                        } ])[0].done(function (response) {
                            spinner.remove();

                            var result = JSON.parse(response);
                            var ul = Y.one('span[data-itemid="' + result.section + '"]').ancestor('div.content').one('ul.section');
                            var il = Y.Node.create(result.module);

                            ul.insert(il, ul.one('li.dndupload-preview'));

                            M.course_dndupload.add_editing(il._node.id);
                        }).fail(function (ex) {
                            spinner.remove();
                            console.log(ex);
                        });
                    }
                });
            });

            var sections = Y.Node.all('li.section');

            sections.each(function (item, index) {
                var drop = new Y.DD.Drop({
                    node : item
                });
            });
        });
    };

    var refresh = function (tag) {
        var root = Y.one('#' + block_knowledge_sharing.treeid);
        var spinner = M.util.add_spinner(Y, root.ancestor());

        root.hide();
        spinner.show();

        Ajax.call([ {
            methodname : 'block_knowledge_sharing_external_group',
            args : {
                'tag' : tag
            }

        } ])[0].done(function (response) {
            spinner.remove();
            root.show();

            var result = JSON.parse(response);

            root.setHTML(result.content);

            block_knowledge_sharing.activeGroup = tag ? 'group_category' : 'group_tag';

            render();
            dragdrop();
            search();
            setgroup();
        }).fail(function (ex) {
            spinner.remove();
            root.show();
            console.log(ex);
        });
    };

    return {
        init_tree : init_tree
    };
});
