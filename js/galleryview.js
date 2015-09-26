/* global Gallery */
(function ($, _, OC, t, Gallery) {
	"use strict";
	/**
	 * Builds and updates the Gallery view
	 *
	 * @constructor
	 */
	var View = function () {
		this.element = $('#gallery');
		this.loadVisibleRows.loading = false;
	};

	View.prototype = {
		element: null,
		breadcrumb: null,
		requestId: -1,

		/**
		 * Removes all thumbnails from the view
		 */
		clear: function () {
			// We want to keep all the events
			this.element.children().detach();
			Gallery.showLoading();
		},

		/**
		 * Populates the view if there are images or albums to show
		 *
		 * @param {string} albumPath
		 */
		init: function (albumPath) {
			// Only do it when the app is initialised
			if (this.requestId === -1) {
				this._initButtons();
			}
			if ($.isEmptyObject(Gallery.imageMap)) {
				this.clear();
				if (albumPath === '') {
					Gallery.showEmpty();
				} else {
					Gallery.showEmptyFolder();
					this.hideButtons();
					Gallery.currentAlbum = albumPath;
					this.breadcrumb = new Gallery.Breadcrumb(albumPath);
					this.breadcrumb.setMaxWidth($(window).width() - Gallery.buttonsWidth);
					Gallery.config.albumDesign = null;
				}
			} else {
				this.viewAlbum(albumPath);
			}

			this._setBackgroundColour();
		},

		/**
		 * Starts the slideshow
		 *
		 * @param {string} path
		 * @param {string} albumPath
		 */
		startSlideshow: function (path, albumPath) {
			var album = Gallery.albumMap[albumPath];
			var images = album.images;
			var startImage = Gallery.imageMap[path];
			Gallery.slideShow(images, startImage, false);
		},

		/**
		 * Sets up the controls and starts loading the gallery rows
		 *
		 * @param {string|null} albumPath
		 */
		viewAlbum: function (albumPath) {
			albumPath = albumPath || '';
			if (!Gallery.albumMap[albumPath]) {
				return;
			}

			this.clear();
			$('#loading-indicator').show();

			if (albumPath !== Gallery.currentAlbum) {
				this.loadVisibleRows.loading = false;
				Gallery.currentAlbum = albumPath;
				this._setupButtons(albumPath);
			}

			Gallery.albumMap[albumPath].viewedItems = 0;
			Gallery.albumMap[albumPath].preloadOffset = 0;

			// Each request has a unique ID, so that we can track which request a row belongs to
			this.requestId = Math.random();
			Gallery.albumMap[Gallery.currentAlbum].requestId = this.requestId;

			// Loading rows without blocking the execution of the rest of the script
			setTimeout(function () {
				this.loadVisibleRows.activeIndex = 0;
				this.loadVisibleRows(Gallery.albumMap[Gallery.currentAlbum], Gallery.currentAlbum);
			}.bind(this), 0);
		},

		/**
		 * Manages the sorting interface
		 *
		 * @param {string} sortType name or date
		 * @param {string} sortOrder asc or des
		 */
		sortControlsSetup: function (sortType, sortOrder) {
			var reverseSortType = 'date';
			if (sortType === 'date') {
				reverseSortType = 'name';
			}
			this._setSortButton(sortType, sortOrder, true);
			this._setSortButton(reverseSortType, 'asc', false); // default icon
		},

		/**
		 * Loads and displays gallery rows on screen
		 *
		 * @param {Album} album
		 * @param {string} path
		 *
		 * @returns {boolean|null|*}
		 */
		loadVisibleRows: function (album, path) {
			var view = this;
			// If the row is still loading (state() = 'pending'), let it load
			if (this.loadVisibleRows.loading &&
				this.loadVisibleRows.loading.state() !== 'resolved') {
				return this.loadVisibleRows.loading;
			}

			/**
			 * At this stage, there is no loading taking place (loading = false|null), so we can
			 * look for new rows
			 */

			var scroll = $('#content-wrapper').scrollTop() + $(window).scrollTop();
			// 2 windows worth of rows is the limit from which we need to start loading new rows.
			// As we scroll down, it grows
			var targetHeight = ($(window).height() * 2) + scroll;
			var showRows = function (album) {

				// If we've reached the end of the album, we kill the loader
				if (!(album.viewedItems < album.subAlbums.length + album.images.length)) {
					view.loadVisibleRows.loading = null;
					$('#loading-indicator').hide();
					return;
				}

				// Everything is still in sync, since no deferred calls have been placed yet

				return album.getNextRow($(window).width()).then(function (row) {

					/**
					 * At this stage, the row has a width and contains references to images based
					 * on
					 * information available when making the request, but this information may have
					 * changed while we were receiving thumbnails for the row
					 */

					if (view.requestId === row.requestId) {
						return row.getDom().then(function (dom) {

							if (Gallery.currentAlbum !== path) {
								view.loadVisibleRows.loading = null;
								return; //throw away the row if the user has navigated away in the
										// meantime
							}
							if (view.element.length === 1) {
								Gallery.showNormal();
							}

							view.element.append(dom);

							if (album.viewedItems < album.subAlbums.length + album.images.length &&
								view.element.height() < targetHeight) {
								return showRows(album);
							}

							// No more rows to load at the moment
							view.loadVisibleRows.loading = null;
							$('#loading-indicator').hide();
						}, function () {
							// Something went wrong, so kill the loader
							view.loadVisibleRows.loading = null;
							$('#loading-indicator').hide();
						});
					}
					// This is the safest way to do things
					view.viewAlbum(Gallery.currentAlbum);

				});
			};
			if (this.element.height() < targetHeight) {
				this.loadVisibleRows.loading = true;
				this.loadVisibleRows.loading = showRows(album);
				return this.loadVisibleRows.loading;
			}
		},

		hideButtons: function () {
			$('#loading-indicator').hide();
			$('#album-info-button').hide();
			$('#share-button').hide();
			$('#sort-name-button').hide();
			$('#sort-date-button').hide();
		},

		/**
		 * Adds all the click handlers to buttons the first time they appear in the interface
		 *
		 * @private
		 */
		_initButtons: function () {
			$('#filelist-button').click(Gallery.switchToFilesView);
			$('#download').click(Gallery.download);
			$('#share-button').click(Gallery.share);
			Gallery.infoBox = new Gallery.InfoBox();
			$('#album-info-button').click(Gallery.showInfo);
			$('#sort-name-button').click(Gallery.sorter);
			$('#sort-date-button').click(Gallery.sorter);
			$('#save #save-button').click(Gallery.showSaveForm);
			$('.save-form').submit(Gallery.saveForm);

			this.requestId = Math.random();
		},

		/**
		 * Sets up all the buttons of the interface
		 *
		 * @param {string} albumPath
		 * @private
		 */
		_setupButtons: function (albumPath) {
			this._shareButtonSetup(albumPath);
			this._infoButtonSetup();

			this.breadcrumb = new Gallery.Breadcrumb(albumPath);
			this.breadcrumb.setMaxWidth($(window).width() - Gallery.buttonsWidth);

			$('#sort-name-button').show();
			$('#sort-date-button').show();
			var currentSort = Gallery.config.albumSorting;
			this.sortControlsSetup(currentSort.type, currentSort.order);
			Gallery.albumMap[Gallery.currentAlbum].images.sort(
				Gallery.utility.sortBy(currentSort.type,
					currentSort.order));
			Gallery.albumMap[Gallery.currentAlbum].subAlbums.sort(Gallery.utility.sortBy('name',
				currentSort.albumOrder));
		},

		/**
		 * Shows or hides the share button depending on if we're in a public gallery or not
		 *
		 * @param {string} albumPath
		 * @private
		 */
		_shareButtonSetup: function (albumPath) {
			var shareButton = $('#share-button');
			if (albumPath === '' || Gallery.token) {
				shareButton.hide();
			} else {
				shareButton.show();
			}
		},

		/**
		 * Shows or hides the info button based on the information we've received from the server
		 *
		 * @private
		 */
		_infoButtonSetup: function () {
			var infoButton = $('#album-info-button');
			infoButton.find('span').hide();
			var infoContentContainer = $('.album-info-container');
			infoContentContainer.slideUp();
			infoContentContainer.css('max-height',
				$(window).height() - Gallery.browserToolbarHeight);
			var albumInfo = Gallery.config.albumInfo;
			if (Gallery.config.albumError) {
				infoButton.hide();
				var text = '<strong>' + t('gallery', 'Configuration error') + '</strong></br>' +
					Gallery.config.albumError.message + '</br></br>';
				Gallery.utility.showHtmlNotification(text, 7);
			} else if ($.isEmptyObject(albumInfo)) {
				infoButton.hide();
			} else {
				infoButton.show();
				if (albumInfo.inherit !== 'yes' || albumInfo.level === 0) {
					infoButton.find('span').delay(1000).slideDown();
				}
			}
		},

		/**
		 * Sets the background colour of the photowall
		 *
		 * @private
		 */
		_setBackgroundColour: function () {
			var wrapper = $('#content-wrapper');
			var albumDesign = Gallery.config.albumDesign;
			if (!$.isEmptyObject(albumDesign) && albumDesign.background) {
				wrapper.css('background-color', albumDesign.background);
			} else {
				wrapper.css('background-color', '#fff');
			}
		},

		/**
		 * Picks the image which matches the sort order
		 *
		 * @param {string} sortType name or date
		 * @param {string} sortOrder asc or des
		 * @param {bool} active determines if we're setting up the active sort button
		 * @private
		 */
		_setSortButton: function (sortType, sortOrder, active) {
			var button = $('#sort-' + sortType + '-button');
			// Removing all the classes which control the image in the button
			button.removeClass('active-button');
			button.find('img').removeClass('front');
			button.find('img').removeClass('back');

			// We need to determine the reverse order in order to send that image to the back
			var reverseSortOrder = 'des';
			if (sortOrder === 'des') {
				reverseSortOrder = 'asc';
			}

			// We assign the proper order to the button images
			button.find('img.' + sortOrder).addClass('front');
			button.find('img.' + reverseSortOrder).addClass('back');

			// The active button needs a hover action for the flip effect
			if (active) {
				button.addClass('active-button');
				if (button.is(":hover")) {
					button.removeClass('hover');
				}
				// We can't use a toggle here
				button.hover(function () {
						$(this).addClass('hover');
					},
					function () {
						$(this).removeClass('hover');
					});
			}
		}
	};

	Gallery.View = View;
})(jQuery, _, OC, t, Gallery);
