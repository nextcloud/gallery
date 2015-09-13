/* global Gallery, Spinner */
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
		this.spinnerDiv = $('#loading-indicator').get(0);
		var spinnerOptions = {
			color: '#999',
			hwaccel: true
		};
		this.spinner = new Spinner(spinnerOptions).spin(this.spinnerDiv);
	};

	View.prototype = {
		element: null,
		breadcrumb: null,
		requestId: -1,
		spinnerDiv: 0,
		spinner: null,

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
			if ($.isEmptyObject(Gallery.imageMap)) {
				this.clear();
				if (albumPath === '') {
					Gallery.showEmpty();
					this.spinner.stop(this.spinnerDiv);
				} else {
					Gallery.showEmptyFolder();
					this.spinner.stop(this.spinnerDiv);
					Gallery.currentAlbum = albumPath;
					this.breadcrumb = new Gallery.Breadcrumb(albumPath);
					this.breadcrumb.setMaxWidth($(window).width() - Gallery.buttonsWidth);
				}
			} else {
				// Only do it when the app is initialised
				if (this.requestId === -1) {
					$('#download').click(Gallery.download);
					$('#share-button').click(Gallery.share);
					Gallery.infoBox = new Gallery.InfoBox();
					$('#album-info-button').click(Gallery.showInfo);
					$('#sort-name-button').click(Gallery.sorter);
					$('#sort-date-button').click(Gallery.sorter);
					$('#save #save-button').click(Gallery.showSaveForm);
					$('.save-form').submit(Gallery.saveForm);
				}
				this._setBackgroundColour();
				this.viewAlbum(albumPath);
			}
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
		 * @param {string} albumPath
		 */
		viewAlbum: function (albumPath) {
			albumPath = albumPath || '';
			if (!Gallery.albumMap[albumPath]) {
				return;
			}

			this.clear();
			this.spinner.spin(this.spinnerDiv);

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
		 * @param {string} sortType
		 * @param {string} sortOrder
		 */
		sortControlsSetup: function (sortType, sortOrder) {
			var sortNameButton = $('#sort-name-button');
			var sortDateButton = $('#sort-date-button');
			// namedes, dateasc etc.
			var icon = sortType + sortOrder;

			var setButton = function (button, icon, active) {
				button.removeClass('sort-inactive');
				if (!active) {
					button.addClass('sort-inactive');
				}
				button.find('img').attr('src', OC.imagePath(Gallery.appName, icon));
			};

			if (sortType === 'name') {
				setButton(sortNameButton, icon, true);
				setButton(sortDateButton, 'dateasc', false); // default icon
			} else {
				setButton(sortDateButton, icon, true);
				setButton(sortNameButton, 'nameasc', false); // default icon
			}
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
					view.spinner.stop(view.spinnerDiv);
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
							view.spinner.stop(view.spinnerDiv);
						}, function () {
							// Something went wrong, so kill the loader
							view.loadVisibleRows.loading = null;
							view.spinner.stop(view.spinnerDiv);
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
			var infoContentElement = $('.album-info-content');
			infoContentElement.slideUp();
			infoContentElement.css('max-height', $(window).height() - Gallery.browserToolbarHeight);
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
			var albumInfo = Gallery.config.albumInfo;
			if (!$.isEmptyObject(albumInfo) && albumInfo.background) {
				wrapper.css('background-color', albumInfo.background);
			} else {
				wrapper.css('background-color', '#fff');
			}
		}
	};

	Gallery.View = View;
})(jQuery, _, OC, t, Gallery);
