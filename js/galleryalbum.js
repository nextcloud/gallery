/* global Handlebars, Gallery, Thumbnails, GalleryImage */
(function ($, Gallery) {
	"use strict";

	var TEMPLATE =
		'<div class="item-container album-container" ' +
		'style="width: {{targetWidth}}px; height: {{targetHeight}}px;" ' +
		'data-width="{{targetWidth}}" data-height="{{targetHeight}}">' +
		'	<div class="album-loader loading"></div>' +
		'	<span class="album-label">{{label}}</span>' +
		'	<a class="album" href="{{targetPath}}"></a>' +
		'</div>';

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
		this.loader = null;
		this.preloadOffset = 0;
	};

	Album.prototype = {
		/**
		 * Creates the album, which will include between 1 and 4 images
		 *
		 *    * Each album is also a link to open that folder
		 *    * An album has a natural size of 200x200 and is comprised of 4 thumbnails which have a
		 *        natural size of 200x200
		 *    * Thumbnails are checked first in order to make sure that we have something to show
		 *
		 * @param {number} targetHeight Each row has a specific height
		 *
		 * @return {$} The album to be placed on the row
		 */
		getDom: function (targetHeight) {
			var album = this;
			return $.Deferred().resolve(function () {
				if (!album._template) {
					album._template = Handlebars.compile(TEMPLATE);
				}
				var template = album._template({
					targetHeight: targetHeight,
					targetWidth: targetHeight,
					label: album.name,
					targetPath: '#' + encodeURIComponent(album.path)
				});
				album.domDef = $(template);
				album.loader = album.domDef.children('.album-loader');
				album.loader.hide();
				album.domDef.click(album._showLoader.bind(album));

				album._fillSubAlbum(targetHeight);

				return album.domDef;
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
		 * Shows a loading animation
		 *
		 * @param event
		 * @private
		 */
		_showLoader: function (event) {
			event.stopPropagation();
			this.loader.show();
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
			var backgroundHeight, backgroundWidth;

			backgroundHeight = (targetHeight / 2);
			backgroundWidth = calcWidth - 2.01;

			// Adjust the size because of the margins around pictures
			backgroundHeight -= 2;

			imageHolder.css("height", backgroundHeight)
				.css("width", backgroundWidth);

			// img is a Thumbnail.image, true means square thumbnails
			return image.getThumbnail(true).then(function (img) {
				if (image.thumbnail.valid) {
					img.alt = '';
					imageHolder.css("background-image", "url('" + img.src + "')");
				}
			});
		},

		/**
		 * Builds the album representation by placing 1 to 4 images on a grid
		 *
		 * @param {Array<GalleryImage>} images
		 * @param {number} targetHeight Each row has a specific height
		 * @param {object} a
		 *
		 * @returns {$.Deferred<Array>}
		 * @private
		 */
		_getFourImages: function (images, targetHeight, a) {
			var calcWidth = targetHeight;
			var targetWidth;
			var imagesCount = images.length;
			var def = new $.Deferred();
			var validImages = [];
			var fail = false;
			var thumbsArray = [];

			for (var i = 0; i < imagesCount; i++) {
				targetWidth = calcWidth;
				// One picture filling the album
				if (imagesCount === 1) {
					targetHeight = 2 * targetHeight;
				}
				// 2 bottom pictures out of 3, or 4 pictures have the size of a quarter of the album
				if ((imagesCount === 3 && i !== 0) || imagesCount === 4) {
					targetWidth = calcWidth / 2;
				}

				// Append the div first in order to not lose the order of images
				var imageHolder = $('<div class="cropped">');
				a.append(imageHolder);
				thumbsArray.push(
					this._getOneImage(images[i], targetHeight, targetWidth, imageHolder));
			}

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
					a.children().remove();
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
		 * @private
		 */
		_fillSubAlbum: function (targetHeight) {
			var album = this;
			var a = this.domDef.children('a');

			if (this.images.length >= 1) {
				this._getFourImages(this.images, targetHeight, a).fail(function (validImages) {
					album.images = validImages;
					album._fillSubAlbum(targetHeight, a);
				});
			} else {
				var imageHolder = $('<div class="cropped">');
				a.append(imageHolder);
				this._showFolder(targetHeight, imageHolder);
			}
		},

		/**
		 * Shows a folder icon in the album since we couldn't get any proper thumbnail
		 *
		 * @param {number} targetHeight
		 * @param imageHolder
		 * @private
		 */
		_showFolder: function (targetHeight, imageHolder) {
			var image = new GalleryImage('Generic folder', 'Generic folder', -1, 'image/svg+xml',
				Gallery.token);
			var thumb = Thumbnails.getStandardIcon(-1);
			image.thumbnail = thumb;
			this.images.push(image);
			thumb.loadingDeferred.done(function (img) {
				imageHolder.append(img);
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
