/* global OC, $, Gallery */
function Thumbnail (path, square) {
	this.square = square;
	this.path = path;
	this.image = null;
	this.loadingDeferred = new $.Deferred();
	this.height = 200;
	this.width = 400;
	this.ratio = null;
	this.valid = true;
	this.status = 200;
}

var Thumbnails = {};
Thumbnails.map = {};
Thumbnails.squareMap = {};

Thumbnails.get = function (path, square) {
	var map = {};
	if (square === 1) {
		map = Thumbnails.squareMap;
		square = 1;
	} else if (square === 2) {//needed Album thumbnail preview if 2 or 3 pics
		map = Thumbnails.squareMap;
		square = 0;
	} else {
		map = Thumbnails.map;
		square = 0;
	}
	if (!map[path]) {
		map[path] = new Thumbnail(path, square);
	}
	return map[path];
};

Thumbnails.loadBatch = function (paths, square) {
	var map = (square) ? Thumbnails.squareMap : Thumbnails.map;
	paths = paths.filter(function (path) {
		return !map[path];
	});
	var batch = {};
	var i, pathsLength = paths.length;
	if (pathsLength) {
		for (i = 0; i < pathsLength; i++) {
			var thumb = new Thumbnail(paths[i], square);
			thumb.image = new Image();
			map[paths[i]] = batch[paths[i]] = thumb;
		}
		var params = {
			images: paths.join(';'),
			scale: window.devicePixelRatio,
			square: (square) ? 1 : 0
		};
		var url = Gallery.buildUrl('thumbnails', '', params);

		var eventSource = new OC.EventSource(url);
		eventSource.listen('preview', function (preview) {
			var path = preview.path;
			var thumb = batch[path];
			thumb.status = preview.status;
			thumb.image.onload = function () {
				// Fix for SVG files which can come in all sizes
				if (square) {
					thumb.image.width = 200;
					thumb.image.height = 200;
				}
				thumb.image.ratio = thumb.image.width / thumb.image.height;
				thumb.image.originalWidth = 200 * thumb.image.ratio;
				thumb.loadingDeferred.resolve(thumb.image);
			};
			thumb.image.onerror = function () {
				thumb.valid = false;
				thumb.loadingDeferred.resolve(thumb.image);
			};
			thumb.image.src = 'data:' + preview.mimetype + ';base64,' + preview.preview;
		});
	}

	return batch;
};
