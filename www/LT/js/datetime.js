LT.Datetime = function() {
	var object = {};

	object.onTimeMousewheel = function(object, delta) {
		var max = object.attr('max');
		var val = object.val();

		val = Math.round(val) + (delta > 0 ? 1 : -1);

		if(val > max) val = 0;
		if(val < 0) val = max;
		if(val < 10) val = '0'+val;

		object.val(val);
	}

	object.setTimestamp = function(propertyName) {
		$('#'+propertyName).val(
			$('#'+propertyName+'_date').val()
			+' '+$('#'+propertyName+'_hour').val()
			+':'+$('#'+propertyName+'_minute').val()
			+':'+$('#'+propertyName+'_second').val()
		);
	}

	object.setTime = function(propertyName) {
		if(
			$('#'+propertyName+'_hour').val()
			|| $('#'+propertyName+'_minute').val()
			|| $('#'+propertyName+'_second').val()
		) {
			if(!$('#'+propertyName+'_hour').val()) {
				$('#'+propertyName+'_hour').val('00');
			}
			if(!$('#'+propertyName+'_minute').val()) {
				$('#'+propertyName+'_minute').val('00');
			}
			if(!$('#'+propertyName+'_second').val()) {
				$('#'+propertyName+'_second').val('00');
			}
			$('#'+propertyName).val(
				$('#'+propertyName+'_hour').val()
				+':'+$('#'+propertyName+'_minute').val()
				+':'+$('#'+propertyName+'_second').val()
			);
		} else {
			$('#'+propertyName).val(null);
		}
	}

	return object;
}();