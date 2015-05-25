/* global OC ,OCA, $, oc_requesttoken, SlideShow */
var galleryFileAction = {
	config: null,
	mediaTypes: {},

	/**
	 * Builds a URL pointing to one of the app's controllers
	 *
	 * @param {string} endPoint
	 * @param {undefined|string} path
	 * @param params
	 *
	 * @returns {string}
	 */
	buildGalleryUrl: function (endPoint, path, params) {
		var extension = '';
		var tokenElement = $('#sharingToken');
		var token = (tokenElement.val()) ? tokenElement.val() : false;
		if (token) {
			params.token = token;
			extension = '.public';
		}
		var query = OC.buildQueryString(params);
		return OC.generateUrl('apps/galleryplus/' + endPoint + extension + path, null) + '?' +
			query;
	},

	/**
	 * Registers a file action for each media type
	 *
	 * @param mediaTypes
	 */
	register: function (mediaTypes) {
		//console.log("enabledPreviewProviders: ", mediaTypes);
		if (mediaTypes) {
			galleryFileAction.mediaTypes = mediaTypes;
		}

		// We only want to create slideshows for supported media types
		for (var i = 0, keys = Object.keys(galleryFileAction.mediaTypes); i < keys.length; i++) {
			// Each click handler gets the same function and images array and
			// is responsible to load the slideshow
			OCA.Files.fileActions.register(keys[i], 'View', OC.PERMISSION_READ, '',
				galleryFileAction.onView);
			OCA.Files.fileActions.setDefault(keys[i], 'View');
		}
	},

	/**
	 * Builds an array containing all the images we can show in the slideshow
	 *
	 * @param {string} filename
	 * @param context
	 */
	onView: function (filename, context) {
		var imageUrl, downloadUrl;
		var fileList = context.fileList;
		var files = fileList.files;
		var start = 0;
		var images = [];
		var dir = context.dir + '/';
		var width = Math.floor($(window).width() * window.devicePixelRatio);
		var height = Math.floor($(window).height() * window.devicePixelRatio);

		/* Find value of longest edge. */
		var longEdge = Math.max( width, height );

		/* Find the next larger image size. */
		if ( longEdge % 100 !== 0 ){
			longEdge = ( longEdge + 100 ) - ( longEdge % 100 );
		}

		for (var i = 0; i < files.length; i++) {
			var file = files[i];
			// We only add images to the slideshow if we think we'll be able
			// to generate previews for this media type
			if (galleryFileAction.mediaTypes[file.mimetype]) {
				/* jshint camelcase: false */
				var params = {
					width: longEdge,
					height: longEdge,
					c: file.etag,
					requesttoken: oc_requesttoken
				};
				imageUrl = galleryFileAction.buildGalleryUrl('preview', '/' + file.id, params);
				downloadUrl = imageUrl + '&download';

				images.push({
					name: file.name,
					path: dir + file.name,
					fileId: file.id,
					mimeType: file.mimetype,
					url: imageUrl,
					downloadUrl: downloadUrl
				});
			}
		}
		for (i = 0; i < images.length; i++) {
			//console.log("Images in the slideshow : ", images[i]);
			if (images[i].name === filename) {
				start = i;
			}
		}
		var slideShow = new SlideShow($('#slideshow'), images);
		slideShow.onStop = function () {
			history.replaceState('', document.title,
				window.location.pathname + window.location.search);
		};
		slideShow.init();
		slideShow.show(start);
	}

};

$(document).ready(function () {
	// Deactivates fileaction on public preview page
	if ($('#imgframe').length > 0) {
		return true;
	}

	var url = galleryFileAction.buildGalleryUrl('config', '', {});
	$.getJSON(url).then(function (config) {
		if (config) {
			galleryFileAction.config = config;
		}
		url = galleryFileAction.buildGalleryUrl('mediatypes', '', {slideshow: 1});
		// We're asking for a list of supported media types.
		// Media files are retrieved through the Files context
		$.getJSON(url, {}, galleryFileAction.register);
	});
});
