/* global $, DOMPurify, Gallery */
/**
 * A thumbnail is the actual image attached to the GalleryImage object
 *
 * @param {number} fileId
 * @param {boolean} square
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

(function ($, OC, Gallery) {
	"use strict";
	var Thumbnails = {
		map: {},
		squareMap: {},

		/**
		 * Retrieves the thumbnail linked to the given fileID
		 *
		 * @param {number} fileId
		 * @param {boolean} square
		 *
		 * @returns {Thumbnail}
		 */
		get: function (fileId, square) {
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
		},

		/**
		 * Returns an icon of a specific type
		 *
		 * -1 is for a folder
		 * -404 is for a broken file icon
		 * -500 is for a media type icon
		 *
		 * @param {number} type
		 *
		 * @returns {Thumbnail}
		 */
		getStandardIcon: function (type) {
			if (!Thumbnails.squareMap[type]) {
				var icon = '';
				// true means square
				var thumb = new Thumbnail(type, true);
				thumb.image = new Image();
				thumb.image.onload = function () {
					thumb.loadingDeferred.resolve(thumb.image);
				};

				if (type === -1) {
					icon = 'filetypes/folder';
				}
				thumb.image.src = OC.imagePath('core', icon);

				Thumbnails.squareMap[type] = thumb;
			}

			return Thumbnails.squareMap[type];
		},

		/**
		 * Loads thumbnails in batch, using EventSource
		 *
		 * @param {Array} ids
		 * @param {boolean} square
		 *
		 * @returns {{}}
		 */
		loadBatch: function (ids, square) {
			var map = (square) ? Thumbnails.squareMap : Thumbnails.map;
			// Prevents re-loading thumbnails when resizing the window
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
				eventSource.listen('preview',
					function (/**{path, status, mimetype, preview}*/ preview) {
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
								thumb.ratio = thumb.image.width / thumb.image.height;
								thumb.image.originalWidth = 200 * thumb.ratio;
								thumb.loadingDeferred.resolve(thumb.image);
							};
							thumb.image.onerror = function () {
								thumb.valid = false;
								var icon = Thumbnails._getMimeIcon(preview.mimetype);
								setTimeout(function () {
									thumb.image.src = icon;
								}, 0);
							};

							if (thumb.status === 200) {
								var imageData = preview.preview;
								if (preview.mimetype === 'image/svg+xml') {
									imageData = Thumbnails._purifySvg(imageData);
								}
								thumb.image.src =
									'data:' + preview.mimetype + ';base64,' + imageData;
							} else {
								thumb.valid = false;
								thumb.image.src = Thumbnails._getMimeIcon(preview.mimetype);
							}
						}
					});
			}

			return batch;
		},

		/**
		 * Returns the link to the media type icon
		 *
		 * Modern browsers get an SVG, older ones a PNG
		 *
		 * @param mimeType
		 *
		 * @returns {*|string}
		 * @private
		 */
		_getMimeIcon: function (mimeType) {
			var icon = OC.MimeType.getIconUrl(mimeType);
			if (Gallery.ieVersion !== false) {
				icon = icon.substr(0, icon.lastIndexOf(".")) + ".png";
			}
			return icon;
		},

		/**
		 * Sanitises SVGs
		 *
		 * We also fix a problem which arises when the XML contains comments
		 *
		 * @param imageData
		 * @returns {string|*}
		 * @private
		 */
		_purifySvg: function (imageData) {
			var pureSvg = DOMPurify.sanitize(window.atob(imageData), {ADD_TAGS: ['filter']});
			// Remove XML comment garbage left in the purified data
			var badTag = pureSvg.indexOf(']&gt;');
			var fixedPureSvg = pureSvg.substring(badTag < 0 ? 0 : 5, pureSvg.length);
			imageData = window.btoa(fixedPureSvg);

			return imageData;
		}

	};

	window.Thumbnails = Thumbnails;
})(jQuery, OC, Gallery);
