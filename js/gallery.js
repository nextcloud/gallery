var Gallery = {};
Gallery.images = [];
Gallery.currentAlbum = '';
Gallery.users = [];
Gallery.albumMap = {};
Gallery.imageMap = {};

Gallery.getAlbum = function (path) {
	if (!Gallery.albumMap[path]) {
		Gallery.albumMap[path] = new Album(path, [], [], OC.basename(path));
		var parent = OC.dirname(path);
		if (parent && parent !== path) {
			Gallery.getAlbum(parent).subAlbums.push(Gallery.albumMap[path]);
		}
	}
	return Gallery.albumMap[path];
};

// fill the albums from Gallery.images
Gallery.fillAlbums = function () {
	var def = new $.Deferred();
	var token = $('#gallery').data('token');
	var album, image;
	$.getJSON(OC.filePath('gallery', 'ajax', 'getimages.php'), {token: token}).then(function (data) {
		Gallery.users = data.users;
		Gallery.displayNames = data.displayNames;
		data.images.sort(function (a, b) {
			return a.toLowerCase().localeCompare(b.toLowerCase());
		});
		Gallery.images = data.images;

		for (var i = 0; i < Gallery.images.length; i++) {
			image = new GalleryImage(Gallery.images[i], false);
			album = Gallery.getAlbum(OC.dirname(Gallery.images[i]));
			album.images.push(image);
			Gallery.imageMap[Gallery.images[i]] = image;
		}
		def.resolve();
	});
	return def;
};

