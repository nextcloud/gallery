/* global Handlebars, oc_requesttoken, Gallery, Thumbnails */
(function ($, Gallery, oc_requesttoken) {
	"use strict";

	var TEMPLATE =
		'<div class="item-container image-container" ' +
		'style="width: {{targetWidth}}px; height: {{targetHeight}}px;" ' +
		'data-width="{{targetWidth}}" data-height="{{targetHeight}}">' +
		'	<span class="image-label">' +
		'		<span class="title">{{label}}</span>' +
		'	</span>' +
		'	<a class="image" href="{{targetPath}}" data-path="{{path}}"></a>' +
		'</div>';

	/**
	 * Creates a new image object to store information about a media file
	 *
	 * @param src
	 * @param path
	 * @param fileId
	 * @param mimeType
	 * @param mTime modification time
	 * @param etag
	 * @constructor
	 */
	var GalleryImage = function (src, path, fileId, mimeType, mTime, etag) {
		this.src = src;
		this.path = path;
		this.fileId = fileId;
		this.mimeType = mimeType;
		this.mTime = mTime;
		this.etag = etag;
		this.thumbnail = null;
		this.domDef = null;
		this.domHeight = null;
	};

	GalleryImage.prototype = {
		/**
		 * Returns the Thumbnail ID
		 *
		 * @returns {[number]}
		 */
		getThumbnailIds: function () {
			return [this.fileId];
		},

		/**
		 * Returns a reference to a loading Thumbnail.image
		 *
		 * @param {boolean} square
		 *
		 * @returns {jQuery.Deferred<Thumbnail.image>}
		 */
		getThumbnail: function (square) {
			if (this.thumbnail === null) {
				this.thumbnail = Thumbnails.get(this.fileId, square);
			}
			return this.thumbnail.loadingDeferred;
		},

		/**
		 * Returns the width of a thumbnail
		 *
		 * Used to calculate the width of the row as we add more images to it
		 *
		 * @returns {number}
		 */
		getThumbnailWidth: function () {
			// img is a Thumbnail.image
			return this.getThumbnail(false).then(function (img) {
				var width = 0;
				if (img) {
					width = img.originalWidth;
				}

				return width;
			});
		},

		/**
		 * Creates the container, the a and img elements in the DOM
		 *
		 * Each image is also a link to start the full screen slideshow
		 *
		 * @param {number} targetHeight
		 *
		 * @return {a}
		 */
		getDom: function (targetHeight) {
			var image = this;
			if (this.domDef === null || this.domHeight !== targetHeight) {
				this.domHeight = targetHeight;
				// img is a Thumbnail.image
				return this.getThumbnail(false).then(function (img) {
					if (!image._template) {
						image._template = Handlebars.compile(TEMPLATE);
					}
					var newWidth = Math.round(targetHeight * image.thumbnail.ratio);
					var url = image._getLink();
					var template = image._template({
						targetHeight: targetHeight,
						targetWidth: newWidth,
						label: OC.basename(image.path),
						targetPath: url,
						path: image.path
					});
					image.domDef = $(template);
					image._addLabel();
					// This will stretch wide images to make them reach targetHeight
					$(img).css({
						'width': newWidth,
						'height': targetHeight
					});
					img.alt = encodeURI(image.path);
					image.domDef.children('a').append(img);

					return image.domDef;
				});
			}
			return $.Deferred().resolve(this.domDef);
		},

		/**
		 * Adds a label to the album
		 *
		 * @private
		 */
		_addLabel: function () {
			var imageLabel = this.domDef.children('.image-label');
			this.domDef.hover(function () {
				imageLabel.slideToggle(OC.menuSpeed);
			}, function () {
				imageLabel.slideToggle(OC.menuSpeed);
			});
		},

		/**
		 * Generates the link for the click action of the image
		 *
		 * @returns {string}
		 * @private
		 */
		_getLink: function () {
			var url = '#' + encodeURIComponent(this.path);
			if (!this.thumbnail.valid) {
				var params = {
					c: this.etag,
					requesttoken: oc_requesttoken
				};
				url = Gallery.utility.buildGalleryUrl(
					'files',
					'/download/' + this.fileId,
					params
				);
			}

			return url;
		}
	};

	window.GalleryImage = GalleryImage;
})(jQuery, Gallery, oc_requesttoken);
