/* global OC, OCA, FileList, $, t */
var GalleryButton = {};
GalleryButton.isPublic = false;
GalleryButton.button = {};
GalleryButton.url = null;


GalleryButton.onFileListUpdated = function () {
	var hasImages = false;
	var fileList;
	var files;

	if (GalleryButton.isPublic) {
		fileList = OCA.Sharing.PublicApp.fileList;
		files = fileList.files;
	} else {
		fileList = FileList;
		files = fileList.files;
	}

	for (var i = 0; i < files.length; i++) {
		var file = files[i];
		if (file.isPreviewAvailable) {
			hasImages = true;
			break;
		}
	}

	if (hasImages) {
		GalleryButton.button.toggleClass('hidden', false);
		GalleryButton.buildUrl(fileList.getCurrentDirectory().replace(/^\//, ''));
	} else {
		GalleryButton.button.toggleClass('hidden', true);
	}
};

GalleryButton.buildUrl = function (dir) {
	var params = {};
	var tokenPath = '';
	var token = ($('#sharingToken').val()) ? $('#sharingToken').val() : false;
	if (token) {
		params.token = token;
		tokenPath = 'public?token={token}';
	}
	GalleryButton.url = OC.generateUrl('apps/galleryplus/' + tokenPath, params) + '#' + dir;
};


$(document).ready(function () {

		if ($('#body-login').length > 0) {
			return true; //deactivate on login page
		}

		if ($('#isPublic').val()) {
			GalleryButton.isPublic = true;
		}

		if ($('#filesApp').val()) {

			$('#fileList').on('updated', GalleryButton.onFileListUpdated);

			// toggle for opening shared file list as picture view
			GalleryButton.button = $('<div id="openAsFileListButton" class="button hidden">' +
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
