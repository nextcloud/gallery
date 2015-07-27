/* global Gallery */
(function ($, OC, OCA, t) {
	"use strict";
	/**
	 * Slideshow featuring zooming
	 *
	 * @constructor
	 */
	var SlideShow = function () {
	};

	SlideShow.prototype = {
		slideshowTemplate: null,
		container: null,
		zoomablePreviewContainer: null,
		controls: null,
		imageCache: {},
		currentImage: null,
		errorLoadingImage: false,
		onStop: null,
		zoomablePreview: null,
		active: false,

		/**
		 * Initialises the slideshow
		 *
		 * @param {bool} autoPlay
		 * @param {number} interval
		 */
		init: function (autoPlay, interval) {
			// FIXME: This should come from the configuration
			/**@param {int} maxScale*/
			this.maxScale = 1;

			return $.when(this._getSlideshowTemplate()).then(function ($tmpl) {
				// Move the slideshow outside the content so we can hide the content
				$('body').append($tmpl);
				this.container = $('#slideshow');
				this.zoomablePreviewContainer = this.container.find('.bigshotContainer');
				this.zoomablePreview = new SlideShow.ZoomablePreview(this.container);
				this.controls =
					new SlideShow.Controls(this, this.container, this.zoomablePreview, interval);
				this.controls.init();

				this._initControlsAutoFader();

				// Replace all Owncloud svg images with png images for ancient browsers
				if (!OC.Util.hasSVGSupport()) {
					OC.Util.replaceSVG(this.$el);
				}

				// Don't show the download button on the "Files" slideshow
				if (OCA.Files) {
					this.container.find('.downloadImage').hide();
				}

				// Only modern browsers can manipulate history
				if (history && history.pushState) {
					// Stop the slideshow when backing out.
					$(window).bind('popstate.slideshow', function () {
						if (this.active === true) {
							this.active = false;
							this.controls.stop();
						}
					}.bind(this));
				}
			}.bind(this)).fail(function () {
				OC.Notification.show(t('core', 'Error loading slideshow template'));
			});
		},

		/**
		 * Refreshes the slideshow's data
		 *
		 * @param {{name:string, url: string, path: string, fallBack: string}[]} images
		 * @param {bool} autoPlay
		 */
		setImages: function (images, autoPlay) {
			this._hideImage();
			this.images = images;
			this.controls.update(images, autoPlay);
		},

		/**
		 * Launches the slideshow
		 *
		 * @param {number} index
		 *
		 * @returns {*}
		 */
		show: function (index) {
			this.hideErrorNotification();
			this.active = true;
			this.container.show();
			this.container.css('background-position', 'center');
			$('html').css('overflow-y', 'hidden');
			this._hideImage();
			var currentImageId = index;
			return this.loadImage(this.images[index]).then(function (img) {
				this.container.css('background-position', '-10000px 0');
				this.container.find('.changeBackground').show();

				// check if we moved along while we were loading
				if (currentImageId === index) {
					var image = this.images[index];
					this.errorLoadingImage = false;
					this.currentImage = img;

					var backgroundColour = '#fff';
					if (image.mimeType === 'image/jpeg' ||
						image.mimeType === 'image/x-dcraw') {
						backgroundColour = '#000';
					}
					img.setAttribute('alt', image.name);
					$(img).css('position', 'absolute');
					$(img).css('background-color', backgroundColour);
					var $border = 30 / window.devicePixelRatio;
					$(img).css('outline', $border + 'px solid ' + backgroundColour);

					// We cannot use nice things on IE8
					if ($('html').is('.ie8')) {
						$(img).addClass('scale')
							.attr('data-scale', 'best-fit-down')
							.attr('data-align', 'center');
						this.zoomablePreviewContainer.append(img);
						$(img).imageScale();
					} else {
						this.zoomablePreview.startBigshot(img, this.currentImage, image.mimeType);
					}

					this._setUrl(image.path);
					this.controls.show(currentImageId);
				}
			}.bind(this), function () {
				// Don't do anything if the user has moved along while we were loading as it would
				// mess up the index
				if (currentImageId === index) {
					this.errorLoadingImage = true;
					this.showErrorNotification(null);
					this._setUrl(this.images[index].path);
					this.images.splice(index, 1);
					this.controls.updateControls(this.images, this.errorLoadingImage);
				}
			}.bind(this));
		},

		/**
		 * Loads the image to show in the slideshow and preloads the next one
		 *
		 * @param {Object} preview
		 *
		 * @returns {*}
		 */
		loadImage: function (preview) {
			var url = preview.url;
			var mimeType = preview.mimeType;

			if (!this.imageCache[url]) {
				this.imageCache[url] = new $.Deferred();
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
				if (mimeType === 'image/svg+xml' &&
					!document.implementation.hasFeature("http://www.w3.org/TR/SVG11/feature#Image",
						"1.1")) {
					image.src = this._getSVG(url);
				} else {
					image.src = url;
				}
			}
			return this.imageCache[url];
		},

		/**
		 * Stops the slideshow
		 */
		stop: function () {
			$('html').css('overflow-y', 'scroll');
			this.active = false;
			this.images = null;
			this._hideImage();
			if (this.onStop) {
				this.onStop();
			}
		},

		/**
		 * Sends the current image as a download
		 *
		 * @param {string} downloadUrl
		 *
		 * @returns {boolean}
		 */
		getImageDownload: function (downloadUrl) {
			OC.redirect(downloadUrl);
			return false;
		},

		/**
		 * Changes the colour of the background of the image
		 */
		toggleBackground: function () {
			var toHex = function (x) {
				return ("0" + parseInt(x).toString(16)).slice(-2);
			};
			var container = this.zoomablePreviewContainer.children('img');
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
		 * @param {string} message
		 */
		showErrorNotification: function (message) {
			if ($.isEmptyObject(message)) {
				message = t('gallery',
					'<strong>Error!</strong> Could not generate a preview of this file.<br>' +
					'Please go to the next slide while we remove this image from the slideshow');
			}
			this.container.find('.notification').html(message);
			this.container.find('.notification').show();
			this.container.find('.changeBackground').hide();
		},

		/**
		 * Hides the error notification
		 */
		hideErrorNotification: function () {
			this.container.find('.notification').hide();
			this.container.find('.notification').html('');
		},

		/**
		 * Automatically fades the controls after 3 seconds
		 *
		 * @private
		 */
		_initControlsAutoFader: function () {
			var inactiveCallback = function () {
				this.container.addClass('inactive');
			}.bind(this);
			var inactiveTimeout = setTimeout(inactiveCallback, 3000);

			this.container.on('mousemove touchstart', function () {
				this.container.removeClass('inactive');
				clearTimeout(inactiveTimeout);
				inactiveTimeout = setTimeout(inactiveCallback, 3000);
			}.bind(this));
		},

		/**
		 * Changes the browser Url, based on the current image
		 *
		 * @param {string} path
		 * @private
		 */
		_setUrl: function (path) {
			if (history && history.replaceState) {
				history.replaceState('', '', '#' + encodeURI(path));
			}
		},

		/**
		 * Hides the current image (before loading the next)
		 *
		 * @private
		 */
		_hideImage: function () {
			this.zoomablePreviewContainer.empty();
		},

		/**
		 * Retrieves an SVG
		 *
		 * An SVG can't be simply attached to a src attribute like a bitmap image
		 *
		 * @param {string} source
		 *
		 * @returns {*}
		 * @private
		 */
		_getSVG: function (source) {
			var svgPreview = null;
			if (window.btoa) {
				var xmlHttp = new XMLHttpRequest();
				xmlHttp.open("GET", source, false);
				xmlHttp.send(null);
				if (xmlHttp.status === 200) {
					if (xmlHttp.responseXML) {
						// Has to be base64 encoded for Firefox
						svgPreview =
							"data:image/svg+xml;base64," + window.btoa(xmlHttp.responseText);
					} else {
						svgPreview = source;
					}
				}
			} else {
				// This is exclusively for IE8
				var message = t('gallery',
					"<strong>Error!</strong> Your browser can't display SVG files.<br>" +
					"Please use a more modern alternative");
				this.showErrorNotification(message);
				svgPreview = '/core/img/filetypes/image-vector.png';
			}

			return svgPreview;
		},

		/**
		 * Retrieves the slideshow's template
		 *
		 * @returns {*}
		 * @private
		 */
		_getSlideshowTemplate: function () {
			var defer = $.Deferred();
			if (!this.$slideshowTemplate) {
				var self = this;
				var url = OC.generateUrl('apps/gallery/slideshow', null);
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
		}
	};

	window.SlideShow = SlideShow;
})(jQuery, OC, OCA, t);
