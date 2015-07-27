/* global oc_requesttoken, FileList, SlideShow */
(function ($, OC, OCA, oc_requesttoken) {
	"use strict";
	var galleryFileAction = {
		config: null,
		mediaTypes: {},
		scrollContainer: null,
		slideShow: null,

		/**
		 * Builds a URL pointing to one of the app's controllers
		 *
		 * @param {string} endPoint
		 * @param {undefined|string} path
		 * @param {Object} params
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
			return OC.generateUrl('apps/gallery/' + endPoint + extension + path, null) + '?' +
				query;
		},

		/**
		 * Registers a file action for each media type
		 *
		 * @param {Array} mediaTypes
		 */
		register: function (mediaTypes) {
			//console.log("enabledPreviewProviders: ", mediaTypes);
			if (mediaTypes) {
				galleryFileAction.mediaTypes = mediaTypes;
			}

			// We only want to create slideshows for supported media types
			for (var i = 0, keys = Object.keys(galleryFileAction.mediaTypes); i <
			keys.length; i++) {
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
		 * @param {Object} context
		 */
		onView: function (filename, context) {
			var imageUrl, downloadUrl;
			var fileList = context.fileList;
			var files = fileList.files;
			var start = 0;
			var images = [];
			var dir = context.dir + '/';
			var width = Math.floor(screen.width * window.devicePixelRatio);
			var height = Math.floor(screen.height * window.devicePixelRatio);

			/* Find value of longest edge. */
			var longEdge = Math.max(width, height);

			/* Find the next larger image size. */
			if (longEdge % 100 !== 0) {
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

			if ($.isEmptyObject(galleryFileAction.slideShow)) {
				galleryFileAction.slideShow = new SlideShow();
				$.when(galleryFileAction.slideShow.init(false, null))
					.then(function () {
						galleryFileAction._startSlideshow(images, start);
					});
			} else {
				galleryFileAction._startSlideshow(images, start);
			}
		},

		/**
		 * Launches the slideshow
		 *
		 * @param {{name:string, url: string, path: string, fallBack: string}[]} images
		 * @param {number} start
		 * @private
		 */
		_startSlideshow: function (images, start) {
			galleryFileAction.slideShow.setImages(images);

			var scrollTop = galleryFileAction.scrollContainer.scrollTop();
			// This is only called when the slideshow is stopped
			galleryFileAction.slideShow.onStop = function () {
				FileList.$fileList.one('updated', function () {
					galleryFileAction.scrollContainer.scrollTop(scrollTop);
				});
			};

			// Only modern browsers can manipulate history
			if (history && history.replaceState) {
				// This stores the fileslist in the history state
				var stateData = {
					dir: FileList.getCurrentDirectory()
				};
				history.replaceState(stateData, document.title, window.location);

				// This creates a new entry in history for the slideshow. It will
				// be updated as the user navigates from picture to picture
				history.pushState(null, '', '#loading');
			}

			galleryFileAction.slideShow.show(start);
		}
	};

	window.galleryFileAction = galleryFileAction;
})(jQuery, OC, OCA, oc_requesttoken);

$(document).ready(function () {
	"use strict";
	// Deactivates fileaction on public preview page
	if ($('#imgframe').length > 0) {
		return true;
	}

	window.galleryFileAction.scrollContainer = $('#app-content');
	if ($('#isPublic').val()) {
		window.galleryFileAction.scrollContainer = $(window);
	}

	// Retrieve the config as well as the list of supported media types.
	// The list of media files is retrieved when the user clicks on a row
	var url = window.galleryFileAction.buildGalleryUrl('config', '', {slideshow: 1});
	$.getJSON(url).then(function (config) {
		if (!$.isEmptyObject(config.features)) {
			window.galleryFileAction.config = config.features;
		}
		window.galleryFileAction.register(config.mediatypes);
	});
});
