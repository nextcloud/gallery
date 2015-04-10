/* global OC, $, _, t, Album, GalleryImage, SlideShow, oc_requesttoken */
var Gallery = {};
Gallery.mediaTypes = {};
Gallery.images = [];
Gallery.currentAlbum = null;
Gallery.users = [];
Gallery.albumConfig = {};
Gallery.albumMap = {};
Gallery.imageMap = {};
Gallery.appName = 'galleryplus';
Gallery.token = undefined;

/**
 * Returns a list of supported media types
 *
 * @returns {string}
 */
Gallery.getMediaTypes = function () {
	var types = '';
	for (var i = 0, keys = Object.keys(Gallery.mediaTypes); i < keys.length; i++) {
		types += keys[i] + ';';
	}

	return types.slice(0, -1);
};

/**
 * Builds a map of the albums located in the current folder
 *
 * @param {string} path
 *
 * @returns {Album}
 */
Gallery.getAlbum = function (path) {
	if (!Gallery.albumMap[path]) {
		Gallery.albumMap[path] = new Album(path, [], [], OC.basename(path));
		// Attaches this album as a sub-album to the parent folder
		if (path !== '') {
			var parent = OC.dirname(path);
			if (parent === path) {
				parent = '';
			}
			Gallery.getAlbum(parent).subAlbums.push(Gallery.albumMap[path]);
		}
	}
	return Gallery.albumMap[path];
};

/**
 * Refreshes the view and starts the slideshow if required
 *
 * @param {string} path
 * @param {string} albumPath
 */
Gallery.refresh = function (path, albumPath) {
	if (Gallery.currentAlbum !== albumPath) {
		Gallery.view.init(albumPath);
	}

	// If the path is mapped, that means that it's an albumPath
	if (Gallery.albumMap[path]) {
		if (Gallery.activeSlideShow) {
			Gallery.activeSlideShow.stop();
		}
	} else if (Gallery.imageMap[path] && !Gallery.activeSlideShow) {
		Gallery.view.startSlideshow(path, albumPath);
	}
};

/**
 * Retrieves information about all the images and albums located in the current folder
 *
 * @returns {*}
 */
Gallery.getFiles = function () {
	var album, image;
	Gallery.images = [];
	Gallery.albumMap = {};
	Gallery.imageMap = {};
	Gallery.albumConfig = null;
	var currentLocation = window.location.href.split('#')[1] || '';
	var params = {
		location: currentLocation,
		mediatypes: Gallery.getMediaTypes()
	};
	// Only use the folder as a GET parameter and not as part of the URL
	var url = Gallery.buildUrl('files', '', params);
	return $.getJSON(url).then(function (data) {
		var path = null;
		var fileId = null;
		var mimeType = null;
		var mTime = null;
		var files = data.files;

		var albumInfo = data.albuminfo;
		Gallery.albumConfig = new Gallery.Config(albumInfo);

		for (var i = 0; i < files.length; i++) {
			path = files[i].path;
			fileId = files[i].fileid;
			mimeType = files[i].mimetype;
			mTime = files[i].mtime;

			Gallery.images.push(path);

			image = new GalleryImage(path, path, fileId, mimeType, mTime);
			var dir = OC.dirname(path);
			var currentFolder = albumInfo.path;
			dir = Gallery.fixDir(path, dir, currentFolder);

			album = Gallery.getAlbum(dir);
			album.images.push(image);
			Gallery.imageMap[image.path] = image;
		}
	}, function () {
		// Triggered if we couldn't find a working folder
		Gallery.view.element.empty();
		Gallery.showEmpty();
		Gallery.currentAlbum = null;
	});
};

/**
 * Removes everything from the path between the 1st sub-folder and the file
 *
 * This enables us to show as many as 4 pictures for each albums, even if the images are found in
 * very deep sub-folders
 *
 * @param dir
 * @param currentFolder
 */
