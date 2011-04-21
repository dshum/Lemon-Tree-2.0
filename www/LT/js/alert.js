LT.Alert = function() {
	var object = {};

	object.alert = function(options) {
		var str = '';

		var alertWindow = $('<div class="alert"></div>').css({
			position: 'absolute',
			zIndex: 100,
			backgroundColor: '#FFFFFF',
			border: '1px solid #555555',
			padding: '5px',
			fontSize: '120%',
			color: '#000000',
			display: 'none'
		});


		str += options.html;
		str += '<br><br>';
		str += '<button class="alert-button">OK</button>';

		alertWindow.html(str).appendTo(document.body).insertBefore(
			document.body.firstChild
		).css({
			top: parseInt(($(window).height() - alertWindow.height()) / 2 + $(window).scrollTop())+'px',
			left: parseInt(($(window).width() - alertWindow.width()) / 2)+'px',
			filter: 'alpha(opacity=1)',
			opacity: '0.01',
			display: 'block'
		}).fadeTo(200, 0.8);

		$('button.alert-button').click(function() {
			$('.alert').fadeOut(100, function() {
				$(this).remove();
			});
			LT.Common.unlock();
		}).focus();
	};

	object.confirm = function(options) {

	};

	object.block = function () {
		$(document.body).css({overflowX: 'hidden'});

		$('<div class="blocker"></div>').css({
			width: $(document).width(),
			height: $(document).height(),
			position: 'absolute',
			top: 0,
			left: 0,
			zIndex: 90,
			overflow: 'hidden',
			backgroundColor: '#FFFFFF',
			display: 'none'
		}).appendTo(document.body).insertBefore(document.body.firstChild).css({
			filter: 'alpha(opacity=1)',
			opacity: '0.01',
			display: 'block'
		}).fadeTo(200, 0.6);

		$('<div class="blocker-image"></div>').css({
			width: $(window).width(),
			height: $(window).height(),
			position: 'absolute',
			top: $(window).scrollTop()+'px',
			left: 0,
			zIndex: 95,
			overflow: 'hidden',
			background: 'url(/LT/img/loader3.gif) no-repeat center',
			display: 'block'
		}).appendTo(document.body).insertAfter(document.body.firstChild);

		$('<textarea></textarea>').css({
			filter: 'alpha(opacity=0)',
			opacity: '0',
			width: '1px',
			height: '1px',
			position: 'absolute',
			top: ($(window).scrollTop() + 100)+'px',
			left: 0
		 }).appendTo(document.body).focus().css({
			display: 'none'
		 }).remove();
	};

	object.unblock = function() {
		$('.blocker-image').remove();
		$('.blocker').fadeOut(100, function() {
		 	$(document.body).css({overflowX: 'auto'});
			$(this).remove();
		});
	};

	return object;
}();