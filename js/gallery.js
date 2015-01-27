/* global OC, $, _, t, Album, GalleryImage, SlideShow, Thumbnail, oc_requesttoken */
var Gallery = {};
Gallery.images = [];
Gallery.currentAlbum = '';
Gallery.users = [];
Gallery.albumMap = {};
Gallery.imageMap = {};
Gallery.appName = 'galleryplus';
Gallery.token = undefined;

Gallery.getAlbum = function (path, token) {
	if (!Gallery.albumMap[path]) {
		Gallery.albumMap[path] = new Album(path, [], [], OC.basename(path), token);
		if (path !== '') {
			var parent = OC.dirname(path);
			if (parent === path) {
				parent = '';
			}
			Gallery.getAlbum(parent, token).subAlbums.push(Gallery.albumMap[path]);
		}
	}
	return Gallery.albumMap[path];
};

// fill the albums from Gallery.images
Gallery.fillAlbums = function () {
	var sortFunction = function (a, b) {
		return a.path.toLowerCase().localeCompare(b.path.toLowerCase());
	};
	var album, image;
	var url = Gallery.buildUrl('images', {});
	return $.getJSON(url).then(function (data) {
		//Gallery.images = data;
		var path = null;
		var mimeType = null;
		for (var i = 0; i < data.length; i++) {
			path = data[i].path;
			Gallery.images.push(path);
			mimeType = data[i].mimetype;
			image = new GalleryImage(path, path, Gallery.token);
			image.mimeType = mimeType;
			var dir = OC.dirname(path);
			if (dir === path) {
				dir = '';
			}
			album = Gallery.getAlbum(dir, Gallery.token);
			album.images.push(image);
			Gallery.imageMap[image.path] = image;
		}

		for (path in Gallery.albumMap) {
			Gallery.albumMap[path].images.sort(sortFunction);
			Gallery.albumMap[path].subAlbums.sort(sortFunction);
		}
	});
};

Gallery.getAlbumInfo = function (album) {
	if (Gallery.token) {
		return [];
	}
	if (!Gallery.getAlbumInfo.cache[album]) {
		var def = new $.Deferred();
		Gallery.getAlbumInfo.cache[album] = def;

		var url = OC.generateUrl('apps/' + Gallery.appName +
		'/albums?albumpath={albumpath}', {albumpath: encodeURIComponent(album)});
		$.getJSON(url, function (data) {
			def.resolve(data);
		});
	}
	return Gallery.getAlbumInfo.cache[album];
};

Gallery.getAlbumInfo.cache = {};

Gallery.getImage = function (image) {
	var width = $(document).width() * window.devicePixelRatio;
	var height = $(document).height() * window.devicePixelRatio;
	var params = {
		file: image,
		x: width,
		y: height,
		requesttoken: oc_requesttoken
	};
	return Gallery.buildUrl('preview', params);
};

Gallery.share = function (event) {
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

		Gallery.getAlbumInfo(Gallery.currentAlbum).then(function (info) {
			$('a.share').data('item', info.fileid).data('link', true)
				.data('possible-permissions', info.permissions).
				click();
			if (!$('#linkCheckbox').is(':checked')) {
				$('#linkText').hide();
			}
		});
	}
};

Gallery.buildUrl = function (endPoint, params) {
	var extension = '';
	if (Gallery.token) {
		params.token = Gallery.token;
		extension = '.public';
	}
	var query = OC.buildQueryString(params);
	return OC.generateUrl('apps/' + Gallery.appName + '/' + endPoint + extension, null) + '?' +
		query;
};

Gallery.view = {};
Gallery.view.element = null;
Gallery.view.cache = {};

Gallery.view.clear = function () {
	Gallery.view.element.empty();
	Gallery.showLoading();
};

