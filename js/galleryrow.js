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
/* global Gallery, Album */
(function ($, Gallery) {
	"use strict";
	/**
	 * Creates a row
	 *
	 * @param {number} targetWidth
	 * @constructor
	 */
	var Row = function (targetWidth) {
		this.targetWidth = targetWidth;
		this.items = [];
		this.width = 4; // 4px margin to start with
		this.domDef = $('<div/>').addClass('row');
	};

	Row.prototype = {
		targetHeight: 200, // standard row height
		draggableOptions: {
			revert: 'invalid',
			revertDuration: 300,
			opacity: 0.7,
			distance: 20,
			zIndex: 1000,
			cursor: 'move',
			helper: function (e) {
				// Capture the original element
				var original = $(e.target).hasClass("ui-draggable") ? $(e.target) : $(
					e.target).closest(".ui-draggable");

				// Create a clone 50% smaller and link it to the #content element
				var clone = original.clone()
					.css({'transform': 'scale(0.5)'})
					.appendTo('#content');

				// Remove the labels
				clone.children('.image-label,.album-label').remove();

				// Centre the mouse pointer
				$(this).draggable("option", "cursorAt", {
					left: Math.floor($(this).width() / 2),
					top: Math.floor($(this).height() / 2)
				});

				return clone;
			},
			start: function (e) {
				// Disable all mouse interactions when dragging
				$('#gallery').css({'pointer-events': 'none'});
				$(e.target).css({opacity: 0.7});
			},
			stop: function (e) { // need to put it back on stop
				$('#gallery').css({'pointer-events': 'all'});
				$(e.target).css({opacity: 1});
			}
		},

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
			itemDom.draggable(this.draggableOptions);

			// The width of an album is always the same as its height
			if (element instanceof Album) {
				validateRowWidth(row.targetHeight);
			} else {
				// We can't calculate the total width if we don't have the width of the thumbnail
				element.getThumbnailWidth(row.targetHeight).then(function (width) {
					if (element.thumbnail.status !== fileNotFoundStatus) {
						element.resize(row.targetHeight, width);
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
			this.domDef.find('a').each(function () {
				// Necessary since DOM elements are not resized when CSS transform is used
				$(this).css('width', $(this).data('width') * scaleRatio)
					.css('height', $(this).data('height') * scaleRatio);
				// This scales the containers inside the anchors
				$(this).children('.container').css('transform-origin', 'left top')
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
