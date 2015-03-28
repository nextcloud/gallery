/* global OC, $, _, t, Album, GalleryImage, SlideShow, oc_requesttoken, marked */
var Gallery = {};
Gallery.images = [];
Gallery.currentAlbum = null;
Gallery.users = [];
Gallery.albumsInfo = {};
Gallery.albumMap = {};
Gallery.imageMap = {};
Gallery.appName = 'galleryplus';
Gallery.token = undefined;
Gallery.currentSort = {};

Gallery.getAlbum = function (path) {
	if (!Gallery.albumMap[path]) {
		Gallery.albumMap[path] = new Album(path, [], [], OC.basename(path));
		// Attaches sub-albums to the current one
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

// fill the albums from Gallery.images
Gallery.fillAlbums = function () {
	var album, image;
	Gallery.images = [];
	Gallery.albumMap = {};
	Gallery.imageMap = {};
	var currentFolder = decodeURI(window.location.href.split('#')[1] || '');
	var url = Gallery.buildUrl('files', '', {location: currentFolder});
	return $.getJSON(url).then(function (data) {
		var path = null;
		var fileId = null;
		var mimeType = null;
		var mTime = null;
		var files = data.files;
		var albumInfo = data.albuminfo;
		Gallery.albumsInfo[albumInfo.path] = {
			fileid: albumInfo.fileid,
			permissions: albumInfo.permissions,
			description: albumInfo.description,
			copyright: albumInfo.copyright,
			copyrightLink: albumInfo.copyright_link
		};
		for (var i = 0; i < files.length; i++) {
			path = files[i].path;
			fileId = files[i].fileid;
			mimeType = files[i].mimetype;
			mTime = files[i].mtime;

			Gallery.images.push(path);

			image = new GalleryImage(path, path, fileId, mimeType, mTime);
			var dir = OC.dirname(path);
			if (dir === path) {
				dir = '';
			}
			album = Gallery.getAlbum(dir);
			album.images.push(image);
			Gallery.imageMap[image.path] = image;
		}
		var sortType = 'name';
		var sortOrder = 'asc';
		var albumSortOrder = 'asc';
		if (!$.isEmptyObject(albumInfo.sorting)) {
			sortType = albumInfo.sorting;
		}
		if (!$.isEmptyObject(albumInfo.sort_order)) {
			sortOrder = albumInfo.sort_order;
			if (sortType === 'name') {
				albumSortOrder = sortOrder;
			}
		}

		Gallery.currentSort = {
			type: sortType,
			order: sortOrder
		};

		for (var j = 0, keys = Object.keys(Gallery.albumMap); j < keys.length; j++) {
			Gallery.albumMap[keys[j]].images.sort(Gallery.sortBy(sortType, sortOrder));
			Gallery.albumMap[keys[j]].subAlbums.sort(Gallery.sortBy('name', albumSortOrder));
		}
	}, function () {
		// Triggered if we couldn't find a working folder
		Gallery.view.element.empty();
		Gallery.showEmpty();
		Gallery.currentAlbum = null;
	});
};

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

		var albumInfo = Gallery.albumsInfo[Gallery.currentAlbum];
		$('a.share').data('item', albumInfo.fileid).data('link', true)
			.data('possible-permissions', albumInfo.permissions).
			click();
		if (!$('#linkCheckbox').is(':checked')) {
			$('#linkText').hide();
		}
	}
};

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

Gallery.download = function (event) {
	event.preventDefault();
	OC.redirect(OC.generateUrl('s/{token}/download?path={path}&files={files}', {
		token: Gallery.token,
		path: $('#content').data('albumname'),
		files: Gallery.currentAlbum
	}));
};

Gallery.showInfo = function (event) {
	event.stopPropagation();
	var infoContentElement = $('.album-info-content');
	var adjustHeight = function () {
		infoContentElement.removeClass('icon-loading');
		var newHeight = infoContentElement[0].scrollHeight;
		infoContentElement.animate({
			height: newHeight + 40
		}, 500);
		infoContentElement.scrollTop(0);
	};

	if (infoContentElement.is(':visible')) {
		infoContentElement.slideUp();
	} else {
		var albumInfo = Gallery.albumsInfo[Gallery.currentAlbum];
		if (!albumInfo.infoLoaded) {
			infoContentElement.addClass('icon-loading');
			infoContentElement.empty();
			infoContentElement.height(100);
			infoContentElement.slideDown();
			if (!$.isEmptyObject(albumInfo.description)) {
				var params = {
					file: Gallery.currentAlbum + '/' + albumInfo.description
				};
				var descriptionUrl = Gallery.buildUrl('download', '', params);
				$.get(descriptionUrl).done(function (data) {
						infoContentElement.append(marked(data));
						infoContentElement.find('a').attr("target", "_blank");
						Gallery.showCopyright(albumInfo, infoContentElement);
						adjustHeight();
					}
				).fail(function () {
						infoContentElement.append('<p>' +
						t('gallery', 'Could not load the description') + '</p>');
						Gallery.showCopyright(albumInfo, infoContentElement);
						adjustHeight();
					});
			} else {
				Gallery.showCopyright(albumInfo, infoContentElement);
				adjustHeight();
			}
			albumInfo.infoLoaded = true;
		} else {
			infoContentElement.slideDown();
		}
		infoContentElement.scrollTop(0);
	}
};

