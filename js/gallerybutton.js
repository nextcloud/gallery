/**
 * Nextcloud - Gallery
 *
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Olivier Paroz <galleryapps@oparoz.com>
 *
 * @copyright Olivier Paroz 2017
 */
/* global OC, OCA, FileList, $, t */
var GalleryButton = {};
GalleryButton.isPublic = false;
GalleryButton.button = {};
GalleryButton.url = null;

/**
 * Rebuilds the Gallery URL every time the files list has changed
 */
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

/**
 * Builds the URL which will load the exact same folder in Gallery
 *
 * @param dir
 */
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

		if ($('html').is('.ie8')) {
			return true; //deactivate in IE8
		}

		if ($('#isPublic').val()) {
			GalleryButton.isPublic = true;
		}

		if ($('#filesApp').val()) {

			$('#fileList').on('updated', GalleryButton.onFileListUpdated);

			// Button for opening files list as gallery view
			GalleryButton.button =
				$('<div id="gallery-button" class="button view-switcher">' +
						'<div id="button-loading" class="hidden"></div>' +
					'<span class="icon-toggle-pictures"></span>' +
					'</div>');

			GalleryButton.button.click(function () {
				$(this).children('span').addClass('hidden');
				$(this).children('#button-loading').removeClass('hidden').addClass('icon-loading-small');
				window.location.href = GalleryButton.url;
			});

			$('#controls').append(GalleryButton.button);
		}
	}
);
