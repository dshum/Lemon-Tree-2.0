LT.Item = function() {
	var object = {};

	object.countChecked = 0;

	object.addItem = function() {
		LT.Common.ajaxSubmit(function(data){
			if(data.itemId) {
				document.location.href = LT.Common.baseUrl+'?module=ItemEdit&itemId='+data.itemId;
			}
		});
	};

	object.saveItem = function() {
		LT.Common.ajaxSubmit(onSaveItem);
	};

	var onSaveItem = function(data) {
		if(data.dropped) {
			for(var i in data.dropped) {
				$('#property_row_'+data.dropped[i]).remove();
				LT.Item.countChecked--;
			}
		}

		if(data.added) {
			for(var i in data.added) {
				switch(data.added[i].table) {
					case 'field':
						insertFieldPropertyRow(data.added[i]);
						break;
					case 'onetoone':
						insertOneToOnePropertyRow(data.added[i]);
						break;
					case 'onetomany':
						insertOneToManyPropertyRow(data.added[i]);
						break;
					case 'manytomany':
						insertManyToManyPropertyRow(data.added[i]);
						break;
					default:
						break;
				}
			}
		}

		if(data.tree && data.tree.length && top.tree) {
			top.tree.LT.Tree.dropTree();
			for(var i in data.tree) {
				top.tree.LT.Tree.addElement(data.tree[i].parentId, data.tree[i].elementId, data.tree[i].elementName, 1, 0);
			}
			top.tree.LT.Tree.buildTree();
		}
	};

	var insertFieldPropertyRow = function(added) {
		$('#property_row_add_field').clone().appendTo('#property_table_field');

		$('#property_row_add_field:first').each(function(){
			this.id = 'property_row_'+added.id;
		});

		$('input:checkbox[name=isShow_add_field]:first').each(function(){
			this.name = 'isShow_'+added.id;
			this.checked = added.isShow ? true : false;
		});

		$('input:checkbox[name=isRequired_add_field]:first').each(function(){
			this.name = 'isRequired_'+added.id;
			this.checked = added.isRequired ? true : false;
		});

		$('input[name=propertyDescription_add_field]:first').each(function(){
			this.name = 'propertyDescription_'+added.id;
			this.value = added.propertyDescription;
		});

		$('input[name=propertyName_add_field]:first').each(function(){
			this.name = 'propertyName_'+added.id;
			this.value = added.propertyName;
		});

		$('select[name=propertyClass_add_field]:first').each(function(){
			if(added.propertyClass == 'VirtualProperty') {
				this.name = null;
				this.value = null;
				this.disabled = true;
				$('input[name=propertyClass_add_field_hidden]:first').each(function(){
					this.name = 'propertyClass_'+added.id;
					this.value = added.propertyClass;
				});
			} else {
				this.name = 'propertyClass_'+added.id;
				this.value = added.propertyClass;
				$('select[name=propertyClass_'+added.id+'] option:first').remove();
				$('select[name=propertyClass_'+added.id+'] option:last').remove();
			}
		});

		$('input:checkbox[name=drop_add_field]:first').each(function(){
			this.name = 'drop_'+added.id;
			$(this).css({display: 'block'});
		});

		$('#parameterListButton_add_field:first').each(function(){
			this.id = 'parameterListButton_'+added.id;
			$(this).css({display: 'block'}).click(function() {
				LT.Common.windowOpen(
					LT.Common.baseUrl+'?module=ParameterList&propertyId='+added.id,
					'ParameterList', 600, 500
				);
				return false;
			});
		});

		$('input:checkbox[name=isShow_add_field]').each(function(){this.checked = false;});
		$('input:checkbox[name=isRequired_add_field]').each(function(){this.checked = false;});
		$('input[name=propertyDescription_add_field]').each(function(){this.value = '';});
		$('input[name=propertyName_add_field]').each(function(){this.value = '';});
		$('select[name=propertyClass_add_field]').each(function(){this.selectedIndex = 0;});
	};

	var insertOneToOnePropertyRow = function(added) {
		$('#property_row_add_onetoone').clone().appendTo('#property_table_onetoone');

		$('#property_row_add_onetoone:first').each(function(){
			this.id = 'property_row_'+added.id;
		});

		$('input:hidden[name=propertyClass_add_onetoone]:first').each(function(){
			this.name = 'propertyClass_'+added.id;
		});

		$('input:checkbox[name=isShow_add_onetoone]:first').each(function(){
			this.name = 'isShow_'+added.id;
			this.checked = added.isShow ? true : false;
		});

		$('input:checkbox[name=isRequired_add_onetoone]:first').each(function(){
			this.name = 'isRequired_'+added.id;
			this.checked = added.isRequired ? true : false;
		});

		$('input[name=propertyDescription_add_onetoone]:first').each(function(){
			this.name = 'propertyDescription_'+added.id;
			this.value = added.propertyDescription;
		});

		$('input[name=propertyName_add_onetoone]:first').each(function(){
			this.name = 'propertyName_'+added.id;
			this.value = added.propertyName;
		});

		$('select[name=fetchClass_add_onetoone]:first').each(function(){
			this.name = 'fetchClass_'+added.id;
			this.value = added.fetchClass;
			this.options[0] = null;
		});

		$('select[name=fetchStrategyId_add_onetoone]:first').each(function(){
			this.name = 'fetchStrategyId_'+added.id;
			this.value = added.fetchStrategyId;
		});

		$('select[name=onDelete_add_onetoone]:first').each(function(){
			this.name = 'onDelete_'+added.id;
			this.value = added.onDelete;
		});

		$('input:checkbox[name=drop_add_onetoone]:first').each(function(){
			this.name = 'drop_'+added.id;
			$(this).css({display: 'block'});
		});

		$('#parameterListButton_add_onetoone:first').each(function(){
			this.id = 'parameterListButton_'+added.id;
			$(this).css({display: 'block'}).click(function() {
				LT.Common.windowOpen(
					LT.Common.baseUrl+'?module=ParameterList&propertyId='+added.id,
					'ParameterList', 600, 500
				);
				return false;
			});
		});

		$('input:checkbox[name=isShow_add_onetoone]').each(function(){this.checked = false;});
		$('input:checkbox[name=isRequired_add_onetoone]').each(function(){this.checked = false;});
		$('input[name=propertyDescription_add_onetoone]').each(function(){this.value = '';});
		$('input[name=propertyName_add_onetoone]').each(function(){this.value = '';});
		$('select[name=fetchClass_add_onetoone]').each(function(){this.selectedIndex = 0;});
		$('select[name=fetchStrategyId_add_onetoone]').each(function(){this.selectedIndex = 0;});
		$('select[name=onDelete_add_onetoone]').each(function(){this.selectedIndex = 0;});
	};

	var insertOneToManyPropertyRow = function(added) {
		$('#property_row_add_onetomany').clone().appendTo('#property_table_onetomany');

		$('#property_row_add_onetomany:first').each(function(){
			this.id = 'property_row_'+added.id;
		});

		$('input:hidden[name=propertyClass_add_onetomany]:first').each(function(){
			this.name = 'propertyClass_'+added.id;
		});

		$('input[name=propertyDescription_add_onetomany]:first').each(function(){
			this.name = 'propertyDescription_'+added.id;
			this.value = added.propertyDescription;
		});

		$('input[name=propertyName_add_onetomany]:first').each(function(){
			this.name = 'propertyName_'+added.id;
			this.value = added.propertyName;
		});

		$('select[name=fetchClass_add_onetomany]:first').each(function(){
			this.name = 'fetchClass_'+added.id;
			this.value = added.fetchClass;
			this.options[0] = null;
		});

		$('input:checkbox[name=drop_add_onetomany]:first').each(function(){
			this.name = 'drop_'+added.id;
			$(this).css({display: 'block'});
		});

		$('input[name=propertyDescription_add_onetomany]').each(function(){this.value = '';});
		$('input[name=propertyName_add_onetomany]').each(function(){this.value = '';});
		$('select[name=fetchClass_add_onetomany]').each(function(){this.selectedIndex = 0;});
	};

	var insertManyToManyPropertyRow = function(added) {
		$('#property_row_add_manytomany').clone().appendTo('#property_table_manytomany');

		$('#property_row_add_manytomany:first').each(function(){
			this.id = 'property_row_'+added.id;
		});

		$('input:hidden[name=propertyClass_add_manytomany]:first').each(function(){
			this.name = 'propertyClass_'+added.id;
		});

		$('input[name=propertyDescription_add_manytomany]:first').each(function(){
			this.name = 'propertyDescription_'+added.id;
			this.value = added.propertyDescription;
		});

		$('input[name=propertyName_add_manytomany]:first').each(function(){
			this.name = 'propertyName_'+added.id;
			this.value = added.propertyName;
		});

		$('select[name=fetchClass_add_manytomany]:first').each(function(){
			this.name = 'fetchClass_'+added.id;
			this.value = added.fetchClass;
			this.options[0] = null;
		});

		$('input:checkbox[name=drop_add_manytomany]:first').each(function(){
			this.name = 'drop_'+added.id;
			$(this).css({display: 'block'});
		});

		$('#parameterListButton_add_manytomany:first').each(function(){
			this.id = 'parameterListButton_'+added.id;
			$(this).css({display: 'block'}).click(function() {
				LT.Common.windowOpen(
					LT.Common.baseUrl+'?module=ParameterList&propertyId='+added.id,
					'ParameterList', 600, 500
				);
				return false;
			});
		});

		$('input[name=propertyDescription_add_manytomany]').each(function(){this.value = '';});
		$('input[name=propertyName_add_manytomany]').each(function(){this.value = '';});
		$('select[name=fetchClass_add_manytomany]').each(function(){this.selectedIndex = 0;});
	};

	return object;
}();