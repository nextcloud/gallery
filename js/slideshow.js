/* global jQuery, OC, OCA, $, t */
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
};

SlideShow.prototype = {
	controls: null,
	imageCache: {},
	currentImage: null,
	errorLoadingImage: false,
	onStop: null,
	zoomablePreview: null,

	/**
	 * Initialises the slideshow
	 *
	 * @param play
	 */
	init: function (play) {
		this.hideImage();
		this.zoomablePreview = new SlideShow.ZoomablePreview(this.container);
		this.controls =
			new SlideShow.Controls(this, this.container, this.zoomablePreview, this.images);
		this.controls.init(play);
	},

	/**
	 * Launches the slideshow
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
		return this.loadImage(this.images[index]).then(function (img) {
			this.container.css('background-position', '-10000px 0');
			this.container.find('.changeBackground').show();

			// check if we moved along while we were loading
			if (currentImageId === index) {
				var image = this.images[index];
				this.errorLoadingImage = false;
				this.currentImage = img;
				this.currentImage.mimeType = image.mimeType;
				this.container.append(img);

				var backgroundColour = '#fff';
				if (this.currentImage.mimeType === 'image/jpeg' ||
					this.currentImage.mimeType === 'image/x-dcraw') {
					backgroundColour = '#000';
				}
				img.setAttribute('alt', image.name);
				$(img).css('position', 'absolute');
				$(img).css('background-color', backgroundColour);
				var $border = 30 / window.devicePixelRatio;
				$(img).css('outline', $border + 'px solid ' + backgroundColour);

				this.zoomablePreview.startBigshot(img, this.currentImage);

				this.setUrl(image.path);
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
	 * Changes the browser Url, based on the current image
	 *
	 * @param path
	 */
	setUrl: function (path) {
		if (history && history.replaceState) {
			history.replaceState('', '', '#' + encodeURI(path));
		}
	},

	/**
	 * Loads the image to show in the slideshow and preloads the next one
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
	 * Retrieves an SVG
	 *
	 * An SVG can't be simply attached to a src attribute like a bitmap image
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
		this.hideErrorNotification();
	},

	previous: function () {
		this.hideErrorNotification();
	},

	stop: function () {
		if (this.onStop) {
			this.onStop();
		}
	},

	/**
	 * Hides the current image (before loading the next)
	 */
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
 * Retrieves the slideshow's template
 *
 * @returns {*}
 * @private
 */
SlideShow.getSlideshowTemplate = function () {
	var defer = $.Deferred();
	if (!this.$slideshowTemplate) {
		var self = this;
		var url = OC.generateUrl('apps/galleryplus/slideshow', null);
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
	// Deactivates slideshow on login page
	if ($('#body-login').length > 0) {
		return true;
	}
	// Deactivates slideshow on public preview page
	if ($('#imgframe').length > 0) {
		return true;
	}
	
	$.when(SlideShow.getSlideshowTemplate()).then(function ($tmpl) {
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
});
