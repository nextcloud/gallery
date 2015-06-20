/* global $, Gallery */
/**
 * A thumbnail is the actual image attached to the GalleryImage object
 *
 * @param {number} fileId
 * @param {bool} square
 * @constructor
 */
function Thumbnail (fileId, square) {
	this.square = square;
	this.fileId = fileId;
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

/**
 * Retrieves the thumbnail linked to the given fileID
 *
 * @param {number} fileId
 * @param {bool} square
 *
 * @returns {Thumbnail}
 */
Thumbnails.get = function (fileId, square) {
	var map = {};
	if (square === true) {
		map = Thumbnails.squareMap;
		square = true;
	} else {
		map = Thumbnails.map;
		square = false;
	}
	if (!map[fileId]) {
		map[fileId] = new Thumbnail(fileId, square);
	}

	return map[fileId];
};

/**
 * Loads thumbnails in batch, using EventSource
 *
 * @param {array} ids
 * @param {bool} square
 *
 * @returns {{}}
 */
Thumbnails.loadBatch = function (ids, square) {
	var map = (square) ? Thumbnails.squareMap : Thumbnails.map;
	// Purely here as a precaution
	ids = ids.filter(function (id) {
		return !map[id];
	});
	var batch = {};
	var i, idsLength = ids.length;
	if (idsLength) {
		for (i = 0; i < idsLength; i++) {
			var thumb = new Thumbnail(ids[i], square);
			thumb.image = new Image();
			map[ids[i]] = batch[ids[i]] = thumb;

		}
		var params = {
			ids: ids.join(';'),
			scale: window.devicePixelRatio,
			square: (square) ? 1 : 0
		};
		var url = Gallery.utility.buildGalleryUrl('thumbnails', '', params);

		var eventSource = new Gallery.EventSource(url);
		eventSource.listen('preview', function (/**{path, status, mimetype, preview}*/ preview) {
			var id = preview.fileid;
			var thumb = batch[id];
			thumb.status = preview.status;
			if (thumb.status === 404) {
				thumb.valid = false;
				thumb.loadingDeferred.resolve(null);
			} else {
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
					thumb.loadingDeferred.resolve(null);
				};

				if (thumb.status === 200) {
					thumb.image.src = 'data:' + preview.mimetype + ';base64,' + preview.preview;
				} else {
					thumb.valid = false;
					thumb.image.src = Gallery.mediaTypes[preview.mimetype];

				}
			}
		});
	}

	return batch;
};
