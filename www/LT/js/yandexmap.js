LT.YandexMap = function() {
	var object = {};

	var map = new Array(), placemark = new Array();

	object.init = function(propertyName, centerX, centerY, scale) {
		map[propertyName] = new YMaps.Map($('#'+propertyName+'_map')[0]);

		map[propertyName].setCenter(new YMaps.GeoPoint(centerX, centerY), scale);

		map[propertyName].addControl(new YMaps.Zoom());

		YMaps.Events.observe(
			map[propertyName],
			map[propertyName].Events.BoundsChange,
			function (e) {
				$('#'+propertyName).val(
					map[propertyName].getCenter().toString()
					+','+map[propertyName].getZoom()
				);
			}
		);
	};

	object.set = function(propertyName) {
		if(placemark[propertyName]) {
			map[propertyName].removeOverlay(placemark[propertyName]);
		}

		placemark[propertyName] = new YMaps.Placemark(
			map[propertyName].getCenter(),
			{draggable: true}
		);

		map[propertyName].addOverlay(placemark[propertyName]);

		YMaps.Events.observe(
			placemark[propertyName],
			placemark[propertyName].Events.Drag,
			function (e) {
				$('#'+propertyName+'_hint').html(
					e.getGeoPoint().toString(3).replace(',', ', ')
				);
			}
		);

		YMaps.Events.observe(
			placemark[propertyName],
			placemark[propertyName].Events.DragEnd,
			function (e) {
				$('#'+propertyName).val(
					e.getGeoPoint().toString()
					+','+map[propertyName].getZoom()
				);
			}
		);

		$('#'+propertyName+'_hint').html(
			map[propertyName].getCenter().toString(3).replace(',', ', ')
		);

		$('#'+propertyName).val(
			map[propertyName].getCenter().toString()
			+','+map[propertyName].getZoom()
		);
	};

	object.search = function(propertyName) {
		var value = $('#'+propertyName+'_query').val();
		var geocoder = new YMaps.Geocoder(
			value,
			{
				results: 1,
				boundedBy: map[propertyName].getBounds()
			}
		);

		YMaps.Events.observe(
			geocoder,
			geocoder.Events.Load,
			function () {
				if(this.length()) {
					var result = this.get(0);
					map[propertyName].setBounds(result.getBounds());
					object.set(propertyName);
				} else {
					alert('Ничего не найдено.');
				}
			}
		);
	};

	object.unset = function(propertyName) {
		if(placemark[propertyName]) {
			map[propertyName].removeOverlay(placemark[propertyName]);
		}
		$('#'+propertyName+'_hint').html('не определено');
		$('#'+propertyName).val('');
	};

	return object;
}();