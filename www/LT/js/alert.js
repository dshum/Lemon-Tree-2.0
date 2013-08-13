LT.Alert = function() {
	var object = {};

	object.block = function () {
		$.blockUI({
			message: $('<div><img src="/LT/img/loader3.gif"></div>'),
			css: {
				border: 'none',
				backgroundColor: 'none',
				cursor: 'arrow'
			},
			overlayCSS: {
				backgroundColor: '#FFF',
				cursor: 'arrow'
			}
		});
	};

	object.unblock = function() {
		$.unblockUI();
	};

	object.alert = function(html) {
		var str = '';
		str += '<p>'+html+'<p>';
		str += '<p><input type="button" id="alert-block-ok" value="OK"></p>';

		$.blockUI({
			message: str,
			css: {
				width: '300px',
				color: '#000',
				cursor: 'arrow'
			},
			overlayCSS: {
				backgroundColor: '#FFF'
			},
			cursorReset: 'default'
		});

		$('#alert-block-ok').click(function() {
			LT.Common.unlock();
			return false;
		});
	};

	object.confirm = function(html, action) {
		var str = '';
		str += '<p align="left">'+html+'<p>';
		str += '<p><button id="alert-block-ok">OK</button> &nbsp; <button id="alert-block-cancel">Отмена</button></p>';

		$.blockUI({
			message: str,
			css: {
				width: '300px',
				padding: '10px',
				color: '#000',
				cursor: 'arrow'
			},
			overlayCSS: {
				backgroundColor: '#FFF'
			},
			cursorReset: 'default'
		});

		$('#alert-block-ok').click(action);

		$('#alert-block-cancel').click(function() {
			LT.Common.unlock();
			return false;
		});
	};

	return object;
}();