Gallery.view.viewAlbum = function (albumPath) {
	albumPath = albumPath || '';
	if (!Gallery.albumMap[albumPath]) {
		return;
	}

	Gallery.view.clear();
	if (albumPath !== Gallery.currentAlbum) {
		Gallery.view.loadVisibleRows.loading = false;
	}
	Gallery.currentAlbum = albumPath;
	Gallery.view.shareButton(albumPath);
	Gallery.view.buildBreadCrumb(albumPath);

	Gallery.getAlbumInfo(Gallery.currentAlbum); //preload album info
	Gallery.albumMap[albumPath].viewedItems = 0;
	setTimeout(function () {
		Gallery.view.loadVisibleRows.activeIndex = 0;
		Gallery.view.loadVisibleRows(Gallery.albumMap[Gallery.currentAlbum], Gallery.currentAlbum);
	}, 0);
};

Gallery.view.shareButton = function (albumPath) {
	if (albumPath === '' || Gallery.token) {
		$('button.share').hide();
	} else {
		$('button.share').show();
	}
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
				} else {
					Gallery.view.loadVisibleRows.loading = null;
				}
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
	OC.Breadcrumb.push(text, '#' + path).click(function () {
		Gallery.view.viewAlbum(path);
	});
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

Gallery.slideShow = function (images, startImage, autoPlay) {
	var start = images.indexOf(startImage);
	images = images.map(function (image) {
		var name = OC.basename(image.path);
		var url = Gallery.getImage(image.src);
		var params = {
			file: image.src,
			requesttoken: oc_requesttoken
		};
		var downloadUrl = Gallery.buildUrl('download', params);

		return {
			name: name,
			path: image.path,
			mimeType: image.mimeType,
			url: url,
			downloadUrl: downloadUrl
		};
	});

	var slideShow = new SlideShow($('#slideshow'), images);
	Thumbnail.concurrent = 1;
	slideShow.onStop = function () {
		Gallery.activeSlideShow = null;
		$('#content').show();
		location.hash = encodeURI(Gallery.currentAlbum);
		Thumbnail.concurrent = 3;
	};
	Gallery.activeSlideShow = slideShow;

	slideShow.init(autoPlay);
	slideShow.show(start);
};

Gallery.activeSlideShow = null;

$(document).ready(function () {
	Gallery.showLoading();

	Gallery.view.element = $('#gallery');
	if (Gallery.view.element.data('token')) {
		Gallery.token = Gallery.view.element.data('token');
	}

	if (Gallery.view.element.data('requesttoken')) {
		oc_requesttoken = Gallery.view.element.data('requesttoken');
	}

	Gallery.fillAlbums().then(function () {
		if (Gallery.images.length === 0) {
			Gallery.showEmpty();
		} else {
			OC.Breadcrumb.container = $('#breadcrumbs');
			window.onhashchange();
			$('button.share').click(Gallery.share);
		}
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
		window.location.href = OC.generateUrl(subUrl, params);
	});
	$('#download').click(function (e) {
		e.preventDefault();
		OC.redirect(OC.generateUrl('s/{token}/download?path={path}&files={files}', {
			token: Gallery.token,
			path: $('#content').data('albumname'),
			files: Gallery.currentAlbum
		}));
	});

	$(window).scroll(function () {
		Gallery.view.loadVisibleRows(Gallery.albumMap[Gallery.currentAlbum], Gallery.currentAlbum);
	});
	$('#content-wrapper').scroll(function () {
		Gallery.view.loadVisibleRows(Gallery.albumMap[Gallery.currentAlbum], Gallery.currentAlbum);
	});

	$(window).resize(_.throttle(function () {
		Gallery.view.viewAlbum(Gallery.currentAlbum);
	}, 500));
});

window.onhashchange = function () {
	var path = decodeURI(location.hash).substr(1);
	if (Gallery.albumMap[path]) {
		if (Gallery.activeSlideShow) {
			Gallery.activeSlideShow.stop();
		}
		path = decodeURIComponent(path);
		if (Gallery.currentAlbum !== path || path === '') {
			Gallery.view.viewAlbum(path);
		}
	} else if (!Gallery.activeSlideShow) {
		var albumPath = OC.dirname(path);
		if (albumPath === path) {
			albumPath = '';
		}
		if (Gallery.currentAlbum !== albumPath || albumPath === '') {
			Gallery.view.viewAlbum(albumPath);
		}
		var album = Gallery.albumMap[albumPath];
		var images = album.images;
		var startImage = Gallery.imageMap[path];
		Gallery.slideShow(images, startImage);
	}
};
