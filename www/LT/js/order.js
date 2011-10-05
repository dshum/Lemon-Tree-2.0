LT.Order = function() {
	var object = {};

	object.moveUp = function() {
		var f = LT.Common.mainForm;

		$('select[name=show]').each(function() {
			if(this.selectedIndex > 0) {
				var i = this.selectedIndex;

				var t = this.options[i].text;
				this.options[i].text = this.options[i - 1].text;
				this.options[i - 1].text = t;

				var v = this.options[i].value;
				this.options[i].value = this.options[i - 1].value;
				this.options[i - 1].value = v;

				this.options[i].selected = false;
				this.options[i - 1].selected = true;

				var order1 = $('input:hidden[name=orderList['+this.options[i].value+']]');
				var order2 = $('input:hidden[name=orderList['+this.options[i - 1].value+']]');
				var v1 = order1.attr('value');
				var v2 = order2.attr('value');

				order1.attr('value', v2);
				order2.attr('value', v1);
			}
		});
	};

	object.moveDown = function() {
		var f = LT.Common.mainForm;

		$('select[name=show]').each(function() {
			if(this.selectedIndex < (this.options.length - 1) && this.selectedIndex != -1) {
				var i = this.selectedIndex;

				var t = this.options[i].text;
				this.options[i].text = this.options[i + 1].text;
				this.options[i + 1].text = t;

				var v = this.options[i].value;
				this.options[i].value = this.options[i + 1].value;
				this.options[i + 1].value = v;

				this.options[i].selected = false;
				this.options[i + 1].selected = true;

				var order1 = $('input:hidden[name=orderList['+this.options[i].value+']]');
				var order2 = $('input:hidden[name=orderList['+this.options[i + 1].value+']]');
				var v1 = order1.attr('value');
				var v2 = order2.attr('value');

				order1.attr('value', v2);
				order2.attr('value', v1);
			}
		});
	};

	object.moveFirst = function() {
		var f = LT.Common.mainForm;

		$('select[name=show]').each(function() {
			if(this.selectedIndex > 0) {
				for(var i = this.selectedIndex; i > 0; i--) {
					object.moveUp();
				}
			}
		});
	};

	object.moveLast = function() {
		var f = LT.Common.mainForm;

		$('select[name=show]').each(function() {
			if(this.selectedIndex > -1) {
				for(var i = this.selectedIndex; i < (this.options.length - 1); i++) {
					object.moveDown();
				}
			}
		});
	};

	return object;
}();

$(function() {
	$('#order_first').click(function(){
		LT.Order.moveFirst();
	}).mousedown(function(){
		$(this).css({backgroundPosition: '0 -45px'});
	}).mouseup(function(){
		$(this).css({backgroundPosition: '0 0'});
	}).mouseover(function(){
		$(this).css({backgroundPosition: '0 0'});
	}).mouseout(function(){
		$(this).css({backgroundPosition: '0 -90px'});
	}).css({backgroundPosition: '0 -90px'});

	$('#order_up').click(function(){
		LT.Order.moveUp();
	}).mousedown(function(){
		$(this).css({backgroundPosition: '0 -45px'});
	}).mouseup(function(){
		$(this).css({backgroundPosition: '0 0'});
	}).mouseover(function(){
		$(this).css({backgroundPosition: '0 0'});
	}).mouseout(function(){
		$(this).css({backgroundPosition: '0 -90px'});
	}).css({backgroundPosition: '0 -90px'});

	$('#order_down').click(function(){
		LT.Order.moveDown();
	}).mousedown(function(){
		$(this).css({backgroundPosition: '0 -45px'});
	}).mouseup(function(){
		$(this).css({backgroundPosition: '0 0'});
	}).mouseover(function(){
		$(this).css({backgroundPosition: '0 0'});
	}).mouseout(function(){
		$(this).css({backgroundPosition: '0 -90px'});
	}).css({backgroundPosition: '0 -90px'});

	$('#order_last').click(function(){
		LT.Order.moveLast();
	}).mousedown(function(){
		$(this).css({backgroundPosition: '0 -45px'});
	}).mouseup(function(){
		$(this).css({backgroundPosition: '0 0'});
	}).mouseover(function(){
		$(this).css({backgroundPosition: '0 0'});
	}).mouseout(function(){
		$(this).css({backgroundPosition: '0 -90px'});
	}).css({backgroundPosition: '0 -90px'});
});

