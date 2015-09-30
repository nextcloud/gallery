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
		 * @param {bool} square
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
				if (img) {
					return img.originalWidth;
				}
				return 0;
			});
		},

		/**
		 * Creates the a and img element in the DOM
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
					$(img).css({
						'height': targetHeight,
						'width': targetHeight * image.thumbnail.ratio
					});
					img.alt = encodeURI(image.path);
					var url = '#' + encodeURIComponent(image.path);

					if (!image.thumbnail.valid) {
						var params = {
							c: image.etag,
							requesttoken: oc_requesttoken
						};
						url = Gallery.utility.buildGalleryUrl('files', '/download/' + image.fileId,
							params);
					}
					var a = $('<a/>').addClass('image').attr('href', url).attr('data-path',
						image.path);

					var imageLabel = $('<span/>').addClass('image-label');
					var imageTitle = $('<span/>').addClass('title').text(
						OC.basename(image.path));
					imageLabel.append(imageTitle);
					a.hover(function () {
						imageLabel.slideToggle(OC.menuSpeed);
					}, function () {
						imageLabel.slideToggle(OC.menuSpeed);
					});
					a.append(imageLabel);
					a.append(img);
					return a;
				});
			}
			return this.domDef;
		}
	};

	window.GalleryImage = GalleryImage;
})(jQuery, Gallery, oc_requesttoken);
