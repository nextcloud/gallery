/* global OC, $, t, Album, GalleryImage, SlideShow, oc_requesttoken */
var Gallery = {};
Gallery.images = [];
Gallery.currentAlbum = null;
Gallery.config = {};
Gallery.albumMap = {};
Gallery.imageMap = {};
Gallery.albumCache = {};
Gallery.appName = 'galleryplus';
Gallery.token = undefined;

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
	var album, image, albumEtag;
	Gallery.images = [];
	Gallery.albumMap = {};
	Gallery.imageMap = {};
	var currentLocation = window.location.href.split('#')[1] || '';
	var albumCache = Gallery.albumCache[decodeURIComponent(currentLocation)];
	if (!$.isEmptyObject(albumCache)) {
		albumEtag = albumCache.etag;
	}
	var params = {
		location: currentLocation,
		mediatypes: Gallery.config.getMediaTypes(),
		features: Gallery.config.galleryFeatures,
		etag: albumEtag
	};
	// Only use the folder as a GET parameter and not as part of the URL
	var url = Gallery.utility.buildGalleryUrl('files', '', params);
	return $.getJSON(url).then(function (/**{albuminfo}*/ data) {
		var path = null;
		var fileId = null;
		var mimeType = null;
		var mTime = null;
		var etag = null;
		var files = null;
		var albumInfo = data.albuminfo;
		Gallery.config.setAlbumConfig(albumInfo);
		if (albumInfo.etag === albumEtag) {
			Gallery.images = albumCache.images;
			Gallery.imageMap = albumCache.imageMap;
			Gallery.albumMap = albumCache.albumMap;
		} else {
			files = data.files;
			for (var i = 0; i < files.length; i++) {
				path = files[i].path;
				fileId = files[i].fileid;
				mimeType = files[i].mimetype;
				mTime = files[i].mtime;
				etag = files[i].etag;

				Gallery.images.push(path);

				image = new GalleryImage(path, path, fileId, mimeType, mTime, etag);
				var dir = OC.dirname(path);
				if (dir === path) {
					dir = '';
				}
				album = Gallery.getAlbum(dir);
				album.images.push(image);
				Gallery.imageMap[image.path] = image;
			}
			Gallery.albumCache[albumInfo.path] = {
				etag: albumInfo.etag,
				files: files,
				images: Gallery.images,
				imageMap: Gallery.imageMap,
				albumMap: Gallery.albumMap
			};
		}
	}, function () {
		// Triggered if we couldn't find a working folder
		Gallery.view.element.empty();
		Gallery.showEmpty();
		Gallery.currentAlbum = null;
	});
};

/**
 * Sorts albums and images based on user preferences
 */
Gallery.sorter = function () {
	var sortType = 'name';
	var sortOrder = 'asc';
	var albumSortType = 'name';
	var albumSortOrder = 'asc';
	if (this.id === 'sort-date-button') {
		sortType = 'date';

	}
	var currentSort = Gallery.config.albumSorting;
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
	Gallery.albumMap[Gallery.currentAlbum].images.sort(Gallery.utility.sortBy(sortType, sortOrder));
	Gallery.albumMap[Gallery.currentAlbum].subAlbums.sort(Gallery.utility.sortBy(albumSortType,
		albumSortOrder));

	// Save the new settings
	Gallery.config.updateAlbumSorting(sortType, sortOrder, albumSortOrder);

	// Refresh the view
	Gallery.view.viewAlbum(Gallery.currentAlbum);
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

		var albumPermissions = Gallery.config.albumPermissions;
		$('a.share').data('item', albumPermissions.fileid).data('link', true)
			.data('possible-permissions', albumPermissions.permissions).
			click();
		if (!$('#linkCheckbox').is(':checked')) {
			$('#linkText').hide();
		}
	}
};

/**
 * Sends an archive of the current folder to the browser
 *
 * @param event
 */
Gallery.download = function (event) {
	event.preventDefault();

	var path = $('#content').data('albumname');
	var files = Gallery.currentAlbum;
	var downloadUrl = Gallery.utility.buildFilesUrl(path, files);

	OC.redirect(downloadUrl);
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
 * Lets the user add the shared files to his ownCloud
 *
 * @param event
 */
Gallery.showSaveForm = function (event) {
	$(this).hide();
	$('.save-form').css('display', 'inline');
	$('#remote_address').focus();
};

/**
 * Sends the shared files to the viewer's ownCloud
 *
 * @param event
 */
Gallery.saveForm = function (event) {
	event.preventDefault();

	var saveElement = $('#save');
	var remote = $(this).find('input[type="text"]').val();
	var owner = saveElement.data('owner');
	var name = saveElement.data('name');
	var isProtected = saveElement.data('protected');
	Gallery.saveToOwnCloud(remote, Gallery.token, owner, name, isProtected);
};

/**
 * Saves the folder to a remote ownCloud installation
 *
 * Our location is the remote for the other server
 *
 * @param {string} remote
 * @param {string}token
 * @param {string}owner
 * @param {string}name
 * @param {bool} isProtected
 */
Gallery.saveToOwnCloud = function (remote, token, owner, name, isProtected) {
	var location = window.location.protocol + '//' + window.location.host + OC.webroot;
	var isProtectedInt = (isProtected) ? 1 : 0;
	var url = remote + '/index.php/apps/files#' + 'remote=' + encodeURIComponent(location)
		+ "&token=" + encodeURIComponent(token) + "&owner=" + encodeURIComponent(owner) + "&name=" +
		encodeURIComponent(name) + "&protected=" + isProtectedInt;

	if (remote.indexOf('://') > 0) {
		OC.redirect(url);
	} else {
		// if no protocol is specified, we automatically detect it by testing https and http
		// this check needs to happen on the server due to the Content Security Policy directive
		$.get(OC.generateUrl('apps/files_sharing/testremote'),
			{remote: remote}).then(function (protocol) {
				if (protocol !== 'http' && protocol !== 'https') {
					OC.dialogs.alert(t('files_sharing',
							'No ownCloud installation (7 or higher) found at {remote}',
							{remote: remote}),
						t('files_sharing', 'Invalid ownCloud url'));
				} else {
					OC.redirect(protocol + '://' + url);
				}
			});
	}
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
 * Resets the height of the content div so that the spinner can be centred
 */
Gallery.resetContentHeight = function () {
	// 200 is the space required for the footer and the browser toolbar
	$('#content').css("min-height", $(window).height() - 200);
};

/**
 * Creates a new slideshow using the images found in the current folder
 *
 * @param {Array} images
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
		var previewUrl = Gallery.utility.getPreviewUrl(image.fileId, image.etag);
		var downloadUrl = previewUrl + '&download';

		return {
			name: name,
			path: image.path,
			file: image.fileId,
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
