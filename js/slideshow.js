/* global jQuery, OC ,OCA, $, t, oc_requesttoken, SlideShowControls, bigshot */
/**
 *
 * @param {jQuery} container
 * @param {{name:string, url: string, path: string, fallBack: string}[]} images
 * @param {int} interval
 * @param {int} maxScale
 * @constructor
 */
var SlideShow = function (container, images, interval, maxScale) {
	this.container = container;
	this.images = images;
	this.maxScale = maxScale || 1; // This should come from the configuration
	this.controls = null;
	this.imageCache = {};
	this.currentImage = null;
	this.errorLoadingImage = false;
	this.onStop = null;
	this.zoomable = null;
	this.fullScreen = null;
	this.canFullScreen = false;
	this.maxZoom = 3;
	this.smallImageDimension = 200 / window.devicePixelRatio;
	this.smallImageScale = 2;
};

SlideShow.mediaTypes = {};

SlideShow.prototype = {
	/**
	 *
	 * @param play
	 */
	init: function (play) {
		this.hideImage();
		this.bigShotSetup();

		this.controls = new SlideShowControls(this, this.container, this.images);
		this.controls.init(play);

		$(window).resize(function () {
			this.zoomDecider();
		}.bind(this));
	},

	bigShotSetup: function () {
		// Detect fullscreen capability (mobile)
		var e = this.container.get(0);
		this.canFullScreen = e.requestFullscreen !== undefined ||
		e.mozRequestFullScreen !== undefined ||
		e.webkitRequestFullscreen !== undefined ||
		e.msRequestFullscreen !== undefined;

		// makes UI controls work in mobile version. Pinch only works on iOS
		var browser = new bigshot.Browser();
		this.container.children('input').each(function (i, e) {
			browser.registerListener(e, 'click', browser.stopEventBubblingHandler(), false);
			browser.registerListener(e, 'touchstart', browser.stopEventBubblingHandler(), false);
			browser.registerListener(e, 'touchend', browser.stopEventBubblingHandler(), false);
		});
	},

	zoomDecider: function () {
		if (this.fullScreen === null && this.currentImage.mimeType !== 'image/svg+xml') {
			this.zoomToOriginal();
		} else {
			this.zoomToFit();
		}
	},

	zoomToFit: function () {
		if (this.zoomable !== null) {
			this.zoomable.flyZoomToFit();
		}
	},

	zoomToOriginal: function () {
		if (this.zoomable === null) {
			return;
		}
		if (this.currentImage.isSmallImage) {
			this.zoomable.flyTo(0, 0, this.smallImageScale, true);
		} else {
			this.zoomable.flyTo(0, 0, 0, true);
		}
	},

	resetZoom: function () {
		if (this.zoomable === null) {
			return;
		}
		if (this.currentImage.isSmallImage) {
			this.zoomable.setZoom(this.smallImageScale, true);
		} else {
			this.zoomable.setZoom(0, true);
		}
	},

	fullScreenStart: function () {
		if (!this.canFullScreen) {
			return;
		}
		this.fullScreen = new bigshot.FullScreen(this.container.get(0));
		this.fullScreen.open();
		this.fullScreen.addOnClose(function () {
			this.fullScreenExit();
		}.bind(this));
	},

	fullScreenExit: function () {
		if (this.fullScreen === null) {
			return;
		}
		this.fullScreen.close();
		this.fullScreen = null;
		this.zoomDecider();

	},

	fullScreenToggle: function () {
		if (this.zoomable === null) {
			return;
		}
		if (this.fullScreen !== null) {
			this.fullScreenExit();
		} else {
			this.fullScreenStart();
		}
	},

	/**
	 *
	 * @param index
	 *
	 * @returns {*}
	 */
	show: function (index) {
		this.hideErrorNotification();
		this.container.show();
		this.container.css('background-position', 'center');
		this.hideImage();
		var currentImageId = index;
		return this.loadImage(this.images[index]).then(function (image) {
			this.container.css('background-position', '-10000px 0');
			this.container.find('.changeBackground').show();

			// check if we moved along while we were loading
			if (currentImageId === index) {
				this.errorLoadingImage = false;
				this.currentImage = image;
				this.currentImage.mimeType = this.images[index].mimeType;
				this.container.append(image);

				image.setAttribute('alt', this.images[index].name);
				$(image).css('position', 'absolute');
				$(image).css('background-color', '#fff');
				var $border = 30 / window.devicePixelRatio;
				$(image).css('outline', $border + 'px solid #fff');

				this.startBigshot(image);

				this.setUrl(this.images[index].path);
				this.controls.show(currentImageId);
			}
		}.bind(this), function () {
			// Don't do anything if the user has moved along while we were loading as it would mess
			// up the index
			if (currentImageId === index) {
				this.errorLoadingImage = true;
				this.showErrorNotification();
				this.setUrl(this.images[index].path);
				this.images.splice(index, 1);
				this.controls.updateControls(this.images, this.errorLoadingImage);
			}

		}.bind(this));
	},

	/**
	 *
	 * @param image
	 */
	startBigshot: function (image) {
		if (this.zoomable !== null) {
			this.zoomable.dispose();
			this.zoomable = null;
		}
		var maxZoom = this.maxZoom;
		var imgWidth = image.naturalWidth / window.devicePixelRatio;
		var imgHeight = image.naturalHeight / window.devicePixelRatio;
		if (imgWidth < this.smallImageDimension && imgHeight < this.smallImageDimension) {
			maxZoom += 3;
			this.currentImage.isSmallImage = true;
		}
		this.zoomable = new bigshot.SimpleImage(new bigshot.ImageParameters({
			container: this.container.get(0),
			maxZoom: maxZoom,
			minZoom: 0,
			touchUI: false,
			width: imgWidth,
			height: imgHeight
		}), image);
		if (this.fullScreen === null && this.currentImage.mimeType !== 'image/svg+xml') {
			this.resetZoom();
		}

		// prevent zoom-on-doubleClick
		this.zoomable.addEventListener('dblclick', function (ie) {
			ie.preventDefault();
		}.bind(this));
	},

	/**
	 *
	 * @param path
	 */
	setUrl: function (path) {
		if (history && history.replaceState) {
			history.replaceState('', '', '#' + encodeURI(path));
		}
	},

	/**
	 *
	 * @param preview
	 *
	 * @returns {*}
	 */
	loadImage: function (preview) {
		var url = preview.url;
		var mimeType = preview.mimeType;

		if (!this.imageCache[url]) {
			this.imageCache[url] = new jQuery.Deferred();
			var image = new Image();

			image.onload = function () {
				if (this.imageCache[url]) {
					this.imageCache[url].resolve(image);
				}
			}.bind(this);
			image.onerror = function () {
				if (this.imageCache[url]) {
					this.imageCache[url].reject(url);
				}
			}.bind(this);
			if (mimeType === 'image/svg+xml') {
				image.src = this._getSVG(url);
			} else {
				image.src = url;
			}
		}
		return this.imageCache[url];
	},

	/**
	 *
	 * @param source
	 *
	 * @returns {*}
	 */
	_getSVG: function (source) {
		var xmlHttp = new XMLHttpRequest();
		xmlHttp.open("GET", source, false);
		xmlHttp.send(null);
		if (xmlHttp.status === 200) {
			if (xmlHttp.responseXML) {
				// Has to be base64 encoded for Firefox
				return "data:image/svg+xml;base64," + btoa(xmlHttp.responseText);
			}
			return source;
		}
		return null;
	},

	next: function () {
		if (this.zoomable !== null) {
			this.zoomable.stopFlying();
			this.resetZoom();
		}
		this.hideErrorNotification();
	},

	previous: function () {
		if (this.zoomable !== null) {
			this.zoomable.stopFlying();
			this.resetZoom();
		}
		this.hideErrorNotification();
	},

	stop: function () {
		if (this.fullScreen !== null) {
			this.fullScreenExit();
		}
		if (this.zoomable !== null) {
			this.zoomable.dispose();
			this.zoomable = null;
		}
		if (this.onStop) {
			this.onStop();
		}
	},

	hideImage: function () {
		this.container.children('img').remove();
	},

	/**
	 * Sends the current image as a download
	 *
	 * @param downloadUrl
	 *
	 * @returns {boolean}
	 */
	getImageDownload: function (downloadUrl) {
		OC.redirect(downloadUrl);
		return false;
	},

	/**
	 * Changes the colour of the background of the image
	 *
	 * @private
	 */
	toggleBackground: function () {
		var toHex = function (x) {
			return ("0" + parseInt(x).toString(16)).slice(-2);
		};
		var container = this.container.children('img');
		var rgb = container.css('background-color').match(/\d+/g);
		var hex = "#" + toHex(rgb[0]) + toHex(rgb[1]) + toHex(rgb[2]);
		var $border = 30 / window.devicePixelRatio;

		// Grey #363636
		if (hex === "#000000") {
			container.css('background-color', '#FFF');
			container.css('outline', $border + 'px solid #FFF');
		} else {
			container.css('background-color', '#000');
			container.css('outline', $border + 'px solid #000');
		}
	},

	/**
	 * Shows an error notification
	 *
	 * @private
	 */
	showErrorNotification: function () {
		this.container.find('.notification').show();
		this.container.find('.changeBackground').hide();
	},

	/**
	 * Hides the error notification
	 * @private
	 */
	hideErrorNotification: function () {
		this.container.find('.notification').hide();
	}
};

