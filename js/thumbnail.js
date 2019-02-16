/**
 * Nextcloud - Gallery
 *
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Olivier Paroz <galleryapps@oparoz.com>
 *
 * @copyright Olivier Paroz 2017
 */
/* global $, DOMPurify, OC, Gallery */
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
				_.each(ids, function(id) {
					var thumb = new Thumbnail(id, square);
					thumb.image = new Image();
					map[id] = batch[id] = thumb;

					thumb.image.onload = function () {
						if (square) {
							thumb.image.width = 200;
							thumb.image.height = 200;
						}
						thumb.ratio = thumb.image.width / thumb.image.height;
						thumb.image.originalWidth = 200 * thumb.ratio;
						thumb.valid = true;
						thumb.status = 200;
						thumb.loadingDeferred.resolve(thumb.image);
						console.log(thumb);
					};
					thumb.image.onerror = function (data) {
						thumb.loadingDeferred.resolve(null);
					};
					var width = square ? 200 : 400;
					thumb.image.src = Gallery.utility.buildGalleryUrl('preview', '/' + id, {width: width, height: 200});
				});
			}

			return batch;
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