Gallery.fixDir = function (path, dir, currentFolder) {
	if (dir === path) {
		dir = '';
	}

	if (dir !== currentFolder) {
		if (currentFolder !== '') {
			currentFolder = currentFolder + '/';
			dir = dir.replace(currentFolder, '');
		}
		var parts = dir.split('/');
		dir = currentFolder + parts[0];
	}

	return dir;
};

/**
 * Sorts images and albums arrays
 *
 * @param {string} sortType
 * @param {string} sortOrder
 *
 * @returns {Function}
 */
Gallery.sortBy = function (sortType, sortOrder) {
	if (sortType === 'name') {
		if (sortOrder === 'asc') {
			//sortByNameAsc
			return function (a, b) {
				return a.path.toLowerCase().localeCompare(b.path.toLowerCase());
			};
		}
		//sortByNameDes
		return function (a, b) {
			return b.path.toLowerCase().localeCompare(a.path.toLowerCase());
		};
	}
	if (sortType === 'date') {
		if (sortOrder === 'asc') {
			//sortByDateAsc
			return function (a, b) {
				return b.mTime - a.mTime;
			};
		}
		//sortByDateDes
		return function (a, b) {
			return a.mTime - b.mTime;
		};
	}
};

/**
 * Sorts thumbnails based on user preferences
 */
Gallery.sorter = function () {
	var sortType = 'name';
	var sortOrder = 'asc';
	var albumSortType = 'name';
	var albumSortOrder = 'asc';
	if (this.id === 'sort-date-button') {
		sortType = 'date';

	}
	var currentSort = Gallery.albumConfig.getAlbumSorting();
	if (currentSort.type === sortType && currentSort.order === sortOrder) {
		sortOrder = 'des';
	}

	// Update the controls
	Gallery.view.sortControlsSetup(sortType, sortOrder);

	// We can't currently sort by album creation time
	if (sortType === 'name') {
		albumSortOrder = sortOrder;
	}

	// FIXME Rendering is still happening while we're sorting...

	// Clear before sorting
	Gallery.view.clear();

	// Sort the images
	Gallery.albumMap[Gallery.currentAlbum].images.sort(Gallery.sortBy(sortType, sortOrder));
	Gallery.albumMap[Gallery.currentAlbum].subAlbums.sort(Gallery.sortBy(albumSortType,
		albumSortOrder));

	// Save the new settings
	Gallery.albumConfig.updateSorting(sortType, sortOrder, albumSortOrder);

	// Refresh the view
	Gallery.view.viewAlbum(Gallery.currentAlbum);
};

/**
 * Builds the URL which will retrieve a large preview of the file
 *
 * @param {string} image
 *
 * @return {string}
 */
Gallery.getPreviewUrl = function (image) {
	var width = $(window).width() * window.devicePixelRatio;
	var height = $(window).height() * window.devicePixelRatio;
	var params = {
		file: image,
		x: width,
		y: height,
		requesttoken: oc_requesttoken
	};
	return Gallery.buildUrl('preview', '', params);
};

/**
 * Populates the share dialog with the needed information
 *
 * @param event
 */
Gallery.share = function (event) {
	// Clicking on share button does not trigger automatic slide-up
	$('.album-info-content').slideUp();

	if (!OC.Share.droppedDown) {
		event.preventDefault();
		event.stopPropagation();

		(function () {
			var target = OC.Share.showLink;
			OC.Share.showLink = function () {
				var r = target.apply(this, arguments);
				$('#linkText').val($('#linkText').val().replace('index.php/s/', 'index.php/apps/' +
				Gallery.appName + '/s/'));

				return r;
			};
		})();

		var albumPermissions = Gallery.albumConfig.getAlbumPermissions(Gallery.currentAlbum);
		$('a.share').data('item', albumPermissions.fileid).data('link', true)
			.data('possible-permissions', albumPermissions.permissions).
			click();
		if (!$('#linkCheckbox').is(':checked')) {
			$('#linkText').hide();
		}
	}
};

/**
 * Builds a URL pointing to one of our PHP controllers
 *
 * @param {string} endPoint
 * @param {undefined|string} path
 * @param params
 *
 * @returns {string}
 */
