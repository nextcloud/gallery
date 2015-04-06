/* global OC, $, _, t, Gallery, Album, GalleryImage, SlideShow */
Gallery.view = {};
Gallery.view.element = null;
Gallery.view.requestId = -1;

/**
 * Removes all thumbnails from the view
 */
Gallery.view.clear = function () {
	// We want to keep all the events
	Gallery.view.element.children().detach();
	Gallery.showLoading();
};

/**
 * Populates the view if there are images or albums to show
 *
 * @param {string} albumPath
 */
Gallery.view.init = function (albumPath) {
	if (Gallery.images.length === 0) {
		Gallery.showEmpty();
	} else {
		// Only do it when the app is initialised
		if (Gallery.view.requestId === -1) {
			$('#download').click(Gallery.download);
			$('#share-button').click(Gallery.share);
			$('#album-info-button').click(Gallery.showInfo);
			$('#sort-name-button').click(Gallery.sorter);
			$('#sort-date-button').click(Gallery.sorter);
		}
		OC.Breadcrumb.container = $('#breadcrumbs');
		Gallery.view.viewAlbum(albumPath);
	}
};

/**
 * Starts the slideshow
 *
 * @param {string} path
 * @param {string} albumPath
 */
Gallery.view.startSlideshow = function (path, albumPath) {
	var album = Gallery.albumMap[albumPath];
	var images = album.images;
	var startImage = Gallery.imageMap[path];
	Gallery.slideShow(images, startImage);
};

/**
 * Sets up the controls and starts loading the gallery rows
 *
 * @param {string} albumPath
 */
Gallery.view.viewAlbum = function (albumPath) {
	albumPath = albumPath || '';
	if (!Gallery.albumMap[albumPath]) {
		return;
	}

	Gallery.view.clear();
	if (albumPath !== Gallery.currentAlbum) {
		Gallery.view.loadVisibleRows.loading = false;
		Gallery.currentAlbum = albumPath;
		Gallery.view.shareButtonSetup(albumPath);
		Gallery.view.infoButtonSetup();
		Gallery.view.buildBreadCrumb(albumPath);
	}

	Gallery.albumMap[albumPath].viewedItems = 0;
	Gallery.albumMap[albumPath].preloadOffset = 0;

	// Each request has a unique ID, so that we can track which request a row belongs to
	Gallery.view.requestId = Math.random();
	Gallery.albumMap[Gallery.currentAlbum].requestId = Gallery.view.requestId;

	// Loading rows without blocking the execution of the rest of the script
	setTimeout(function () {
		Gallery.view.loadVisibleRows.activeIndex = 0;
		Gallery.view.loadVisibleRows(Gallery.albumMap[Gallery.currentAlbum], Gallery.currentAlbum);
	}, 0);
};

/**
 * Shows or hides the share button depending on if we're in a public gallery or not
 *
 * @param {string} albumPath
 */
Gallery.view.shareButtonSetup = function (albumPath) {
	var shareButton = $('button.share');
	if (albumPath === '' || Gallery.token) {
		shareButton.hide();
	} else {
		shareButton.show();
	}
};

/**
 * Shows or hides the info button based on the information we've received from the server
 */
Gallery.view.infoButtonSetup = function () {
	var infoButton = $('#album-info-button');
	var infoContentElement = $('.album-info-content');
	infoContentElement.slideUp();
	infoContentElement.css('max-height', $(window).height() - 150);
	var albumInfo = Gallery.albumConfig.getAlbumInfo();
	if ($.isEmptyObject(albumInfo.description) &&
		$.isEmptyObject(albumInfo.descriptionLink) &&
		$.isEmptyObject(albumInfo.copyright) &&
		$.isEmptyObject(albumInfo.copyrightLink)) {
		infoButton.hide();
	} else {
		infoButton.show();
	}
};

/**
 * Manages the sorting interface
 *
 * @param {string} sortType
 * @param {string} sortOrder
 */
