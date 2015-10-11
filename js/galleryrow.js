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
		this.width = 4; // 4px margin to start with
		this.requestId = requestId;
		this.domDef = $('<div/>').addClass('row');
	};

	Row.prototype = {
		targetHeight: 200, // standard row height
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
			var itemDom;

			var validateRowWidth = function (width) {
				row.items.push(element);
				row.width += width + 4; // add 4px for the margin
				def.resolve(!row._isFull());
			};

			itemDom = element.getDom(row.targetHeight);
			row.domDef.append(itemDom);

			// No need to use getThumbnailWidth() for albums, the width is always 200
			if (element instanceof Album) {
				var width = row.targetHeight;
				validateRowWidth(width);
			} else {
				// We can't calculate the total width if we don't have the width of the thumbnail
				element.getThumbnailWidth().then(function (width) {
					if (element.thumbnail.status !== fileNotFoundStatus) {
						element.resize(row.targetHeight);
						validateRowWidth(width);
					} else {
						itemDom.remove();
						def.resolve(true);
					}
				}, function () {
					itemDom.remove();
					def.resolve(true);
				});
			}

			return def.promise();
		},

		/**
		 * Returns the DOM element of the row
		 *
		 * @returns {*}
		 */
		getDom: function () {
			return this.domDef;
		},

		/**
		 * Resizes the row once it's full
		 */
		fit: function () {
			var scaleRatio = (this.width > this.targetWidth) ? this.targetWidth / this.width : 1;

			// This animates the elements when the window is resized
			var targetHeight = 4 + (this.targetHeight * scaleRatio);
			targetHeight = targetHeight.toFixed(3);
			this.domDef.height(targetHeight);
			this.domDef.width(this.width * scaleRatio);

			// Resizes and scales all photowall elements to make them fit within the window's width
			this.domDef.find('.item-container').each(function () {
				// Necessary since DOM elements are not resized when CSS transform is used
				$(this).css('width', $(this).data('width') * scaleRatio)
					.css('height', $(this).data('height') * scaleRatio);
				// This scales the anchors inside the item-container divs
				$(this).children('a').css('transform-origin', 'left top')
					.css('-webkit-transform-origin', 'left top')
					.css('-ms-transform-origin', 'left top')
					.css('transform', 'scale(' + scaleRatio + ')')
					.css('-webkit-transform', 'scale(' + scaleRatio + ')')
					.css('-ms-transform', 'scale(' + scaleRatio + ')');
			});

			// Restore the rows to their normal opacity. This happens immediately with rows
			// containing albums only
			this.domDef.css('opacity', 1);
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
