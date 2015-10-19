/* global oc_requesttoken, Gallery, Thumbnails */
(function ($, Gallery, oc_requesttoken) {
	"use strict";
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
				this.domDef = this.getThumbnail(false).then(function (img) {
					var container = $('<div/>')
						.addClass('item-container image-container')
						.css('width', targetHeight * image.thumbnail.ratio)
						.css('height', targetHeight);
					image._addLabel(container);

					var newWidth = Math.round(targetHeight * image.thumbnail.ratio);
					container.attr('data-width', newWidth)
						.attr('data-height', targetHeight);

					var url = image._getLink();
					var a = $('<a/>')
						.addClass('image')
						.attr('href', url)
						.attr('data-path', image.path);

					// This will stretch wide images to make them reach targetHeight
					$(img).css({
						'width': targetHeight * image.thumbnail.ratio,
						'height': targetHeight
					});
					img.alt = encodeURI(image.path);
					a.append(img);
					container.append(a);

					return container;
				});
			}
			return this.domDef;
		},

		/**
		 * Adds a label to the album
		 *
		 * @param container
		 * @private
		 */
		_addLabel: function (container) {
			var imageLabel = $('<span/>')
				.addClass('image-label');
			var imageTitle = $('<span/>')
				.addClass('title').text(
					OC.basename(this.path));
			imageLabel.append(imageTitle);
			container.hover(function () {
				imageLabel.slideToggle(OC.menuSpeed);
			}, function () {
				imageLabel.slideToggle(OC.menuSpeed);
			});
			container.append(imageLabel);
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