Gallery.getAlbumInfo = function (album) {
	if (album === $('#gallery').data('token')) {
		return [];
	}
	if (!Gallery.getAlbumInfo.cache[album]) {
		var def = new $.Deferred();
		Gallery.getAlbumInfo.cache[album] = def;
		$.getJSON(OC.filePath('gallery', 'ajax', 'gallery.php'), {gallery: album}, function (data) {
			def.resolve(data);
		});
	}
	return Gallery.getAlbumInfo.cache[album];
};
Gallery.getAlbumInfo.cache = {};
Gallery.getImage = function (image) {
	return OC.filePath('gallery', 'ajax', 'image.php') + '?file=' + encodeURIComponent(image);
};
Gallery.share = function (event) {
	if (!OC.Share.droppedDown) {
		event.preventDefault();
		event.stopPropagation();

		(function () {
			var target = OC.Share.showLink;
			OC.Share.showLink = function () {
				var r = target.apply(this, arguments);
				$('#linkText').val($('#linkText').val().replace('service=files', 'service=gallery'));
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
Gallery.view = {};
Gallery.view.element = null;
Gallery.view.clear = function () {
	Gallery.view.element.empty();
};
Gallery.view.cache = {};


Gallery.view.viewAlbum = function (albumPath) {
	var i, crumbs, path;
//	if (!albumPath) {
//		albumPath = $('#gallery').data('token');
//	}
	if (!albumPath || albumPath === '') {
		albumPath = OC.currentUser;
	}
	if (!Gallery.albumMap[albumPath]) {
		return;
	}
	console.log(albumPath);

	Gallery.view.clear();
	Gallery.currentAlbum = albumPath;

	if (albumPath === OC.currentUser) {
		$('button.share').hide();
	} else {
		$('button.share').show();
	}

	OC.Breadcrumb.clear();
	var albumName = $('#content').data('albumname');
	if (!albumName) {
		albumName = t('gallery', 'Pictures');
	}
	OC.Breadcrumb.push(albumName, '#').click(function () {
		Gallery.view.viewAlbum(OC.currentUser);
	});
	crumbs = albumPath.split('/');
	//first entry is username
	path = crumbs.splice(0, 1);
	for (i = 0; i < crumbs.length; i++) {
		if (crumbs[i]) {
			path += '/' + crumbs[i];
			Gallery.view.pushBreadCrumb(crumbs[i], path);
		}
	}
//
//	if (albumPath === OC.currentUser) {
//		Gallery.view.showUsers();
//	}


	Gallery.getAlbumInfo(Gallery.currentAlbum); //preload album info

	Gallery.view.loadVisibleRows.loading = false;
	Gallery.albumMap[albumPath].viewedItems = 0;
	setTimeout(Gallery.view.loadVisibleRows, 0);
};

Gallery.view.loadVisibleRows = function () {
	if (Gallery.view.loadVisibleRows.loading) {
		return;
	}
	var currentAlbum = Gallery.currentAlbum;
	// load 2 windows worth of rows
	var targetHeight = ($(window).height() * 2) + $(window).scrollTop();
	var showRows = function (album) {
		album.getNextRow(Gallery.view.element.width()).then(function (row) {
			console.log(row);
			return row.getDom().then(function (dom) {
				if (Gallery.currentAlbum !== currentAlbum) {
					return; //throw away the row if the user has navigated away in the meantime
				}
				Gallery.view.element.append(dom);
				if (album.viewedItems < album.subAlbums.length + album.images.length && Gallery.view.element.height() < targetHeight) {
					showRows(album);
				} else {
					Gallery.view.loadVisibleRows.loading = false;
				}
			}, function () {
				Gallery.view.loadVisibleRows.loading = false;
			});
		});
	};
	if (Gallery.view.element.height() < targetHeight) {
		Gallery.view.loadVisibleRows.loading = true;
		showRows(Gallery.albumMap[Gallery.currentAlbum]);
	}
};
Gallery.view.loadVisibleRows.loading = false;

Gallery.view.pushBreadCrumb = function (text, path) {
	OC.Breadcrumb.push(text, '#' + path).click(function () {
		Gallery.view.viewAlbum(path);
	});
};

Gallery.view.showUsers = function () {
	var i, j, user, head, subAlbums, album, singleImages;
	for (i = 0; i < Gallery.users.length; i++) {
		singleImages = [];
		user = Gallery.users[i];
		subAlbums = Gallery.subAlbums[user];
		if (subAlbums) {
			if (subAlbums.length > 0) {
				head = $('<h2/>');
				head.text(t('gallery', 'Shared by') + ' ' + Gallery.displayNames[user]);
				$('#gallery').append(head);
				for (j = 0; j < subAlbums.length; j++) {
					album = subAlbums[j];
					Gallery.view.addAlbum(album);
					Gallery.view.element.append(' '); //add a space for justify
				}
			}
		}
		for (j = 0; j < Gallery.albums[user].length; j++) {
			Gallery.view.addImage(Gallery.albums[user][j]);
		}
	}
};

$(document).ready(function () {
	Gallery.view.element = $('#gallery');
	Gallery.fillAlbums().then(function () {
		OC.Breadcrumb.container = $('#breadcrumbs');
		window.onhashchange();
		$('button.share').click(Gallery.share);
	});

	Gallery.view.element.on('click', 'a.image', function (event) {
		event.preventDefault();
		var path = $(this).data('path');
		var album = Gallery.albumMap[OC.dirname(path)];
		if (location.hash !== encodeURI(path)) {
			location.hash = encodeURI(path);
			Thumbnail.paused = true;
			var images = album.images.map(function (image) {
				return Gallery.getImage(image.src);
			});
			var i = images.indexOf(Gallery.getImage(path));
			Slideshow.start(images, i);
		}
	});

	$('#openAsFileListButton').click(function (event) {
		window.location.href = window.location.href.replace('service=gallery', 'service=files');
	});

	jQuery.fn.slideShow.onstop = function () {
		$('#content').show();
		Thumbnail.paused = false;
		var albumParts = Gallery.currentAlbum.split('/');
		//not an album bit a single shared image, go back to the root
		if (OC.currentUser && albumParts.length === 1 && albumParts[0] !== OC.currentUser) {
			Gallery.currentAlbum = OC.currentUser;
		}
		location.hash = encodeURI(Gallery.currentAlbum);
		Thumbnail.concurrent = 3;
	};

	$(window).scroll(function () {
		Gallery.view.loadVisibleRows();
	});
});

window.onhashchange = function () {
	var album = decodeURI(location.hash).substr(1);
	if (!album) {
		album = OC.currentUser;
	}
	if (!album) {
		album = $('#gallery').data('token');
	}
	if (!Gallery.imageMap[album]) {
		Slideshow.end();
		album = decodeURIComponent(album);
		if (Gallery.currentAlbum !== album) {
			Gallery.view.viewAlbum(album);
		}
	} else {
		Gallery.view.viewAlbum(OC.dirname(album));
		$('#gallery').find('a.image[data-path="' + album + '"]').click();
	}
};