/**
 *
 * @param endPoint
 * @param path
 * @param params
 *
 * @returns {string}
 */
SlideShow.buildUrl = function (endPoint, params) {
	var extension = '';
	var token = ($('#sharingToken').val()) ? $('#sharingToken').val() : false;
	if (token) {
		params.token = token;
		extension = '.public';
	}
	var query = OC.buildQueryString(params);
	return OC.generateUrl('apps/galleryplus/' + endPoint + extension, null) + '?' + query;
};

/**
 *
 * @returns {*}
 * @private
 */
SlideShow._getSlideshowTemplate = function () {
	var defer = $.Deferred();
	if (!this.$slideshowTemplate) {
		var self = this;
		var url = OC.generateUrl('apps/galleryplus/templates/slideshow.html', null);
		url = url.replace('/index.php', '');
		$.get(url, function (tmpl) {
			var template = $(tmpl);
			var tmplButton;
			var tmplTrans;
			var buttonsArray = [
				{
					el: '.next',
					trans: 'Next'
				},
				{
					el: '.play',
					trans: 'Play'
				},
				{
					el: '.pause',
					trans: 'Pause'
				},
				{
					el: '.previous',
					trans: 'Previous'
				},
				{
					el: '.exit',
					trans: 'Close'
				},
				{
					el: '.downloadImage',
					trans: 'Download',
					toolTip: true
				},
				{
					el: '.changeBackground',
					trans: 'Toggle background',
					toolTip: true
				}
			];
			for (var i = 0; i < buttonsArray.length; i++) {
				var button = buttonsArray[i];

				tmplButton = template.find(button.el);
				tmplTrans = t('gallery', button.trans);
				tmplButton.val(tmplTrans);
				if (button.toolTip) {
					tmplButton.attr("title", tmplTrans);
				}
			}
			self.$slideshowTemplate = template;
			defer.resolve(self.$slideshowTemplate);
		})
			.fail(function () {
				defer.reject();
			});
	} else {
		defer.resolve(this.$slideshowTemplate);
	}
	return defer.promise();
};

