/* global $, OC, Thumbnails */
/**
 * Creates a new album object to store information about an album
 *
 * @param {string} path
 * @param {[]} subAlbums
 * @param {[]} images
 * @param {string} name
 * @constructor
 */
function Album (path, subAlbums, images, name) {
	this.path = path;
	this.subAlbums = subAlbums;
	this.images = images;
	this.viewedItems = 0;
	this.name = name;
	this.domDef = null;
	this.preloadOffset = 0;
}

/**
 * Creates a row
 *
 * @param targetWidth
 * @param requestId
 * @constructor
 */
function Row (targetWidth, requestId) {
	this.targetWidth = targetWidth;
	this.items = [];
	this.width = 8; // 4px margin to start with
	this.requestId = requestId;
}

/**
 * Creates a new image object to store information about a media file
 *
 * @param src
 * @param path
 * @param fileId
 * @param mimeType
 * @param mTime modification time
 * @constructor
 */
function GalleryImage (src, path, fileId, mimeType, mTime) {
	this.src = src;
	this.path = path;
	this.fileId = fileId;
	this.mimeType = mimeType;
	this.mTime = mTime;
	this.thumbnail = null;
	this.domDef = null;
	this.domHeigth = null;
}

Album.prototype = {
	_getThumbnail: function () {
		if (this.images.length) {
			return this.images[0].getThumbnail(1);
		}
		return this.subAlbums[0]._getThumbnail();
	},
	/**
	 * Retrieves a thumbnail and adds it to the album representation
	 *
	 * @param {GalleryImage} image
	 * @param {number} targetHeight Each row has a specific height
	 * @param {number} calcWidth Album width
	 * @param {object} a
	 * @param {bool} doubleWidth false for a 1x1 album thumbnail and true for a 2x1 album thumbnail
	 *
	 * @returns {a}
	 * @private
	 */
	_getOneImage: function (image, targetHeight, calcWidth, a, doubleWidth) {
		var parts = image.src.split('/');
		parts.shift();
		var path = parts.join('/');
		//var albumpath = this.path;
		var gm = new GalleryImage(image.src, path);
		gm.getThumbnail(1).then(function (img) {
			var backgroundHeight, backgroundWidth;
			img.alt = '';
			backgroundHeight = (targetHeight / 2);
			backgroundWidth = calcWidth - 2;

			// Adjust the size because of the margins around pictures
			if (doubleWidth) {
				backgroundHeight -= 3;
				backgroundWidth -= 2;
			} else {
				backgroundHeight -= 2;
			}
			var croppedDiv = $('<div class="cropped">');
			croppedDiv.css("background-image", "url('" + img.src + "')");
			croppedDiv.css("height", backgroundHeight);
			croppedDiv.css("width", backgroundWidth);
			a.append(croppedDiv);
		});
	},

	/**
	 * Builds the album representation by placing 1 to 4 images on a grid
	 *
	 * @param {array<GalleryImage>} images
	 * @param {number} targetHeight Each row has a specific height
	 * @param {object} a
	 *
	 * @returns {a}
	 * @private
	 */
	_getFourImages: function (images, targetHeight, a) {
		var calcWidth = Math.floor(targetHeight / 2);
		var targetWidth;
		var imagesCount = images.length;
		var doubleWidth = false;

		for (var i = 0; i < imagesCount; i++) {
			targetWidth = calcWidth;
			if (imagesCount === 2 || (imagesCount === 3 && i === 0)) {
				targetWidth = calcWidth * 2;
				doubleWidth = true;
			}
			this._getOneImage(images[i], targetHeight, targetWidth, a, doubleWidth);
		}
	},

	/**
	 * preload the first $count thumbnails
	 *
	 * @param count
	 * @private
	 */
	_preload: function (count) {
		var items = this.subAlbums.concat(this.images);
		var realCounter = 0;
		var maxThumbs = 0;
		var paths = [];
		var squarePaths = [];
		for (var i = this.preloadOffset; i < this.preloadOffset + count && i < items.length; i++) {
			if (items[i].subAlbums) {
				maxThumbs = 4;
				var imagesLength = items[i].images.length;
				if (imagesLength > 0 && imagesLength < 4) {
					maxThumbs = imagesLength;
				}
				squarePaths = squarePaths.concat(items[i].getThumbnailPaths(maxThumbs));
				realCounter = realCounter + maxThumbs;
			} else {
				paths = paths.concat(items[i].getThumbnailPaths());
				realCounter++;
			}
			if (realCounter >= count) {
				i++;
				break;
			}
		}

		this.preloadOffset = i;
		Thumbnails.loadBatch(paths, false);
		Thumbnails.loadBatch(squarePaths, true);
	},

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

		return this._getThumbnail().then(function (img) {
			var a = $('<a/>').addClass('album').attr('href', '#' + encodeURIComponent(album.path));

			a.append($('<span/>').addClass('album-label').text(album.name));
			var ratio = Math.round(img.ratio * 100) / 100;
			var calcWidth = (targetHeight * ratio) / 2;

			a.width(calcWidth * 2);
			a.height(targetHeight);

			if (album.images.length > 1) {
				album._getFourImages(album.images, targetHeight, a);
			} else if (album.images.length === 0 && album.subAlbums[0].images.length > 1) {
				album._getFourImages(album.subAlbums[0].images, targetHeight, a);
			} else {
				a.append(img);
				img.height = (targetHeight - 2);
				img.width = (targetHeight * ratio) - 2;
			}

			return a;
		});
	},

	getThumbnailWidth: function () {
		return this._getThumbnail().then(function (img) {
			return img.originalWidth;
		});
	},

	/**
	 *
	 * @param {number} width
	 * @returns {$.Deferred<Row>}
	 */
	getNextRow: function (width) {
		var numberOfThumbnailsToPreload = 6;

		/**
		 * Add images to the row until it's full
		 *
		 * @todo The number of images to preload should be a user setting
		 *
		 * @param {Album} album
		 * @param {Row} row
		 * @param {array<Album|GalleryImage>} images
		 *
		 * @returns {$.Deferred<Row>}
		 */
		var addRowElements = function (album, row, images) {
			if ((album.viewedItems + 5) > album.preloadOffset) {
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
		var row = new Row(width, this.requestId);
		return addRowElements(this, row, items);
	},

	/**
	 * Returns paths of thumbnails belonging to the album
	 *
	 * @param {number} count
	 *
	 * @return string[]
	 */
	getThumbnailPaths: function (count) {
		var paths = [];
		var items = this.images.concat(this.subAlbums);
		for (var i = 0; i < items.length && i < count; i++) {
			paths = paths.concat(items[i].getThumbnailPaths(count));
		}

		return paths;
	}
};

Row.prototype = {
	/**
	 * Calculates if the row is full
	 *
	 * @returns {boolean}
	 * @private
	 */
	_isFull: function () {
		return this.width > this.targetWidth;
	},

	/**
	 * Adds sub-albums and images to the row until it's full
	 *
	 * @param {GalleryImage} image
	 *
	 * @return {$.Deferred<bool>} true if more images can be added to the row
	 */
	addElement: function (image) {
		var row = this;
		var def = new $.Deferred();
		image.getThumbnailWidth().then(function (width) {
			row.items.push(image);
			row.width += width + 4; // add 4px for the margin
			def.resolve(!row._isFull());
		}, function () {
			def.resolve(true);
		});
		return def;
	},

	getDom: function () {
		var scaleRation = (this.width > this.targetWidth) ? this.targetWidth / this.width : 1;
		var targetHeight = 200 * scaleRation;
		var row = $('<div/>').addClass('row loading');
		/**
		 * @param row
		 * @param {GalleryImage[]} items
		 * @param i
		 * @returns {*}
		 */
		var addImageToDom = function (row, items, i) {
			return items[i].getDom(targetHeight).then(function (itemDom) {
				i++;
				row.append(itemDom);
				if (i < items.length) {
					return addImageToDom(row, items, i);
				}
				return row;
			});
		};
		return addImageToDom(row, this.items, 0);
	}
};

GalleryImage.prototype = {
	/**
	 * Returns the Thumbnail path
	 *
	 * @returns {[string]}
	 */
	getThumbnailPaths: function () {
		return [this.path];
	},

	/**
	 * Returns a reference to a loading Thumbnail.image
	 *
	 * @param {bool} square
	 *
	 * @returns {$.Deferred<Thumbnail.image>}
	 */
	getThumbnail: function (square) {
		if (this.thumbnail === null) {
			this.thumbnail = Thumbnails.get(this.src, square);
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
		return this.getThumbnail().then(function (img) {
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
	 * @return {a}
	 */
	getDom: function (targetHeight) {
		var image = this;
		if (this.domDef === null || this.domHeigth !== targetHeight) {
			this.domHeigth = targetHeight;
			// img is a Thumbnail.image
			this.domDef = this.getThumbnail().then(function (img) {
				img.height = targetHeight;
				img.width = targetHeight * img.ratio;
				img.setAttribute('width', 'auto');
				img.alt = encodeURI(image.path);
				var url = '#' + encodeURIComponent(image.path);
				var a = $('<a/>').addClass('image').attr('href', url).attr('data-path', image.path);

				var imageLabel = $('<span/>').addClass('image-label');
				var imageTitle = $('<span/>').addClass('title').html('<strong>>&nbsp;</strong>' +
				OC.basename(image.path));
				imageLabel.append(imageTitle);
				a.hover(function () {
					imageLabel.slideToggle(250);
				}, function () {
					imageLabel.slideToggle(250);
				});
				a.append(imageLabel);
				a.append(img);
				return a;
			});
		}
		return this.domDef;
	}
};
