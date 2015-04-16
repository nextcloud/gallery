/* global OC, $, _, Gallery */
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

		$.getJSON(Gallery.buildGalleryUrl('mediatypes', '', {}))
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