Gallery.view.sortControlsSetup = function (sortType, sortOrder) {
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
};

/**
 * Loads and displays gallery rows on screen
 *
 * @param {Album} album
 * @param {string} path
 *
 * @returns {boolean|null|*}
 */
Gallery.view.loadVisibleRows = function (album, path) {
	// If the row is still loading (state() = 'pending'), let it load
	if (Gallery.view.loadVisibleRows.loading &&
		Gallery.view.loadVisibleRows.loading.state() !== 'resolved') {
		return Gallery.view.loadVisibleRows.loading;
	}

	/**
	 * At this stage, there is no loading taking place (loading = false|null), so we can look for
	 * new rows
	 */

	var scroll = $('#content-wrapper').scrollTop() + $(window).scrollTop();
	// 2 windows worth of rows is the limit from which we need to start loading new rows. As we
	// scroll down, it grows
	var targetHeight = ($(window).height() * 2) + scroll;
	var showRows = function (album) {

		// If we've reached the end of the album, we kill the loader
		if (!(album.viewedItems < album.subAlbums.length + album.images.length)) {
			Gallery.view.loadVisibleRows.loading = null;
			return;
		}

		// Everything is still in sync, since no deferred calls have been placed yet

		return album.getNextRow($(window).width()).then(function (row) {

			/**
			 * At this stage, the row has a width and contains references to images based on
			 * information available when making the request, but this information may have changed
			 * while we were receiving thumbnails for the row
			 */

			if (Gallery.view.requestId === row.requestId) {
				return row.getDom().then(function (dom) {

					// defer removal of loading class to trigger CSS3 animation
					_.defer(function () {
						dom.removeClass('loading');
					});
					if (Gallery.currentAlbum !== path) {
						Gallery.view.loadVisibleRows.loading = null;
						return; //throw away the row if the user has navigated away in the
								// meantime
					}
					if (Gallery.view.element.length === 1) {
						Gallery.showNormal();
					}

					Gallery.view.element.append(dom);

					if (album.viewedItems < album.subAlbums.length + album.images.length &&
						Gallery.view.element.height() < targetHeight) {
						return showRows(album);
					}

					// No more rows to load at the moment
					Gallery.view.loadVisibleRows.loading = null;
				}, function () {
					// Something went wrong, so kill the loader
					Gallery.view.loadVisibleRows.loading = null;
				});
			} else {
				// This is the safest way to do things
				Gallery.view.viewAlbum(Gallery.currentAlbum);
			}
		});
	};
	if (Gallery.view.element.height() < targetHeight) {
		Gallery.view.loadVisibleRows.loading = true;
		Gallery.view.loadVisibleRows.loading = showRows(album);
		return Gallery.view.loadVisibleRows.loading;
	}
};
Gallery.view.loadVisibleRows.loading = false;

/**
 * Builds the breadcrumb
 *
 * @param {string} albumPath
 */
Gallery.view.buildBreadCrumb = function (albumPath) {
	var i, crumbs, path;
	OC.Breadcrumb.clear();
	var albumName = $('#content').data('albumname');
	if (!albumName) {
		albumName = t('gallery', 'Pictures');
	}
	Gallery.view.pushBreadCrumb(albumName, '');

	path = '';
	crumbs = albumPath.split('/');
	for (i = 0; i < crumbs.length; i++) {
		if (crumbs[i]) {
			if (path) {
				path += '/' + crumbs[i];
			} else {
				path += crumbs[i];
			}
			Gallery.view.pushBreadCrumb(crumbs[i], path);
		}
	}
};

/**
 * Adds a path to the breadcrumb
 *
 * @fixme Needs to shorten long paths like on the Files app
 *
 * @param {string} text
 * @param {string} path
 */
Gallery.view.pushBreadCrumb = function (text, path) {
	OC.Breadcrumb.push(text, '#' + encodeURIComponent(path));
};