Gallery.buildUrl = function (endPoint, path, params) {
	if (path === undefined) {
		path = '';
	}
	var extension = '';
	if (Gallery.token) {
		params.token = Gallery.token;
		extension = '.public';
	}
	var query = OC.buildQueryString(params);
	return OC.generateUrl('apps/' + Gallery.appName + '/' + endPoint + extension + path, null) +
		'?' +
		query;
};

/**
 * Sends an archive of the current folder to the browser
 *
 * @param event
 */
Gallery.download = function (event) {
	event.preventDefault();
	OC.redirect(OC.generateUrl('s/{token}/download?path={path}&files={files}', {
		token: Gallery.token,
		path: $('#content').data('albumname'),
		files: Gallery.currentAlbum
	}));
};

/**
 * Shows an information box to the user
 *
 * @param event
 */
Gallery.showInfo = function (event) {
	event.stopPropagation();
	Gallery.infoBox.showInfo();
};

/**
 * Hide the search button while we wait for core to fix the templates
 */
Gallery.hideSearch = function () {
	$('form.searchbox').hide();
};

/**
 * Shows an empty gallery message
 */
Gallery.showEmpty = function () {
	$('#emptycontent').removeClass('hidden');
	$('#controls').addClass('hidden');
	$('#content').removeClass('icon-loading');
};

/**
 * Shows the infamous loading spinner
 */
Gallery.showLoading = function () {
	$('#emptycontent').addClass('hidden');
	$('#controls').removeClass('hidden');
	$('#content').addClass('icon-loading');
};

/**
 * Shows thumbnails
 */
Gallery.showNormal = function () {
	$('#emptycontent').addClass('hidden');
	$('#controls').removeClass('hidden');
	$('#content').removeClass('icon-loading');
};

/**
 * Shows a warning to users of old, unsupported version of Internet Explorer
 */
Gallery.showOldIeWarning = function () {
	var text = '<strong>Your browser is not supported!</strong></br>' +
		'please install one of the following alternatives</br>' +
		'<a href="http://www.getfirefox.com"><strong>Mozilla Firefox</strong></a> or ' +
		'<a href="https://www.google.com/chrome/"><strong>Google Chrome</strong></a>' +
		'</br>';
	Gallery.showHtmlNotification(text, 60);
};

/**
 * Shows a warning to users of the latest version of Internet Explorer
 */
Gallery.showModernIeWarning = function () {
	var text = '<strong>This application may not work properly on your browser.</strong></br>' +
		'For an improved experience, please install one of the following alternatives</br>' +
		'<a href="http://www.getfirefox.com"><strong>Mozilla Firefox</strong></a> or ' +
		'<a href="https://www.google.com/chrome/"><strong>Google Chrome</strong></a>' +
		'</br>';
	Gallery.showHtmlNotification(text, 15);
};

/**
 * Shows a notification at the top of the screen
 *
 * @param {string} text
 * @param {int} timeout
 */
Gallery.showHtmlNotification = function (text, timeout) {
	var options = {
		timeout: timeout,
		isHTML: true
	};
	OC.Notification.showTemporary(t('gallery', text), options);
};

/**
 * Resets the height of the content div so that the spinner can be centred
 */
Gallery.resetContentHeight = function () {
	// 200 is the space required for the footer and the browser toolbar
	$('#content').css("min-height", $(window).height() - 200);
};

/**
 * Creates a new slideshow using the images found in the current folder
 *
 * @param {array} images
 * @param {string} startImage
 * @param {bool} autoPlay
 *
 * @returns {boolean}
 */
