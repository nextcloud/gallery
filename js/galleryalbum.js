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
/* global Handlebars, Gallery, Thumbnails, GalleryImage */
(function ($, Gallery) {
	"use strict";

	/**
	 * Creates a new album object to store information about an album
	 *
	 * @param {string} path
	 * @param {Array<Album|GalleryImage>} subAlbums
	 * @param {Array<Album|GalleryImage>} images
	 * @param {string} name
	 * @param {number} fileId
	 * @param {number} mTime
	 * @param {string} etag
	 * @param {number} size
	 * @param {Boolean} sharedWithUser
	 * @param {string} owner
	 * @param {number} freeSpace
	 * @param {number} permissions
	 * @constructor
	 */
	var Album = function (path, subAlbums, images, name, fileId, mTime, etag, size, sharedWithUser,
						  owner, freeSpace, permissions) {
		this.path = path;
		this.subAlbums = subAlbums;
		this.images = images;
		this.viewedItems = 0;
		this.name = name;
		this.fileId = fileId;
		this.mTime = mTime;
		this.etag = etag;
		this.size = size;
		this.sharedWithUser = sharedWithUser;
		this.owner = owner;
		this.freeSpace = freeSpace;
		this.permissions = permissions;
		this.domDef = null;
		this.loader = null;
		this.preloadOffset = 0;
	};

	Album.prototype = {
		requestId: null,
		droppableOptions: {
			accept: '#gallery > .row > a',
			activeClass: 'album-droppable',
			hoverClass: 'album-droppable-hover',
			tolerance: 'pointer'
		},

		/**
		 * Processes UI elements dropped on the album
		 *
		 * @param event
		 * @param ui
		 */
		onDrop: function (event, ui) {
			var $item = ui.draggable;
			var $clone = ui.helper;
			var $target = $(event.target);
			var targetPath = $target.data('dir').toString();
			var filePath = $item.data('path').toString();
			var fileName = OC.basename(filePath);

			this.loader.show();

			$clone.fadeOut("normal", function () {
				Gallery.move($item, fileName, filePath, $target, targetPath);
			});
		},

		/**
		 * Returns a new album row
		 *
		 * @param {number} width
		 *
		 * @returns {Gallery.Row}
		 */
		getRow: function (width) {
			return new Gallery.Row(width);
		},

		/**
		 * Creates the DOM element for the album and return it immediately so as to not block the
		 * rendering of the rest of the interface
		 *
		 *    * Each album also contains a link to open that folder
		 *    * An album has a natural size of 200x200 and is comprised of 4 thumbnails which have a
		 *        natural size of 200x200
		 *    * Thumbnails are checked first in order to make sure that we have something to show
		 *
		 * @param {number} targetHeight Each row has a specific height
		 *
		 * @return {$} The album to be placed on the row
		 */
		getDom: function (targetHeight) {
			if (this.domDef === null) {
				var albumElement = Gallery.Templates.galleryalbum({
					targetHeight: targetHeight,
					targetWidth: targetHeight,
					dir: this.path,
					path: this.path,
					permissions: this.permissions,
					freeSpace: this.freeSpace,
					label: this.name,
					targetPath: '#' + encodeURIComponent(this.path)
				});
				this.domDef = $(albumElement);
				this.loader = this.domDef.children('.album-loader');
				this.loader.hide();
				this.domDef.click(this._openAlbum.bind(this));

				this.droppableOptions.drop = this.onDrop.bind(this);
				this.domDef.droppable(this.droppableOptions);

				// Define a if you don't want to set the style in the template
				//a.width(targetHeight);
				//a.height(targetHeight);

				this._fillSubAlbum(targetHeight);
			} else {
				this.loader.hide();
			}

			return this.domDef;
		},

		/**
		 * Fills the row with albums and images
		 *
		 * @param {Gallery.Row} row The row to append elements to
		 *
		 * @returns {$.Deferred<Gallery.Row>}
		 */
		fillNextRow: function (row) {
			var def = new $.Deferred();
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
					row.fit();
					def.resolve(row);
				});
			};
			var items = this.subAlbums.concat(this.images);
			addRowElements(this, row, items);
			return def.promise();
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
		 * Call when the album is clicked on.
		 *
		 * @param event
		 * @private
		 */
		_openAlbum: function (event) {
			event.stopPropagation();
			// show loading animation
			this.loader.show();
			if(!_.isUndefined(Gallery.Share)){
				Gallery.Share.hideDropDown();
			}
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
			var spinner = $('<div class="icon-loading">');
			imageHolder.append(spinner);

			// img is a Thumbnail.image, true means square thumbnails
			return image.getThumbnail(true).then(function (img) {
				if (image.thumbnail.valid) {
					img.alt = '';
					spinner.remove();
					imageHolder.css("background-image", "url('" + img.src + "')")
						.css('opacity', 1);
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
			var subAlbum = this.domDef.children('.album');

			if (this.images.length >= 1) {
				this._getFourImages(this.images, targetHeight, subAlbum).fail(
					function (validImages) {
						album.images = validImages;
						album._fillSubAlbum(targetHeight, subAlbum);
					});
			} else {
				var imageHolder = $('<div class="cropped">');
				subAlbum.append(imageHolder);
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
				null, null);
			var thumb = Thumbnails.getStandardIcon(-1);
			image.thumbnail = thumb;
			this.images.push(image);
			thumb.loadingDeferred.done(function (img) {
				img.height = (targetHeight - 2);
				img.width = (targetHeight) - 2;
				imageHolder.append(img);
				imageHolder.css('opacity', 1);
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
