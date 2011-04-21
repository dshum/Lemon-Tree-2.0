LT.Tree = function() {
	var object = {};

	object.browseUrl = null;
	object.treeUrl = null;

	var FOLDER_OPENED_ID = 1;
	var FOLDER_CLOSED_ID = 2;
	var PERMISSION_DROP = 3;
	var ROOT_ID = 'Root.1';

	var branch = new Array();
	var elementList = new Array();
	var activeFolder = null;
	var contextMenuElementId = null;

	object.addElement = function(parentId, elementId, elementName, permissionId, openFolder) {
		if(!branch[parentId]) {
			branch[parentId] = new Array();
		}
		var count = branch[parentId].length;
		branch[parentId][count] = elementId;
		elementList[elementId] = {parentId: parentId, elementId: elementId, elementName: elementName, permissionId: permissionId, openFolder: openFolder};
	};

	object.setActiveFolder = function(elementId) {
		activeFolder = $('#link_'+elementId);
	};

	object.dropTree = function() {
		var escapeId = escapeElementId(ROOT_ID);
		$('#branch_'+escapeId).empty();
		branch = new Array();
		elementList = new Array();
	};

	object.dropNode = function(elementId) {
		var escapeId = escapeElementId(elementId);
		LT.Tree.dropBranch(elementId);
		$('#link_'+escapeId).parent().empty().remove();
	};

	object.replaceNode = function(elementId, elementName) {
		var escapeId = escapeElementId(elementId);
		$('#link_'+escapeId).attr('title', elementName).html(elementName);
	};

	object.dropBranch = function(parentId) {
		var escapeId = escapeElementId(parentId);
		unsetBranch(parentId);
		$('#branch_'+escapeId).empty();
	};

	object.buildTree = function() {
		LT.Tree.buildBranch(ROOT_ID);
	};

	object.buildBranch = function(parentId) {
		var escapeId = escapeElementId(parentId);
		var parentObject = $('#branch_'+escapeId);

		if(branch[parentId] && branch[parentId].length) {
			for(var i = 0; i < branch[parentId].length; i++) {
				var elementId = branch[parentId][i];
				var element = elementList[elementId];

				var li = $('<li></li>').appendTo(parentObject);
				if(i == branch[parentId].length - 1) {
					li.addClass('l');
				}

				var folder = $('<a></a>').appendTo(li);
				var img = $('<img />').appendTo(folder);
				var link = $('<a></a>').appendTo(li);

				folder.attr({
					name: 'folder_'+elementId,
					href: LT.Tree.browseUrl+'?elementId='+elementId,
					target: 'browse',
					elementid: elementId
				});

				link.attr({
					id: 'link_'+elementId,
					href: LT.Tree.browseUrl+'?elementId='+elementId,
					target: 'browse',
					title: element.elementName,
					elementid: elementId,
					parentid: parentId
				}).html(element.elementName);

				link.bind(window.opera ? 'click' : 'contextmenu', LT.Tree.showContextMenu);

				if(branch[elementId] && branch[elementId].length) {

					if(element.openFolder == FOLDER_OPENED_ID) {

						folder.click(function() {
							openBranch($(this).attr('elementid'));
							return false;
						});

						img.attr({id: 'img_'+elementId, src: 'img/tree_folder_minus.png'});

						link.click(function() {
							LT.Tree.showFolder($(this).attr('elementid'));
							openBranch($(this).attr('elementid'), true);
							top.browse.document.location.href = this.href;
							return false;
						});

						var ul = $('<ul></ul>').appendTo(li);
						ul.attr({id: 'branch_'+elementId}).css({display: 'block'});
						LT.Tree.buildBranch(elementId);

					} else {

						folder.click(function() {
							openBranch($(this).attr('elementid'));
							return false;
						});

						img.attr({id: 'img_'+elementId, src: 'img/tree_folder_plus.png'});

						link.click(function() {
							LT.Tree.showFolder($(this).attr('elementid'));
							openBranch($(this).attr('elementid'), true);
							top.browse.document.location.href = this.href;
							return false;
						});

						var ul = $('<ul></ul>').appendTo(li);
						ul.attr({id: 'branch_'+elementId}).css({display: 'none'});
					}

				} else {

					img.attr({id: 'img_'+elementId, src: 'img/tree_folder.png'});

					link.click(function() {
						LT.Tree.showFolder($(this).attr('elementid'));
						top.browse.document.location.href = this.href;
						return false;
					});

				}
			}
		}
	};

	var unsetBranch = function(parentId) {
		if(branch[parentId]) {
			for(var i in branch[parentId]) {
				unsetBranch(branch[parentId][i]);
				elementList[branch[parentId][i]] = null;
			}
			branch[parentId] = null;
		}
	};

	var openBranch = function(elementId, flag) {
		var escapeId = escapeElementId(elementId);
		var element = elementList[elementId];

		if(element.openFolder == FOLDER_OPENED_ID) {
			if(!flag) {
				$('#img_'+escapeId).attr({src: 'img/tree_folder_plus.png'});
				$('#branch_'+escapeId).slideUp('fast');
				elementList[elementId].openFolder = FOLDER_CLOSED_ID;
				$.post(
					LT.Tree.treeUrl+'?action=open',
					{elementId: elementId, value: 'close'},
					function(data) {
						if(data.isParent == '1') {
							top.browse.document.location.href = LT.Tree.browseUrl+'?elementId='+elementId;
						}
					},
					'json'
				);
			}
		} else if(element.openFolder == FOLDER_CLOSED_ID) {
			$('#img_'+escapeId).attr({src: 'img/tree_folder_minus.png'});
			$('#branch_'+escapeId).slideDown('fast');
			elementList[elementId].openFolder = FOLDER_OPENED_ID;
			$.post(LT.Tree.treeUrl+'?action=open', {elementId: elementId, value: 'open'}, null, 'json');
		} else {
			$('#img_'+escapeId).attr({src: 'img/tree_folder_minus.png'});
			LT.Tree.buildBranch(elementId);
			$('#branch_'+escapeId).slideDown('fast');
			elementList[elementId].openFolder = FOLDER_OPENED_ID;
			$.post(LT.Tree.treeUrl+'?action=open', {elementId: elementId, value: 'open'}, null, 'json');
		}
	};

	object.showFolder = function(elementId) {
		var escapeId = escapeElementId(elementId);

		if(activeFolder) {
			activeFolder.css({
				color: '#000000',
				backgroundColor: '#FFFFFF',
				padding: '0'
			});
		}

		activeFolder = $('#link_'+escapeId);
		if(elementId != ROOT_ID) {
			activeFolder.css({
				color: '#855900',
				backgroundColor: '#FFFADB',
				padding: '1px 3px'
			});
		}
	};

	object.showContextMenu = function(event) {
		var container = $('#contextmenuBlock');

		event.stopPropagation();
		LT.Tree.hideContextMenu();

		if(window.opera && !event.ctrlKey) return false;

		var elementId = $(this).attr('elementid');
		var parentId = $(this).attr('parentid');
		var element = elementList[elementId];

		$('#context_edit').css({
			color: '#000'
		}).hover(function() {
			$(this).css({backgroundColor: '#889738', color: '#FFF'})
		}, function() {
			$(this).css({backgroundColor: '#fafafa', color: '#000'})
		}).unbind('click').click(function() {
			LT.Tree.hideContextMenu();
			top.browse.document.location.href = LT.Tree.browseUrl+'?module=ElementEdit&elementId='+elementId;
			return false;
		});

		if(element.permissionId >= PERMISSION_DROP) {

			$('#context_move').css({
				color: '#000'
			}).hover(function() {
				$(this).css({backgroundColor: '#889738', color: '#FFF'})
			}, function() {
				$(this).css({backgroundColor: '#fafafa', color: '#000'})
			}).unbind('click').click(function() {
				LT.Tree.hideContextMenu();
				$('#ContextMenuForm').each(function() {
					this['check[]'].value = elementId;
					this['sourceId'].value = parentId;
					this.action = LT.Tree.browseUrl+'?module=ElementMove';
					this.submit();
				});
				return false;
			});

			$('#context_drop').css({
				color: '#000'
			}).hover(function() {
				$(this).css({backgroundColor: '#889738', color: '#FFF'})
			}, function() {
				$(this).css({backgroundColor: '#fafafa', color: '#000'})
			}).unbind('click').click(function() {
				LT.Tree.hideContextMenu();
				if(!confirm('Удалить выбранный элемент?')) {
					return false;
				}
				var url = LT.Tree.browseUrl+'?module=ElementEdit&elementId='+elementId+'&action=drop';
				$.post(
					url, {},
					function(data) {
						if(data.error && data.error.length) {
							var str = '';
							for(var i in data.error) {
								str += data.error[i]+'\n';
							}
							alert(str);
						} else if(data.dropped) {
							LT.Tree.dropNode(data.dropped);
						}
					},
					'json'
				);
				return false;
			});

		} else {

			$('#context_move').css({
				color: '#999'
			}).hover(function() {
				$(this).css({backgroundColor: '#fafafa'})
			}).unbind('click').click(function() {
				LT.Tree.hideContextMenu();
				return false;
			});

			$('#context_drop').css({
				color: '#999'
			}).hover(function() {
				$(this).css({backgroundColor: '#fafafa'})
			}).unbind('click').click(function() {
				LT.Tree.hideContextMenu();
				return false;
			});

		}

		var size = {
			'width': $(window).width(),
			'height': $(window).height(),
			'sT': $(window).scrollTop(),
			'cW': container.width(),
			'cH': container.height()
		};

		container.css({
			'left': ((event.clientX + size.cW) > size.width ? (event.clientX - size.cW) : event.clientX),
			'top': ((event.clientY + size.cH) > size.height && event.clientY > size.cH ? (event.clientY + size.sT - size.cH) : event.clientY + size.sT)
		}).bind(window.opera ? 'click' : 'contextmenu', LT.Tree.hideContextMenu).fadeIn('fast');

		return false;
	};

	object.hideContextMenu = function() {
		$('#contextmenuBlock').hide();
		return false;
	};

	var escapeElementId = function(elementId) {
		return elementId.replace(/\./, '\\.');
	};

	return object;
}();