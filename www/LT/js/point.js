LT.Point = function() {
	var object = {};

	var origX = 0;
	var origY = 0;
	var currX = 0;
	var currY = 0;
	var pointImageWidth = 0;
	var pointImageHeight = 0;
	var mapImageWidth = 0;
	var mapImageHeight = 0;

	object.init = function(propertyName, x, y) {
		origX = x;
		origY = y;
		pointImageWidth = $('#'+propertyName+'_point').width();
		pointImageHeight = $('#'+propertyName+'_point').height();
		mapImageWidth = $('#'+propertyName+'_map').width();
		mapImageHeight = $('#'+propertyName+'_map').height();

		if(x || y) set(propertyName, x, y);

		$('#'+propertyName+'_reset').click(function() {
			set(propertyName, origX, origY);
		});

		$('#'+propertyName+'_unset').click(function() {
			unset(propertyName);
		});

		$('#'+propertyName+'_move').click(function() {
			$('#'+propertyName+'_inputfocus').focus();
			$('#'+propertyName+'_point').css({visibility: 'visible'});
		});

		$('#'+propertyName+'_inputfocus').keydown(function(e) {
			var x = currX;
			var y = currY;

			switch(e.keyCode) {
				case 37: if(x > 0) x--; break;
				case 38: if(y > 0) y--; break;
				case 39: if(x < mapImageWidth) x++; break;
				case 40: if(y < mapImageHeight) y++; break;
				case 8: x = origX; y = origY; break;
				case 46: x = 0; y = 0; break;
				default: break;
			}

			set(propertyName, x, y);
		});

		$('#'+propertyName+'_map').mousedown(function(e) {
			var offset = $(this).offset();
			var scrollLeft = $(document).scrollLeft();
			var scrollTop = $(document).scrollTop();
			var x = e.clientX + scrollLeft - offset.left;
			var y = e.clientY + scrollTop - offset.top;

			set(propertyName, x, y);
		}).mouseup(function() {
			$('#'+propertyName+'_inputfocus').focus();
		});

		$('#'+propertyName+'_point').mousedown(function(e) {
			$('#'+propertyName+'_inputfocus').focus();
		}).mouseup(function() {
			$('#'+propertyName+'_inputfocus').focus();
		});
	};

	var set = function(propertyName, x, y) {
		var x = parseInt(x);
		var y = parseInt(y);
		var left = (x - Math.ceil(pointImageWidth / 2) + 1);
		var top = (y - Math.ceil(pointImageHeight / 2) + 1);

		currX = x;
		currY = y;

		$('#'+propertyName).val(x+','+y);

		$('#'+propertyName+'_point').css({
			left: left+'px',
			top: top+'px',
			visibility: 'visible'
		}).attr({
			alt: x+', '+y,
			title: x+', '+y
		});

		$('#'+propertyName+'_hint').html(x+'&#215;'+y);
	};

	var unset = function(propertyName) {
		$('#'+propertyName).val('');

		$('#'+propertyName+'_point').css({visibility: 'hidden'});

		$('#'+propertyName+'_hint').html('Не определено');
	};

	return object;
}();