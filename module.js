M.block_knowledge_sharing = {};

M.block_knowledge_sharing.init_tree = function(Y, treeid, searchid) {

	M.block_knowledge_sharing.treeid = treeid;

	M.block_knowledge_sharing.activeGroup = 'group_tag';

	M.block_knowledge_sharing.searchid = searchid;
	M.block_knowledge_sharing.lastValue = '';

	M.block_knowledge_sharing
			.render();

	M.block_knowledge_sharing
			.dragdrop();

	M.block_knowledge_sharing
			.search();

	M.block_knowledge_sharing
			.setgroup();

	M.block_knowledge_sharing
			.group();
};

M.block_knowledge_sharing.setgroup = function() {
	Y
			.one('#'
					+ M.block_knowledge_sharing.treeid)
			.ancestor()
			.all('#group_tag, #group_category')
			.removeClass('active')
			.addClass('inactive');

	Y
			.one('#'
					+ M.block_knowledge_sharing.treeid)
			.ancestor()
			.one('#'
					+ M.block_knowledge_sharing.activeGroup)
			.addClass('active')
			.removeClass('inactive');
};

M.block_knowledge_sharing.group = function() {
	Y
			.one('#'
					+ M.block_knowledge_sharing.treeid)
			.ancestor()
			.all('#group_tag, #group_category')
			.on('click', function(e) {
				e
						.preventDefault();

				if (e.currentTarget
						.getAttribute('class')
						.indexOf('inactive') >= 0) {
					return;
				}

				var forTag = e.currentTarget
						.get('id')
						.replace('group_', '') == 'tag';

				M.block_knowledge_sharing
						.refresh(forTag);
			});
};

M.block_knowledge_sharing.search = function() {
	Y
			.use('autocomplete-base', 'autocomplete-filters', function(Y) {
				var TreeFilter = Y.Base
						.create('treeFilter', Y.Base, [ Y.AutoCompleteBase ], {
							initializer : function() {
								this
										._bindUIACBase();
								this
										._syncUIACBase();
							}
						}),

				filter = new TreeFilter({
					inputNode : '#'
							+ M.block_knowledge_sharing.searchid,
					minQueryLength : 0,
					queryDelay : 0,

					source : (function() {
						var results = [];

						Y
								.all('#'
										+ M.block_knowledge_sharing.treeid
										+ '>div>div div.ygtvitem')
								.each(function(node) {
									results
											.push({
												node : node,
												text : node
														.get('text')
											});
								});

						return results;
					}
							()),

					resultTextLocator : 'text',
					resultFilters : 'phraseMatch'
				});

				filter
						.on('results', function(e) {
							Y
									.all('#'
											+ M.block_knowledge_sharing.treeid
											+ '>div>div div.ygtvitem')
									.addClass('hidden');

							Y.Array
									.each(e.results, function(result) {
										result.raw.node
												.removeClass('hidden');
									});
						});
			});
};

M.block_knowledge_sharing.render = function() {
	Y
			.use('yui2-treeview', function(Y) {
				var tree = new Y.YUI2.widget.TreeView(M.block_knowledge_sharing.treeid);

				tree
						.subscribe('clickEvent', function(node, event) {
							return false
						});

				tree
						.render();
				tree
						.expandAll();
			});
};

M.block_knowledge_sharing.dragdrop = function() {
	Y
			.use('dd', 'io', function(Y) {
				var modules = Y.Node
						.all('div.knowledge_sharing_module');

				modules
						.each(function(item, index) {
							var drag = new Y.DD.Drag({
								node : item
							})
									.plug(Y.Plugin.DDProxy, {
										cloneNode : true,
										moveOnEnd : false
									});

							drag
									.on('drag:enter', function(e) {
										e.drop
												.get('node')
												.one('.dndupload-preview')
												.removeClass('dndupload-hidden');
									});

							drag
									.on('drag:exit', function(e) {
										e.drop
												.get('node')
												.one('.dndupload-preview')
												.addClass('dndupload-hidden');
									});

							drag
									.on('drag:drophit', function(e) {
										var target = e.drop
												.get('node');

										target
												.one('.dndupload-preview')
												.addClass('dndupload-hidden');

										var section = target
												.one('span[data-itemtype="sectionname"]')
												.getAttribute('data-itemid');
										var module = e.drag
												.get('node')
												.getAttribute('data-module');

										if (section) {
											spinner = M.util
													.add_spinner(Y, target);
											Y
													.io(M.cfg.wwwroot
															+ '/blocks/knowledge_sharing/controller.php', {
														method : 'POST',
														data : {
															method : 'upload',
															section : section,
															module : module,
															course : Y
																	.one('#course')
																	.get('value'),
															sesskey : M.cfg.sesskey
														},
														on : {
															start : function(transactionid, arguments) {
																spinner
																		.show();
															},
															end : function(transactionid, arguments) {
																spinner
																		.remove();
															},
															success : function(transactionid, response, arguments) {
																var result = JSON
																		.parse(response.responseText);

																var ul = Y
																		.one('span[data-itemid="'
																				+ result.section
																				+ '"]')
																		.ancestor('div')
																		.one('ul.section');
																var il = Y.Node
																		.create(result.module);

																ul
																		.insert(il, ul
																				.one('li.dndupload-preview'));

																M.course_dndupload
																		.add_editing(il._node.id);
															},
															failure : function(transactionid, response, arguments) {
																console
																		.log(response);
															}
														}
													});
										}
									});
						});

				var sections = Y.Node
						.all('li.section');

				sections
						.each(function(item, index) {
							var drop = new Y.DD.Drop({
								node : item
							});
						});
			});
};

M.block_knowledge_sharing.refresh = function(tag) {
	Y
			.use('io', function(Y) {
				var root = Y
						.one('#'
								+ M.block_knowledge_sharing.treeid);
				var spinner = M.util
						.add_spinner(Y, root
								.ancestor());

				Y
						.io(M.cfg.wwwroot
								+ '/blocks/knowledge_sharing/controller.php', {
							method : 'POST',
							data : {
								method : 'group',
								tag : tag,
								sesskey : M.cfg.sesskey
							},
							on : {
								start : function(transactionid, arguments) {
									root
											.hide();
									spinner
											.show();
								},
								end : function(transactionid, arguments) {
									spinner
											.remove();
									root
											.show();
								},
								success : function(transactionid, response, arguments) {
									var result = JSON
											.parse(response.responseText);

									root
											.setHTML(result.content);

									M.block_knowledge_sharing.activeGroup = tag ? 'group_category'
											: 'group_tag';

									M.block_knowledge_sharing
											.render();

									M.block_knowledge_sharing
											.dragdrop();

									M.block_knowledge_sharing
											.search();

									M.block_knowledge_sharing
											.setgroup();
								},
								failure : function(transactionid, response, arguments) {
									console
											.log(response);
								}
							}
						});
			});
};
