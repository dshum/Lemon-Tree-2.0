LT.RewriteRule = function() {
	var object = {};

	object.saveList = function() {
		LT.Common.ajaxSubmit(onSaveList);
	};

	var onSaveList = function(data) {
		if(data.dropped) {
			for(var i in data.dropped) {
				$('#rewrite_rule_row_'+data.dropped[i]).remove();
			}
		}

		if(data.added) {
			insertRewriteRuleRow(data.added);
		}
	};

	var insertRewriteRuleRow = function(added) {
		$('#rewrite_rule_row_add').clone().appendTo('#rewrite_rule_table');

		$('#rewrite_rule_row_add:first').each(function(){
			this.id = 'rewrite_rule_row_'+added.id;
		});

		$('select[name=level_add]:first').each(function(){
			this.name = 'level_'+added.id;
			this.value = added.level;
		});

		$('input[name=url_add]:first').each(function(){
			this.name = 'url_'+added.id;
			this.value = added.url;
		});

		$('select[name=item_add]:first').each(function(){
			this.name = 'item_'+added.id;
			this.value = added.itemId;
		});

		$('input[name=initialPath_add]:first').each(function(){
			this.name = 'initialPath_'+added.id;
			this.value = added.initialPath;
		});

		$('input:checkbox[name=drop_add]:first').each(function(){
			this.name = 'drop_'+added.id;
			$(this).css({display: 'block'});
		});

		$('select[name=level_add]').each(function(){this.selectedIndex = 0;});
		$('input[name=url_add]').each(function(){this.value = '';});
		$('select[name=item_add]').each(function(){this.selectedIndex = 0;});
		$('input[name=initialPath_add]').each(function(){this.value = '';});
	};

	return object;
}();