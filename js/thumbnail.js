/* global OC, $, Gallery */
function Thumbnail (path, square, token) {
	this.token = token;
	this.square = square;
	this.path = path;
	this.image = null;
	this.loadingDeferred = new $.Deferred();
	this.ratio = null;
}

Thumbnail.map = {};
Thumbnail.squareMap = {};
Thumbnail.height = 200;
Thumbnail.width = 400;

Thumbnail.get = function (path, square, token) {
	var map = {};
	if (square === 1) {
		map = Thumbnail.squareMap;
		square = 1;
	} else if (square === 2) {//needed Album thumbnail preview if 2 or 3 pics
		map = Thumbnail.squareMap;
		square = 0;
	} else {
		map = Thumbnail.map;
		square = 0;
	}
	if (!map[path]) {
		map[path] = new Thumbnail(path, square, token);
	}
	return map[path];
};

Thumbnail.loadBatch = function (paths, square, token) {
	var map = (square) ? Thumbnail.squareMap : Thumbnail.map;
	paths = paths.filter(function (path) {
		return !map[path];
	});
	var thumbnails = {};
	if (paths.length) {
		paths.forEach(function (path) {
			var thumb = new Thumbnail(path, square, token);
			thumb.image = new Image();
			map[path] = thumbnails[path] = thumb;
		});

		var params = {
			images: paths.join(';'),
			scale: window.devicePixelRatio,
			square: (square) ? 1 : 0,
			token: (token) ? token : ''
		};
		var url = Gallery.buildUrl('thumbnails', params);

		var eventSource = new OC.EventSource(url);
		eventSource.listen('preview', function (preview) {
			//var status = preview.status;
			var data = preview.data;
			var path = data.image;
			var thumb = thumbnails[path];
			thumb.image.onload = function () {
				Thumbnail.loadingCount--;
				// Fix for SVG files which can come in all sizes
				if (square) {
					thumb.image.width = 200;
					thumb.image.height = 200;
				}
				thumb.image.ratio = thumb.image.width / thumb.image.height;
				thumb.image.originalWidth = 200 * thumb.image.ratio;
				thumb.loadingDeferred.resolve(thumb.image);
			};
			thumb.image.src = 'data:' + data.mimetype + ';base64,' + data.preview;
		});
	}

	return thumbnails;
};
