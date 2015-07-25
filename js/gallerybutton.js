/* global OC, OCA, FileList, $, t */
var GalleryButton = {};
GalleryButton.isPublic = false;
GalleryButton.button = {};
GalleryButton.url = null;

GalleryButton.onFileListUpdated = function () {
	"use strict";
	var fileList;

	if (GalleryButton.isPublic) {
		fileList = OCA.Sharing.PublicApp.fileList;
	} else {
		fileList = FileList;
	}

	GalleryButton.buildGalleryUrl(fileList.getCurrentDirectory().replace(/^\//, ''));
};

GalleryButton.buildGalleryUrl = function (dir) {
	"use strict";
	var params = {};
	var tokenPath = '';
	var sharingTokenElement = $('#sharingToken');
	var token = (sharingTokenElement.val()) ? sharingTokenElement.val() : false;
	if (token) {
		params.token = token;
		tokenPath = 's/{token}';
	}
	GalleryButton.url =
		OC.generateUrl('apps/gallery/' + tokenPath, params) + '#' + encodeURIComponent(dir);
};

$(document).ready(function () {
		"use strict";
		if ($('#body-login').length > 0) {
			return true; //deactivate on login page
		}

		if ($('#isPublic').val()) {
			GalleryButton.isPublic = true;
		}

		if ($('#filesApp').val()) {

			$('#fileList').on('updated', GalleryButton.onFileListUpdated);

			// toggle for opening shared file list as picture view
			GalleryButton.button = $('<div id="openAsFileListButton" class="button">' +
			'<img class="svg" src="' + OC.imagePath('core', 'actions/toggle-pictures.svg') + '"' +
			'alt="' + t('gallery', 'Picture view') + '"/>' +
			'</div>');

			GalleryButton.button.click(function () {
				window.location.href = GalleryButton.url;
			});

			$('#controls').prepend(GalleryButton.button);
		}
	}
);