$(document).ready(function () {
	if ($('#body-login').length > 0) {
		return true; //deactivate slideshow on login page
	}

	$.when(SlideShow._getSlideshowTemplate()).then(function ($tmpl) {
		$('body').append($tmpl); //move the slideshow outside the content so we can hide the content

		var inactiveCallback = function () {
			$('#slideshow').addClass('inactive');
		};
		var inactiveTimeout = setTimeout(inactiveCallback, 3000);

		$('#slideshow').on('mousemove touchstart', function () {
			$('#slideshow').removeClass('inactive');
			clearTimeout(inactiveTimeout);
			inactiveTimeout = setTimeout(inactiveCallback, 3000);
		});

		// replace all Owncloud svg images with png images for browser that don't support svg
		if (!OC.Util.hasSVGSupport()) {
			OC.Util.replaceSVG(this.$el);
		}

		if (OCA.Files) {
			// Don't show the download button on the "Files" slideshow
			$('#slideshow').find('.downloadImage').hide();
		}
	}).fail(function () {
		OC.Notification.show(t('core', 'Error loading slideshow template'));
	});

	if (OCA.Files && OCA.Files.fileActions) {
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
			return OCA.Files.fileActions.register(mime, 'View', OC.PERMISSION_READ, '', function (filename, context) {
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
					// We only add images to the slideshow if we can generate previews for this
					// media type
					if (file.isPreviewAvailable || file.mimetype === 'image/svg+xml') {
						var params = {
							file: dir + file.name,
							x: width,
							y: height,
							requesttoken: requestToken
						};
						imageUrl = SlideShow.buildUrl('preview', params);
						downloadUrl = SlideShow.buildUrl('download', params);

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

		var url = SlideShow.buildUrl('mediatypes', '', {slideshow: 1});
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
	}
});
