LT.ElementMove = function() {
	var object = {};

	var ROOT_ID = 'Root.1';

	var branch = new Array();
	var elementList = new Array();

	object.addElement = function(parentId, elementId, elementName, isFolder, isActive) {
		if(!branch[parentId]) {
			branch[parentId] = new Array();
		}
		var count = branch[parentId].length;
		branch[parentId][count] = elementId;
		elementList[elementId] = {parentId: parentId, elementId: elementId, elementName: elementName, isFolder: isFolder, isActive: isActive};
	};

	object.buildTree = function() {
		LT.ElementMove.buildBranch(ROOT_ID);
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

				if(element.isActive) {
					var link = $('<a></a>').appendTo(li);
				} else {
					var link = $('<span></span>').appendTo(li);
				}

				folder.attr({
					name: 'folder_'+elementId,
					href: LT.ElementMove.browseUrl+'?elementId='+elementId,
					elementid: elementId
				}).click(function() {return false});

				if(element.isActive) {
					link.attr({
						id: 'link_'+elementId,
						href: LT.Common.selfUrl+'&elementId='+elementId,
						title: element.elementName,
						elementid: elementId,
						elementname: element.elementName
					}).html(element.elementName);
				} else {
					link.attr({
						id: 'link_'+elementId,
						title: element.elementName,
						elementid: elementId,
						elementname: element.elementName
					}).css({
						color: '#999'
					}).html(element.elementName);
				}

				if(branch[elementId] && branch[elementId].length) {

					img.attr({id: 'img_'+elementId, src: 'img/tree_folder_minus.png'});
					var ul = $('<ul></ul>').appendTo(li);
					ul.attr({id: 'branch_'+elementId}).css({display: 'block'});
					LT.ElementMove.buildBranch(elementId);

				} else if(element.isFolder) {

					img.attr({id: 'img_'+elementId, src: 'img/tree_folder.png'});

				} else {

					img.attr({id: 'img_'+elementId, src: 'img/tree_file.png'});

				}
			}
		}
	};

	var escapeElementId = function(elementId) {
		return elementId.replace(/\./, '\\.');
	};

	return object;
}();