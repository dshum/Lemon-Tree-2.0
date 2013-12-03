LT.MultilinkTree = function() {
	var object = {};

	var branch = new Array();
	var elementList = new Array();
	var activeFolder = null;

	object.addElement = function(propertyName, parentId, elementId, elementName, isActive, isOpen, isCheckbox) {
		if(!branch[propertyName]) {
			branch[propertyName] = new Array();
		}
		if(!branch[propertyName][parentId]) {
			branch[propertyName][parentId] = new Array();
		}

		if(!elementList[propertyName]) {
			elementList[propertyName] = new Array();
		}

		var count = branch[propertyName][parentId].length;

		branch[propertyName][parentId][count] = elementId;

		elementList[propertyName][elementId] = {
			parentId: parentId,
			elementId: elementId,
			elementName: elementName,
			isActive: isActive,
			isOpen: isOpen,
			isCheckbox: isCheckbox
		};
	};

	object.addPlainElement = function(propertyName, elementId, elementName, isActive) {
		if(!branch[propertyName]) {
			branch[propertyName] = new Array();
		}

		if(!elementList[propertyName]) {
			elementList[propertyName] = new Array();
		}

		var count = branch[propertyName].length;

		branch[propertyName][count] = elementId;

		elementList[propertyName][elementId] = {
			elementId: elementId,
			elementName: elementName,
			isActive: isActive
		};
	};

	object.buildBranch = function(propertyName, parentId) {
		var escapedParentId = escapeId(parentId);
		var parentObject = $('#'+propertyName+'_branch_'+escapedParentId);

		if(branch[propertyName][parentId] && branch[propertyName][parentId].length) {
			for(var i = 0; i < branch[propertyName][parentId].length; i++) {
				var elementId = branch[propertyName][parentId][i];
				var element = elementList[propertyName][elementId];
				var id = getId(elementId);
				var checked = element.isActive ? ' checked="true"' : '';

				var div = $('<div></div>').appendTo(parentObject);

				if(branch[propertyName][elementId] && branch[propertyName][elementId].length) {
					var open = element.isOpen ? 'true' : 'false';
					var a = $('<a class="plus" elementid="'+elementId+'" opened="'+open+'"></a>').appendTo(div).click(function() {
						var elementId = $(this).attr('elementid');
						var escapedElementId = escapeId(elementId);
						var opened = $(this).attr('opened');
						if(opened == 'true') {
							$('#'+propertyName+'_plus_'+escapedElementId).attr('src', 'img/ico_plus.gif');
							$('#'+propertyName+'_branch_'+escapedElementId).slideUp('fast');
							$(this).attr('opened', 'false');
						} else {
							$('#'+propertyName+'_plus_'+escapedElementId).attr('src', 'img/ico_minus.gif');
							$('#'+propertyName+'_branch_'+escapedElementId).slideDown('fast');
							$(this).attr('opened', 'true');
						}
						return false;
					});
					if(element.isOpen) {
						var img = $('<img id="'+propertyName+'_plus_'+elementId+'" width="13" height="13" src="img/ico_minus.gif" alt="Свернуть ветку" titile="Свернуть ветку" />').appendTo(a);
					} else {
						var img = $('<img id="'+propertyName+'_plus_'+elementId+'" width="13" height="13" src="img/ico_plus.gif" alt="Раскрыть ветку" titile="Раскрыть ветку" />').appendTo(a);
					}
				} else {
					var img = $('<img width="11" height="11" src="img/p.gif" />').appendTo(div);
				}

				if(element.isCheckbox) {
					var checkbox = $('<input type="checkbox" id="'+propertyName+'_check_'+elementId+'" name="'+propertyName+'[]" elementname="'+element.elementName+'" value="'+id+'"'+checked+'" title="Выбрать" />').appendTo(div);
					var span = $('<label for="'+propertyName+'_check_'+elementId+'">&nbsp;'+element.elementName+'</label>').appendTo(div);
				} else {
					var span = $('<span>&nbsp;'+element.elementName+'</span>').appendTo(div);
				}

				if(branch[propertyName][elementId] && branch[propertyName][elementId].length) {
					if(element.isOpen) {
						var subdiv = $('<div id="'+propertyName+'_branch_'+elementId+'" style="display: block;"></div>').appendTo(div);
					} else {
						var subdiv = $('<div id="'+propertyName+'_branch_'+elementId+'" style="display: none;"></div>').appendTo(div);
					}
					LT.MultilinkTree.buildBranch(propertyName, elementId);
				}

			}
		}
	};

	object.buildPlainList = function(propertyName, parentId) {
		var escapedParentId = escapeId(parentId);
		var parentObject = $('#'+propertyName+'_branch_'+escapedParentId);

		if(branch[propertyName] && branch[propertyName].length) {
			for(var i = 0; i < branch[propertyName].length; i++) {
				var elementId = branch[propertyName][i];
				var element = elementList[propertyName][elementId];
				var id = getId(elementId);
				var checked = element.isActive ? ' checked="true"' : '';

				var div = $('<div></div>').appendTo(parentObject);

				var img = $('<img width="11" height="11" src="img/p.gif" />').appendTo(div);

				var radio = $('<input type="checkbox" id="'+propertyName+'_check_'+elementId+'" name="'+propertyName+'[]" elementname="'+element.elementName+'" value="'+id+'"'+checked+'" title="Выбрать" />').appendTo(div);

				var span = $('<label for="'+propertyName+'_check_'+elementId+'">&nbsp;'+element.elementName+'</label>').appendTo(div);
			}
		}
	};

	var escapeId = function(elementId) {
		return elementId.replace(/\./, '\\.');
	};

	var getId = function(elementId) {
		var offset = elementId.indexOf('.') + 1;
		return elementId.substring(offset);
	};

	return object;
}();