Gallery.showCopyright = function (albumInfo, infoContentElement) {
	if (!$.isEmptyObject(albumInfo.copyright) || !$.isEmptyObject(albumInfo.copyrightLink)) {
		var copyright;
		var copyrightTitle = $('<h4/>');
		copyrightTitle.append(t('gallery', 'Copyright'));
		infoContentElement.append(copyrightTitle);

		if (!$.isEmptyObject(albumInfo.copyright)) {
			copyright = marked(albumInfo.copyright);
		} else {
			copyright = '<p>' + t('gallery', 'Copyright notice') + '</p>';
		}

		if (!$.isEmptyObject(albumInfo.copyrightLink)) {
			var subUrl = '';
			var params = {
				path: '/' + Gallery.currentAlbum,
				files: albumInfo.copyrightLink
			};
			if (Gallery.token) {
				params.token = Gallery.token;
				subUrl = 's/{token}/download?dir={path}&files={files}';
			} else {
				subUrl = 'apps/files/ajax/download.php?dir={path}&files={files}';
			}
			var copyrightUrl = OC.generateUrl(subUrl, params);
			var copyrightLink = $('<a>', {
				html: copyright,
				title: copyright,
				href: copyrightUrl,
				target: "_blank"
			});
			infoContentElement.append(copyrightLink);
		} else {
			infoContentElement.append(copyright);
		}
	}
};

Gallery.view = {};
Gallery.view.element = null;
Gallery.view.cache = {};

Gallery.view.clear = function () {
	Gallery.view.element.empty();
	Gallery.showLoading();
};

Gallery.view.init = function (albumPath) {
	var downloadButton = $('#download');
	var shareButton = $('button.share');
	var infoButton = $('#album-info-button');
	downloadButton.off();
	shareButton.off();
	infoButton.off();

	if (Gallery.images.length === 0) {
		Gallery.showEmpty();
	} else {
		OC.Breadcrumb.container = $('#breadcrumbs');
		Gallery.view.viewAlbum(albumPath);
		downloadButton.click(Gallery.download);
		shareButton.click(Gallery.share);
		infoButton.click(Gallery.showInfo);
	}
};

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
	setTimeout(function () {
		Gallery.view.loadVisibleRows.activeIndex = 0;
		Gallery.view.loadVisibleRows(Gallery.albumMap[Gallery.currentAlbum], Gallery.currentAlbum);
	}, 0);
};

