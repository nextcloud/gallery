/* global jQuery, OC ,OCA, $, t, oc_requesttoken, bigshot */
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
	this.interval = interval || 5000;
	this.maxScale = maxScale || 1; // This should come from the configuration
	this.playTimeout = 0;
	this.current = 0;
	this.imageCache = {};
	this.playing = false;
	this.progressBar = container.find('.progress');
	this.currentImage = null;
	this.errorLoadingImage = false;
	this.onStop = null;
	this.active = false;
	this.zoomable = null;
	this.fullScreen = null;
	this.canFullScreen = false;
	this.maxZoom = 3;
	this.smallImageDimension = 200 / window.devicePixelRatio;
	this.smallImageScale = 2;
};

SlideShow.prototype = {
	/**
	 *
	 * @param play
	 */
	init: function (play) {
		this.active = true;
		this.hideImage();
		this.bigShotSetup();

		// hide arrows and play/pause when only one pic
		this.container.find('.next, .previous').toggle(this.images.length > 1);
		if (this.images.length === 1) {
			this.container.find('.play, .pause').hide();
		}

		// Hide the toggle background button until we have something to show
		this.container.find('.changeBackground').hide();

		var makeCallBack = function (handler) {
			return function (evt) {
				if (!this.active) {
					return;
				}
				evt.stopPropagation();
				handler.call(this);
			}.bind(this);
		}.bind(this);

		this.buttonSetup(makeCallBack);
		this.keyCodeSetup(makeCallBack);

		$(window).resize(function () {
			this.zoomDecider();
		}.bind(this));

		if (play) {
			this.play();
		} else {
			this.pause();
		}
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

	/**
	 *
	 * @param makeCallBack
	 */
	buttonSetup: function (makeCallBack) {
		this.container.children('.next').click(makeCallBack(this.next));
		this.container.children('.previous').click(makeCallBack(this.previous));
		this.container.children('.exit').click(makeCallBack(this.stop));
		this.container.children('.pause').click(makeCallBack(this.pause));
		this.container.children('.play').click(makeCallBack(this.play));
		this.container.children('.downloadImage').click(makeCallBack(this.getImageDownload));
		this.container.children('.changeBackground').click(makeCallBack(this.toggleBackground));
		//this.container.click(makeCallBack(this.next));
	},

	/**
	 *
	 * @param makeCallBack
	 */
	keyCodeSetup: function (makeCallBack) {
		$(document).keyup(function (evt) {
			if (evt.keyCode === 27) { // esc
				makeCallBack(this.stop)(evt);
			} else if (evt.keyCode === 37) { // left
				makeCallBack(this.previous)(evt);
			} else if (evt.keyCode === 39) { // right
				makeCallBack(this.next)(evt);
			} else if (evt.keyCode === 32) { // space
				makeCallBack(this.play)(evt);
			} else if (evt.keyCode === 70) { // f (fullscreen)
				makeCallBack(this.fullScreenToggle)(evt);
			} else if (this.zoomOutKey(evt)) {
				makeCallBack(this.zoomToOriginal)(evt);
			} else if (this.zoomInKey(evt)) {
				makeCallBack(this.zoomToFit)(evt);
			}
		}.bind(this));
	},

	/**
	 *
	 * @param evt
	 *
	 * @returns {boolean}
	 */
	zoomOutKey: function (evt) {
		// zero, o or down key
		return (evt.keyCode === 48 || evt.keyCode === 96 || evt.keyCode === 79 ||
		evt.keyCode === 40);
	},

	/**
	 *
	 * @param evt
	 *
	 * @returns {boolean}
	 */
	zoomInKey: function (evt) {
		// 9, i or up key
		return (evt.keyCode === 57 || evt.keyCode === 105 || evt.keyCode === 73 ||
		evt.keyCode === 38);
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
		this.current = index;
		this.container.css('background-position', 'center');
		this.hideImage();
		return this.loadImage(this.images[index]).then(function (image) {
			this.container.css('background-position', '-10000px 0');
			this.container.find('.changeBackground').show();

			// check if we moved along while we were loading
			if (this.current === index) {
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
				if (this.playing) {
					this.setTimeout();
				}
			}
		}.bind(this), function () {
			// Don't do anything if the user has moved along while we were loading as it would mess
			// up the index
			if (this.current === index) {
				this.errorLoadingImage = true;
				this.showErrorNotification();
				this.setUrl(this.images[index].path);
				this.images.splice(index, 1);
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
				image.src = this.getSVG(url);
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
	getSVG: function (source) {
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

	setTimeout: function () {
		this.clearTimeout();
		this.playTimeout = setTimeout(this.next.bind(this), this.interval);
		this.progressBar.stop();
		this.progressBar.css('height', '6px');
		this.progressBar.animate({'height': '26px'}, this.interval, 'linear');
	},

	clearTimeout: function () {
		if (this.playTimeout) {
			clearTimeout(this.playTimeout);
		}
		this.progressBar.stop();
		this.progressBar.css('height', '6px');
		this.playTimeout = 0;
	},

	play: function () {
		this.playing = true;
		this.container.find('.pause').show();
		this.container.find('.play').hide();
		this.setTimeout();
	},

	pause: function () {
		this.playing = false;
		this.container.find('.pause').hide();
		this.container.find('.play').show();
		this.clearTimeout();
	},

	next: function () {
		if (this.zoomable !== null) {
			this.zoomable.stopFlying();
			this.resetZoom();
		}
		this.hideErrorNotification();
		if (this.errorLoadingImage) {
			this.current -= 1;
		}
		this.current = (this.current + 1) % this.images.length;
		var next = (this.current + 1) % this.images.length;
		this.show(this.current).then(function () {
			// preload the next image
			this.loadImage(this.images[next]);
		}.bind(this));
	},

	previous: function () {
		if (this.zoomable !== null) {
			this.zoomable.stopFlying();
			this.resetZoom();
		}
		this.hideErrorNotification();
		this.current = (this.current - 1 + this.images.length) % this.images.length;
		var previous = (this.current - 1 + this.images.length) % this.images.length;
		this.show(this.current).then(function () {
			// preload the next image
			this.loadImage(this.images[previous]);
		}.bind(this));
	},

	stop: function () {
		if (this.fullScreen !== null) {
			this.fullScreenExit();
		}
		this.clearTimeout();
		this.container.hide();
		if (this.zoomable !== null) {
			this.zoomable.dispose();
			this.zoomable = null;
		}
		this.active = false;
		if (this.onStop) {
			this.onStop();
		}
	},

	hideImage: function () {
		this.container.children('img').remove();
	},

	togglePlay: function () {
		if (this.playing) {
			this.pause();
		} else {
			this.play();
		}
	},

	/**
	 *
	 * @returns {boolean}
	 */
	getImageDownload: function () {
		OC.redirect(this.images[this.current].downloadUrl);
		return false;
	},

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

	showErrorNotification: function () {
		this.container.find('.notification').show();
		this.container.find('.changeBackground').hide();
	},

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

		var url = SlideShow.buildUrl('mimetypes', {});
		// We're asking for a list of supported mimes. Images are given through the context
		$.getJSON(url).then(function (supportedMimes) {

			//console.log("enabledPreviewProviders: ", supportedMimes);

			// We only want to create slideshows for supported media types
			for (var m = 0; m < supportedMimes.length; ++m) {
				var mime = supportedMimes[m];
				// Each click handler gets the same function and images array and is responsible to
				// load the slideshow
				prepareFileActions(mime);
				OCA.Files.fileActions.setDefault(mime, 'View');
			}
		});
	}
});
