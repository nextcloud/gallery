/* global Gallery, Album */
(function ($, Gallery) {
	"use strict";
	/**
	 * Creates a row
	 *
	 * @param {number} targetWidth
	 * @param {number} requestId
	 * @constructor
	 */
	var Row = function (targetWidth, requestId) {
		this.targetWidth = targetWidth;
		this.items = [];
		this.width = 8; // 4px margin to start with
		this.requestId = requestId;
	};

	Row.prototype = {
		/**
		 * Adds sub-albums and images to the row until it's full
		 *
		 * @param {Album|GalleryImage} element
		 *
		 * @return {jQuery.Deferred<bool>} true if more images can be added to the row
		 */
		addElement: function (element) {
			var row = this;
			var fileNotFoundStatus = 404;
			var def = new $.Deferred();

			var appendDom = function (width) {
				row.items.push(element);
				row.width += width + 4; // add 4px for the margin
				def.resolve(!row._isFull());
			};

			// No need to use getThumbnailWidth() for albums, the width is always 200
			if (element instanceof Album) {
				var width = 200;
				appendDom(width);
			} else {
				element.getThumbnailWidth().then(function (width) {
					if (element.thumbnail.status !== fileNotFoundStatus) {
						appendDom(width);
					} else {
						def.resolve(true);
					}
				}, function () {
					def.resolve(true);
				});
			}

			return def.promise();
		},

		/**
		 * Creates the row element in the DOM
		 *
		 * @returns {*}
		 */
		getDom: function () {
			var scaleRatio = (this.width > this.targetWidth) ? this.targetWidth / this.width : 1;
			var targetHeight = 200 * scaleRatio;
			targetHeight = targetHeight.toFixed(3);
			var row = $('<div/>').addClass('row');
			/**
			 * @param {*} row
			 * @param {GalleryImage[]|Album[]} items
			 * @param {number} i
			 *
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
		},

		/**
		 * Calculates if the row is full
		 *
		 * @returns {boolean}
		 * @private
		 */
		_isFull: function () {
			return this.width > this.targetWidth;
		}
	};

	Gallery.Row = Row;
})(jQuery, Gallery);