/**
 * Shows or hides the share button depending on if we're in a public gallery or not
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
	var albumInfo = Gallery.albumsInfo[Gallery.currentAlbum];
	if ($.isEmptyObject(albumInfo.description) &&
		$.isEmptyObject(albumInfo.copyright) &&
		$.isEmptyObject(albumInfo.copyrightLink)) {
		infoButton.hide();
	} else {
		infoButton.show();
	}

	var descriptionElement = $('.album-info-content');
	descriptionElement.slideUp();
};

Gallery.view.loadVisibleRows = function (album, path) {
	if (Gallery.view.loadVisibleRows.loading &&
		Gallery.view.loadVisibleRows.loading.state() !== 'resolved') {
		return Gallery.view.loadVisibleRows.loading;
	}
	// load 2 windows worth of rows
	var scroll = $('#content-wrapper').scrollTop() + $(window).scrollTop();
	var targetHeight = ($(window).height() * 2) + scroll;
	var showRows = function (album) {
		if (!(album.viewedItems < album.subAlbums.length + album.images.length)) {
			Gallery.view.loadVisibleRows.loading = null;
			return;
		}
		return album.getNextRow($(window).width()).then(function (row) {
			return row.getDom().then(function (dom) {
				// defer removal of loading class to trigger CSS3 animation
				_.defer(function () {
					dom.removeClass('loading');
				});
				if (Gallery.currentAlbum !== path) {
					Gallery.view.loadVisibleRows.loading = null;
					return; //throw away the row if the user has navigated away in the meantime
				}
				if (Gallery.view.element.length === 1) {
					Gallery.showNormal();
				}
				Gallery.view.element.append(dom);
				if (album.viewedItems < album.subAlbums.length + album.images.length &&
					Gallery.view.element.height() < targetHeight) {
					return showRows(album);
				}
				Gallery.view.loadVisibleRows.loading = null;
			}, function () {
				Gallery.view.loadVisibleRows.loading = null;
			});
		});
	};
	if (Gallery.view.element.height() < targetHeight) {
		Gallery.view.loadVisibleRows.loading = true;
		Gallery.view.loadVisibleRows.loading = showRows(album);
		return Gallery.view.loadVisibleRows.loading;
	}
};
Gallery.view.loadVisibleRows.loading = false;

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

Gallery.view.pushBreadCrumb = function (text, path) {
	OC.Breadcrumb.push(text, '#' + path);
};

Gallery.hideSearch = function () {
	$('form.searchbox').hide();
};

Gallery.showEmpty = function () {
	$('#emptycontent').removeClass('hidden');
	$('#controls').addClass('hidden');
	$('#content').removeClass('icon-loading');
};

Gallery.showLoading = function () {
	$('#emptycontent').addClass('hidden');
	$('#controls').removeClass('hidden');
	$('#content').addClass('icon-loading');
};

Gallery.showNormal = function () {
	$('#emptycontent').addClass('hidden');
	$('#controls').removeClass('hidden');
	$('#content').removeClass('icon-loading');
};

Gallery.showOldIeWarning = function () {
	var text = '<strong>Your browser is not supported!</strong></br>' +
		'please install one of the following alternatives</br>' +
		'<a href="http://www.getfirefox.com"><strong>Mozilla Firefox</strong></a> or ' +
		'<a href="https://www.google.com/chrome/"><strong>Google Chrome</strong></a>' +
		'</br>';
	Gallery.showHtmlNotification(text, 60);
};

Gallery.showModernIeWarning = function () {
	var text = '<strong>This application may not work properly on your browser.</strong></br>' +
		'For an improved experience, please install one of the following alternatives</br>' +
		'<a href="http://www.getfirefox.com"><strong>Mozilla Firefox</strong></a> or ' +
		'<a href="https://www.google.com/chrome/"><strong>Google Chrome</strong></a>' +
		'</br>';
	Gallery.showHtmlNotification(text, 15);
};

Gallery.showHtmlNotification = function (text, timeout) {
	var options = {
		timeout: timeout,
		isHTML: true
	};
	OC.Notification.showTemporary(t('gallery', text), options);
};

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
		location.hash = encodeURI(Gallery.currentAlbum);
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
		$('#content').height($(window).height());
		Gallery.showLoading();

		Gallery.view.element = $('#gallery');
		if (Gallery.view.element.data('token')) {
			Gallery.token = Gallery.view.element.data('token');
		}

		if (Gallery.view.element.data('requesttoken')) {
			oc_requesttoken = Gallery.view.element.data('requesttoken');
		}

		Gallery.fillAlbums().then(function () {
			window.onhashchange();
		});

		$('#openAsFileListButton').click(function () {
			var subUrl = '';
			var params = {path: '/' + Gallery.currentAlbum};
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
			Gallery.view.loadVisibleRows(Gallery.albumMap[Gallery.currentAlbum], Gallery.currentAlbum);
		});
		$('#content-wrapper').scroll(function () {
			Gallery.view.loadVisibleRows(Gallery.albumMap[Gallery.currentAlbum], Gallery.currentAlbum);
		});

		// A shorter delay avoids redrawing the view in the middle of a previous request, but it
		// may kill baby CPUs
		$(window).resize(_.throttle(function () {
			Gallery.view.viewAlbum(Gallery.currentAlbum);
			var infoContentElement = $('.album-info-content');
			infoContentElement.css('max-height', $(window).height() - 150);
		}, 500));
	}
});

window.onhashchange = function () {
	var path = decodeURI(window.location.href.split('#')[1] || '');
	var albumPath = OC.dirname(path);
	if (Gallery.albumMap[path]) {
		albumPath = path;
	} else if (!Gallery.albumMap[albumPath]) {
		albumPath = '';
	}
	if (Gallery.currentAlbum !== null && Gallery.currentAlbum !== albumPath) {
		Gallery.fillAlbums().done(function () {
			Gallery.refresh(path, albumPath);
		});
	} else {
		Gallery.refresh(path, albumPath);
	}
};
