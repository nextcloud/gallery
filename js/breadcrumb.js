/* global Gallery */
(function ($, OC, t, Gallery) {
	"use strict";
	/**
	 * Breadcrumbs that represent the path to the current album
	 *
	 * @todo We could speed things up when the window is resized by caching the size of crumbs and
	 *     resizing the breadcrumb based on those values instead of rebuilding it from scratch
	 *
	 * @param {string} albumPath
	 * @constructor
	 */
	var Breadcrumb = function (albumPath) {
		this.breadcrumbsElement = $('#breadcrumbs');
		this.albumPath = albumPath;
	};

	Breadcrumb.prototype = {
		breadcrumbsElement: null,
		albumPath: null,
		availableWidth: 0,

		/**
		 * Defines the maximum available width in which we can build the breadcrumb and builds it
		 *
		 * @param {int} availableWidth
		 */
		setMaxWidth: function (availableWidth) {
			if (this.availableWidth !== availableWidth) {
				this.availableWidth = availableWidth;
				this._build();
			}
		},

		/**
		 * Builds the breadcrumb
		 *
		 * Shortens it when the path is too long
		 * @private
		 */
		_build: function () {
			var i, crumbs, path, currentAlbum, crumbElement;
			var breadcrumbs = [];
			this._clear();
			var albumName = $('#content').data('albumname');
			if (!albumName) {
				albumName = t('gallery', 'Pictures');
			}
			path = '';
			crumbs = this.albumPath.split('/');
			currentAlbum = crumbs.pop();

			if (currentAlbum) {
				// We first push the current folder
				crumbElement = this._push(currentAlbum, false);
				crumbElement.addClass('last');

				// This builds the breadcrumbs
				var crumbsLength = crumbs.length;
				if (crumbsLength > 0) {
					for (i = 0; i < crumbsLength; i++) {
						if (crumbs[i]) {
							if (path) {
								path += '/' + crumbs[i];
							} else {
								path += crumbs[i];
							}
							breadcrumbs.push({
								name: crumbs[i],
								path: path
							});
						}
					}
					this._addCrumbs(breadcrumbs);
				}
			}
			// This adds the home button
			this._addHome(albumName, currentAlbum);
		},

		/**
		 * Clears the breadcrumbs and its tooltip
		 */
		_clear: function () {
			this.breadcrumbsElement.children().remove();
		},

		/**
		 * Adds an element to the breadcrumb
		 *
		 * @param {string} name
		 * @param {string|bool} link
		 * @param img
		 * @private
		 */
		_push: function (name, link, img) {
			var crumb = $('<div/>');
			crumb.addClass('crumb');
			if (link !== false) {
				link = '#' + encodeURIComponent(link);
				var crumbLink = $('<a/>');
				crumbLink.attr('href', link);
				if (img) {
					crumbLink.append(img);
				} else {
					crumbLink.text(name);
				}
				crumb.append(crumbLink);
			} else {
				crumb.html($('<span/>').text(name));
			}
			this.breadcrumbsElement.prepend(crumb);

			return crumb;
		},

		/**
		 * Adds the Home button
		 *
		 * @param {string} albumName
		 * @param {string} currentAlbum
		 * @private
		 */
		_addHome: function (albumName, currentAlbum) {
			var crumbImg = $('<img/>');
			crumbImg.attr('src', OC.imagePath('core', 'places/home'));
			crumbImg.attr('title', albumName);
			var crumbElement = this._push('', '', crumbImg);
			if (!currentAlbum) {
				crumbElement.addClass('last');
			}
		},

		/**
		 * Adds all the elements located in between the home button and the last folder
		 *
		 * Shrinks the breadcrumb if there is not enough room
		 *
		 * @param {Array} crumbs
		 * @private
		 */
		_addCrumbs: function (crumbs) {
			var i, crumbElement;
			var shorten = false;
			var ellipsisPath = '';

			// We go through the array in reverse order
			for (i = crumbs.length; i >= 0; i--) {
				if (crumbs[i]) {
					crumbElement =
						this._push(crumbs[i].name, crumbs[i].path);
					if (shorten) {
						crumbElement.hide();
					}
					// If we've reached the maximum width, we start hiding crumbs
					if (this.breadcrumbsElement.width() > this.availableWidth) {
						shorten = true;
						crumbElement.hide();
						ellipsisPath = crumbs[i].path;
					}
				}
			}
			// If we had to hide crumbs, we'll add a pay to go to the parent folder
			if (shorten) {
				crumbElement = this._push('...', ellipsisPath);
				crumbElement.attr('title', ellipsisPath).tooltip({
					fade: true,
					placement: 'bottom',
					delay: {
						hide: 5
					}
				});
			}
		}
	};

	Gallery.Breadcrumb = Breadcrumb;
})(jQuery, OC, t, Gallery);