Gallery.slideShow = function (images, startImage, autoPlay) {
	if (startImage === undefined) {
		OC.Notification.showTemporary(t('gallery', 'Aborting preview. Could not find the file'));
		return false;
	}
	var start = images.indexOf(startImage);
	images = images.map(function (image) {
		var name = OC.basename(image.path);
		var previewUrl = Gallery.getPreviewUrl(image.src);
		var params = {
			file: image.src,
			requesttoken: oc_requesttoken
		};
		var downloadUrl = Gallery.buildUrl('download', '', params);

		return {
			name: name,
			path: image.path,
			mimeType: image.mimeType,
			url: previewUrl,
			downloadUrl: downloadUrl
		};
	});

	var slideShow = new SlideShow($('#slideshow'), images);
	slideShow.onStop = function () {
		Gallery.activeSlideShow = null;
		$('#content').show();
		if (Gallery.currentAlbum !== '') {
			location.hash = encodeURIComponent(Gallery.currentAlbum);
		} else {
			location.hash = '!';
		}
	};
	Gallery.activeSlideShow = slideShow;

	slideShow.init(autoPlay);
	slideShow.show(start);
};

Gallery.activeSlideShow = null;

$(document).ready(function () {
	Gallery.hideSearch();

	Gallery.ie11AndAbove =
		navigator.userAgent.indexOf('Trident') != -1 && navigator.userAgent.indexOf('MSIE') == -1;
	Gallery.ie10AndBelow = navigator.userAgent.indexOf('MSIE') != -1;

	if (Gallery.ie10AndBelow) {
		Gallery.showOldIeWarning();
		Gallery.showEmpty();
	} else {
		if (Gallery.ie11AndAbove) {
			Gallery.showModernIeWarning();
		}

		// Needed to centre the spinner in some browsers
		Gallery.resetContentHeight();
		Gallery.showLoading();

		Gallery.view = new Gallery.View();
		Gallery.token = Gallery.view.getRequestToken();

		$.getJSON(Gallery.buildUrl('mediatypes', '', {}))
			.then(function (mediaTypes) {
				//console.log('mediaTypes', mediaTypes);
				Gallery.mediaTypes = mediaTypes;
			})
			.then(function () {
				Gallery.getFiles().then(function () {
					window.onhashchange();
				});
			});

		$('#openAsFileListButton').click(function () {
			var subUrl = '';
			var params = {path: '/' + encodeURIComponent(Gallery.currentAlbum)};
			if (Gallery.token) {
				params.token = Gallery.token;
				subUrl = 's/{token}?path={path}';
			} else {
				subUrl = 'apps/files?dir={path}';
			}
			OC.redirect(OC.generateUrl(subUrl, params));
		});

		$(document).click(function () {
			$('.album-info-content').slideUp();
		});

		$(window).scroll(function () {
			Gallery.view.loadVisibleRows(Gallery.albumMap[Gallery.currentAlbum],
				Gallery.currentAlbum);
		});
		$('#content-wrapper').scroll(function () {
			Gallery.view.loadVisibleRows(Gallery.albumMap[Gallery.currentAlbum],
				Gallery.currentAlbum);
		});

		// A shorter delay avoids redrawing the view in the middle of a previous request, but it
		// may kill baby CPUs
		var windowWidth = $(window).width();
		var windowHeight = $(window).height();
		$(window).resize(_.throttle(function () {
			if (windowWidth !== $(window).width()) {
				Gallery.view.viewAlbum(Gallery.currentAlbum);
				// 320 is the width required for the buttons
				Gallery.view.breadcrumb.setMaxWidth(windowWidth - 320);

				windowWidth = $(window).width();
			}
			if (windowHeight !== $(window).height()) {
				Gallery.resetContentHeight();
				var infoContentElement = $('.album-info-content');
				// 150 is the space required for the browser toolbar on some mobile OS
				infoContentElement.css('max-height', windowHeight - 150);

				windowHeight = $(window).height();
			}
		}, 250));
	}
});

window.onhashchange = function () {
	// The hash location is ALWAYS encoded
	var path = decodeURIComponent(window.location.href.split('#')[1] || '');
	var albumPath = OC.dirname(path);
	if (Gallery.albumMap[path]) {
		albumPath = path;
	} else if (!Gallery.albumMap[albumPath]) {
		albumPath = '';
	}
	if (Gallery.currentAlbum !== null && Gallery.currentAlbum !== albumPath) {
		Gallery.getFiles().done(function () {
			Gallery.refresh(path, albumPath);
		});
	} else {
		Gallery.refresh(path, albumPath);
	}
};
