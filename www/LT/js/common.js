var LT = function() {
	return {};
}();

LT.Common = function() {
	var object = {};

	object.baseUrl = null;
	object.selfUrl = null;
	object.mainForm = null;

	var customOnSuccess = null;
	var lockForm = false;

	object.windowOpen = function(url, name, width, height) {
		var w = window.open(
			url,
			name,
			'width='+width+
			',height = '+height+
			',status = yes, menubar = no, resizable = no, scrollbars = yes,'+
			'left = '+String((screen.width - width)/2)
			+',top = '+String((screen.height - height)/2));

		w.focus();

		return w;
	};

	object.setTimeExpire = function(timeExpire) {
		var minute = Math.floor(timeExpire / 60);
		var second = timeExpire % 60;
		var strMinute = minute < 10 ? '0'+minute : minute;
		var strSecond = second < 10 ? '0'+second : second;

		$('#timeExpire').html('00:'+strMinute+':'+strSecond);

		timeExpire--;

		if(timeExpire < 1) {
			$('form[name=LogoutForm]').submit();
			return false;
		}

		setTimeout('LT.Common.setTimeExpire('+timeExpire+')', 1000);
	};

	object.onCtrlS = function(e) {
		if(!e) var e = window.event;

		if(e.keyCode) {
			var code = e.keyCode;
		} else if(e.which) {
			var code = e.which;
		}

		if(code == 83 && e.ctrlKey == true) {
			$(object.mainForm).submit();
			return false;
		}

		return true;
	};

	object.ajaxSubmit = function(onSuccess) {
		if(!LT.Common.mainForm) return false;
		if(lockForm) return false;

		lockForm = true;
		$('save_button').each(function() {this.disabled = true;});
		object.showIndicator();

		if(onSuccess) {
			customOnSuccess = onSuccess;
		}

		$(object.mainForm).ajaxSubmit({
			url: this.action,
			dataType: 'json',
			success: object.onSuccess
		});

		return false;
	};

	object.ajaxPost = function(url, data, onSuccess) {
		if(lockForm) return false;

		lockForm = true;
		$('save_button').each(function() {this.disabled = true;});
		object.showIndicator();

		if(onSuccess) {
			customOnSuccess = onSuccess;
		}

		$.post(url, data, object.onSuccess, 'json');

		return false;
	};


	object.onSuccess = function(data) {
		if(data.error && data.error.length) {
			var str = '';
			for(var i in data.error) {
				str += data.error[i]+'<br>';
			}
			LT.Alert.alert(str);
		} else if(customOnSuccess) {
			$(customOnSuccess(data));
			customOnSuccess = null;
			object.unlock();
		} else {
			object.unlock();
		}
	};

	object.refreshElementList = function(elementId, itemId) {
		object.showIndicator();
		$.post(
			object.selfUrl+'&action=show',
			{
				elementId: elementId,
				itemId: itemId,
				filter: true
			},
			function(data) {
				var itemId = data.itemId;
				var elementListContent = data.elementListContent;
				if(itemId && elementListContent) {
					elementListContent = elementListContent.replace(/\[\[\[/g, '<').replace(/\]\]\]/g, '>');
					$('#item_'+itemId).html(elementListContent);
				}
				object.hideIndicator();
			},
			'json'
		);
	};

	object.unlock = function() {
		object.hideIndicator();
		$('save_button').each(function() {this.disabled = false;});
		lockForm = false;
	};

	object.showIndicator = function() {
		LT.Alert.block();
	};

	object.hideIndicator = function() {
		LT.Alert.unblock();
	};

	object.escapeElementId = function(id) {
		return id.replace(/\./, '\\.');
	};

	return object;
}();