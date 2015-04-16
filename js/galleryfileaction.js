/* global jQuery, OC ,OCA, $, t, oc_requesttoken, SlideShow */
$(document).ready(function () {
	// This is still required in OC8
	var requestToken;
	if ($('#filesApp').val() && $('#isPublic').val()) {
		// That's the only way to get one with the broken template
		requestToken = $('#publicUploadRequestToken').val();
	} else if ($('#gallery').data('requesttoken')) {
		requestToken = $('#gallery').data('requesttoken');
	} else {
		requestToken = oc_requesttoken;
	}
	$(document).on('ajaxSend', function (elm, xhr) {
		xhr.setRequestHeader('requesttoken', requestToken);
	});

	var prepareFileActions = function (mime) {
		return OCA.Files.fileActions.register(mime, 'View', OC.PERMISSION_READ, '',
			function (filename, context) {
				var imageUrl, downloadUrl;
				var fileList = context.fileList;
				var files = fileList.files;
				var start = 0;
				var images = [];
				var dir = context.dir + '/';
				var width = Math.floor($(window).width() * window.devicePixelRatio);
				var height = Math.floor($(window).height() * window.devicePixelRatio);

				for (var i = 0; i < files.length; i++) {
					var file = files[i];
					// We only add images to the slideshow if we think we'll be able
					// to generate previews for this media type
					if (file.isPreviewAvailable || file.mimetype === 'image/svg+xml') {
						var params = {
							file: dir + file.name,
							x: width,
							y: height,
							requesttoken: requestToken
						};
						imageUrl = SlideShow.buildGalleryUrl('preview', '', params);
						downloadUrl = SlideShow.buildGalleryUrl('download', '', params);

						images.push({
							name: file.name,
							path: dir + file.name,
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
					location.hash = '';
				};
				slideShow.init();
				slideShow.show(start);
			});
	};

	var url = SlideShow.buildGalleryUrl('mediatypes', '', {slideshow: 1});
	// We're asking for a list of supported media types. Media files are retrieved through the
	// context
	$.getJSON(url).then(function (mediaTypes) {
		//console.log("enabledPreviewProviders: ", mediaTypes);
		SlideShow.mediaTypes = mediaTypes;

		// We only want to create slideshows for supported media types
		for (var i = 0, keys = Object.keys(mediaTypes); i < keys.length; i++) {
			// Each click handler gets the same function and images array and
			// is responsible to load the slideshow
			prepareFileActions(keys[i]);
			OCA.Files.fileActions.setDefault(keys[i], 'View');
		}
	});
});