/* global Gallery, Thumbnails, GalleryImage */
(function ($, Gallery) {
	"use strict";
	/**
	 * Creates a new album object to store information about an album
	 *
	 * @param {string} path
	 * @param {Array<Album|GalleryImage>} subAlbums
	 * @param {Array<Album|GalleryImage>} images
	 * @param {string} name
	 * @constructor
	 */
	var Album = function (path, subAlbums, images, name) {
		this.path = path;
		this.subAlbums = subAlbums;
		this.images = images;
		this.viewedItems = 0;
		this.name = name;
		this.domDef = null;
		this.preloadOffset = 0;
	};

	Album.prototype = {
		/**
		 * Creates the album, which will include between 1 and 4 images
		 *
		 *    * Each album is also a link to open that folder
		 *    * An album has a natural size of 200x200 and is comprised of 4 thumbnails which have a
		 * natural size of 200x200 The whole thing gets resized to match the targetHeight
		 *    * Thumbnails are checked first in order to make sure that we have something to show
		 *
		 * @param {number} targetHeight Each row has a specific height
		 *
		 * @return {a} The album to be placed on the row
		 */
		getDom: function (targetHeight) {
			var album = this;

			return this._getThumbnail().then(function () {
				var a = $('<a/>').addClass('album').attr('href',
					'#' + encodeURIComponent(album.path));

				a.append($('<span/>').addClass('album-label').text(album.name));

				a.width(targetHeight);
				a.height(targetHeight);

				album._fillSubAlbum(targetHeight, a);

				return a;
			});
		},

		/**
		 * Fills the row with albums and images
		 *
		 * @param {number} width
		 * @returns {$.Deferred<Gallery.Row>}
		 */
		getNextRow: function (width) {
			var numberOfThumbnailsToPreload = 6;
			var buffer = 5;

			/**
			 * Add images to the row until it's full
			 *
			 * @todo The number of images to preload should be a user setting
			 *
			 * @param {Album} album
			 * @param {Row} row
			 * @param {Array<Album|GalleryImage>} images
			 *
			 * @returns {$.Deferred<Gallery.Row>}
			 */
			var addRowElements = function (album, row, images) {
				if ((album.viewedItems + buffer) > album.preloadOffset &&
					(album.preloadOffset < images.length)) {
					album._preload(numberOfThumbnailsToPreload);
				}

				var image = images[album.viewedItems];
				return row.addElement(image).then(function (more) {
					album.viewedItems++;
					if (more && album.viewedItems < images.length) {
						return addRowElements(album, row, images);
					}
					return row;
				});
			};
			var items = this.subAlbums.concat(this.images);
			var row = new Gallery.Row(width, this.requestId);
			return addRowElements(this, row, items);
		},

		/**
		 * Returns IDs of thumbnails belonging to the album
		 *
		 * @param {number} count
		 *
		 * @return number[]
		 */
		getThumbnailIds: function (count) {
			var ids = [];
			var items = this.images.concat(this.subAlbums);
			for (var i = 0; i < items.length && i < count; i++) {
				ids = ids.concat(items[i].getThumbnailIds(count));
			}

			return ids;
		},

		/**
		 * Returns the first thumbnail it finds
		 *
		 * @returns {*}
		 * @private
		 */
		_getThumbnail: function () {
			if (this.images.length) {
				return this.images[0].getThumbnail(true);
			}
			return this.subAlbums[0]._getThumbnail();
		},

		/**
		 * Retrieves a thumbnail and adds it to the album representation
		 *
		 * Only attaches valid thumbnails to the album
		 *
		 * @param {GalleryImage} image
		 * @param {number} targetHeight Each row has a specific height
		 * @param {number} calcWidth Album width
		 * @param {jQuery} imageHolder
		 *
		 * @returns {$.Deferred<Thumbnail>}
		 * @private
		 */
		_getOneImage: function (image, targetHeight, calcWidth, imageHolder) {
			// img is a Thumbnail.image, true means square thumbnails
			return image.getThumbnail(true).then(function (img) {
				if (image.thumbnail.valid) {
					var backgroundHeight, backgroundWidth;
					img.alt = '';

					backgroundHeight = (targetHeight / 2);
					backgroundWidth = calcWidth - 2.01;

					// Adjust the size because of the margins around pictures
					backgroundHeight -= 2;

					imageHolder.css("background-image", "url('" + img.src + "')");
					imageHolder.css("height", backgroundHeight);
					imageHolder.css("width", backgroundWidth);
				}
			});
		},

		/**
		 * Builds the album representation by placing 1 to 4 images on a grid
		 *
		 * @param {array<GalleryImage>} images
		 * @param {number} targetHeight Each row has a specific height
		 * @param {object} a
		 *
		 * @returns {$.Deferred<array>}
		 * @private
		 */
		_getFourImages: function (images, targetHeight, a) {
			var calcWidth = targetHeight / 2;
			var targetWidth;
			var imagesCount = images.length;
			var def = new $.Deferred();
			var validImages = [];
			var fail = false;
			var thumbsArray = [];

			for (var i = 0; i < imagesCount; i++) {
				targetWidth = calcWidth;
				if (imagesCount === 2 || (imagesCount === 3 && i === 0)) {
					targetWidth = calcWidth * 2;
				}
				targetWidth = targetWidth.toFixed(3);

				// Append the div first in order to not lose the order of images
				var imageHolder = $('<div class="cropped">');
				a.append(imageHolder);
				thumbsArray.push(
					this._getOneImage(images[i], targetHeight, targetWidth, imageHolder));
			}

			var labelWidth = (targetHeight - 0.01);
			a.find('.album-label').width(labelWidth);

			// This technique allows us to wait for all objects to be resolved before making a
			// decision
			$.when.apply($, thumbsArray).done(function () {
				for (var i = 0; i < imagesCount; i++) {
					// Collect all valid images, just in case
					if (images[i].thumbnail.valid) {
						validImages.push(images[i]);
					} else {
						fail = true;
					}
				}

				// At least one thumbnail could not be retrieved
				if (fail) {
					// Clean up the album
					a.children().not('.album-label').remove();
					// Send back the list of images which have thumbnails
					def.reject(validImages);
				}
			});

			return def.promise();
		},

		/**
		 * Fills the album representation with images we've received
		 *
		 *    * Each album includes between 1 and 4 images
		 *    * Each album is also a link to open that folder
		 *    * An album has a natural size of 200x200 and is comprised of 4 thumbnails which have a
		 * natural size of 200x200 The whole thing gets resized to match the targetHeight
		 *
		 * @param {number} targetHeight
		 * @param a
		 * @private
		 */
		_fillSubAlbum: function (targetHeight, a) {
			var album = this;

			if (this.images.length > 1) {
				this._getFourImages(this.images, targetHeight, a).fail(function (validImages) {
					album.images = validImages;
					album._fillSubAlbum(targetHeight, a);
				});
			} else if (this.images.length === 1) {
				this._getOneImage(this.images[0], 2 * targetHeight, targetHeight,
					a).fail(function () {
						album.images = [];
						album._showFolder(targetHeight, a);
					});
			} else {
				this._showFolder(targetHeight, a);
			}
		},

		/**
		 * Shows a folder icon in the album since we couldn't get any proper thumbnail
		 *
		 * @param {number} targetHeight
		 * @param a
		 * @private
		 */
		_showFolder: function (targetHeight, a) {
			var image = new GalleryImage('Generic folder', 'Generic folder', -1, 'image/png',
				Gallery.token);
			var thumb = Thumbnails.getStandardIcon(-1);
			image.thumbnail = thumb;
			this.images.push(image);
			thumb.loadingDeferred.done(function (img) {
				a.append(img);
				img.height = (targetHeight - 2);
				img.width = (targetHeight) - 2;
			});
		},

		/**
		 * Preloads the first $count thumbnails
		 *
		 * @param {number} count
		 * @private
		 */
		_preload: function (count) {
			var items = this.subAlbums.concat(this.images);
			var realCounter = 0;
			var maxThumbs = 0;
			var fileIds = [];
			var squareFileIds = [];
			for (var i = this.preloadOffset; i < this.preloadOffset + count &&
			i < items.length; i++) {
				if (items[i].subAlbums) {
					maxThumbs = 4;
					var imagesLength = items[i].images.length;
					if (imagesLength > 0 && imagesLength < 4) {
						maxThumbs = imagesLength;
					}
					var squareFileId = items[i].getThumbnailIds(maxThumbs);
					squareFileIds = squareFileIds.concat(squareFileId);
					realCounter = realCounter + maxThumbs;
				} else {
					var fileId = items[i].getThumbnailIds();
					fileIds = fileIds.concat(fileId);
					realCounter++;
				}
				if (realCounter >= count) {
					i++;
					break;
				}
			}

			this.preloadOffset = i;
			Thumbnails.loadBatch(fileIds, false);
			Thumbnails.loadBatch(squareFileIds, true);
		}
	};

	window.Album = Album;
})(jQuery, Gallery);
