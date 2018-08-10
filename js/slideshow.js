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
/* global Gallery, Thumbnails, DOMPurify */
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
		/** {Image} */
		currentImage: null,
		errorLoadingImage: false,
		onStop: null,
		zoomablePreview: null,
		active: false,
		backgroundToggle: false,
		// We need 6 hexas for comparison reasons
		darkBackgroundColour: '#000000',
		lightBackgroundColour: '#ffffff',

		/**
		 * Initialises the slideshow
		 *
		 * @param {boolean} autoPlay
		 * @param {number} interval
		 * @param {Array} features
		 */
		init: function (autoPlay, interval, features) {
			if (features.indexOf('background_colour_toggle') > -1) {
				this.backgroundToggle = true;
			}

			return $.when(this._getSlideshowTemplate()).then(function ($tmpl) {
				// Move the slideshow outside the content so we can hide the content
				$('body').append($tmpl);
				this.container = $('#slideshow');
				this.zoomablePreviewContainer = this.container.find('.bigshotContainer');
				this.zoomablePreview = new SlideShow.ZoomablePreview(this.container);
				this.controls =
					new SlideShow.Controls(
						this,
						this.container,
						this.zoomablePreview,
						interval,
						features,
						this.restoreContent.bind(this));
				this.controls.init();

				this._initControlsAutoFader();

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
				OC.Notification.show(t('gallery', 'Error loading slideshow template'));
			});
		},

		/**
		 * Refreshes the slideshow's data
		 *
		 * @param {{name:string, url: string, path: string, fallBack: string}[]} images
		 * @param {boolean} autoPlay
		 */
		setImages: function (images, autoPlay) {
			this._hideImage();
			this.images = images;
			this.controls.update(images, autoPlay);
		},

		/**
		 * Hides the content behind the slideshow
		 *
		 * This should be called when the slideshow is shown.
		 *
		 * It hides the content (and, in the public share page, also the footer)
		 * to ensure that the body size is just the slideshow size and thus no
		 * scroll bars are shown.
		 */
		hideContent: function () {
			this._savedScrollPosition = $(window).scrollTop();

			$('#content').hide();
			$('footer').hide();
		},

		/**
		 * Shows again the content behind the slideshow
		 *
		 * This should be called when the slideshow is hidden.
		 *
		 * It restores the content hidden when calling "hideContent", including
		 * the vertical scrolling position.
		 */
		restoreContent: function () {
			$('#content').show();
			$('footer').show();

			if (this._savedScrollPosition) {
				$(window).scrollTop(this._savedScrollPosition);
			}
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
			this.hideContent();
			this.container.css('background-position', 'center');
			this._hideImage();
			this.container.find('.icon-loading-dark').show();
			var currentImageId = index;
			return this.loadImage(this.images[index]).then(function (img) {
				this.container.css('background-position', '-10000px 0');

				// check if we moved along while we were loading
				if (currentImageId === index) {
					var image = this.images[index];
					var transparent = this._isTransparent(image.mimeType);
					this.controls.showActionButtons(transparent, Gallery.token, image.permissions);
					this.errorLoadingImage = false;
					this.currentImage = img;
					img.setAttribute('alt', image.name);
					$(img).css('position', 'absolute');
					$(img).css('background-color', image.backgroundColour);
					if (transparent && this.backgroundToggle === true) {
						var $border = 30 / window.devicePixelRatio;
						$(img).css('outline', $border + 'px solid ' + image.backgroundColour);
					}

					this.zoomablePreview.startBigshot(img, this.currentImage, image.mimeType);

					this._setUrl(image.path);
					this.controls.show(currentImageId);
					this.container.find('.icon-loading-dark').hide();
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
					preview.backgroundColour = this._getBackgroundColour(image, mimeType);
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
		 * Shows a new image in the slideshow and preloads the next in the list
		 *
		 * @param {number} current
		 * @param {Object} next
		 */
		next: function (current, next) {
			this.show(current).then(function () {
				// Preloads the next image in the list
				this.loadImage(next);
			}.bind(this));
		},

		/**
		 * Determines which colour to use for the background
		 *
		 * @param {*} image
		 * @param {string} mimeType
		 *
		 * @returns {string}
		 * @private
		 */
		_getBackgroundColour: function (image, mimeType) {
			var backgroundColour = this.darkBackgroundColour;
			if (this._isTransparent(mimeType) && this._isMainlyDark(image)) {
				backgroundColour = this.lightBackgroundColour;
			}
			return backgroundColour;
		},

		/**
		 * Calculates the luminance of an image to determine if an image is mainly dark
		 *
		 * @param {*} image
		 *
		 * @returns {boolean}
		 * @private
		 */
		_isMainlyDark: function (image) {
			var isMainlyDark = false;
			var numberOfSamples = 1000; // Seems to be the sweet spot
			// The name has to be 'canvas'
			var lumiCanvas = document.createElement('canvas');

			var imgArea = image.width * image.height;
			var canArea = numberOfSamples;
			var factor = Math.sqrt(canArea / imgArea);

			var scaledWidth = factor * image.width;
			var scaledHeight = factor * image.height;
			lumiCanvas.width = scaledWidth;
			lumiCanvas.height = scaledHeight;
			var lumiCtx = lumiCanvas.getContext('2d');
			lumiCtx.drawImage(image, 0, 0, scaledWidth, scaledHeight);
			var imgData = lumiCtx.getImageData(0, 0, lumiCanvas.width, lumiCanvas.height);
			var pix = imgData.data; // pix.length will be approximately 4*numberOfSamples (for RGBA)
			var pixelArraySize = pix.length;
			var totalLuminance = 0;
			var sampleNumber = 1;
			var averageLuminance;
			var totalAlpha = 0;
			var alphaLevel;
			var red = 0;
			var green = 0;
			var blue = 0;
			var alpha = 0;
			var lum = 0;
			var alphaThreshold = 0.1;

			var sampleCounter = 0;
			var itemsPerPixel = 4; // red, green, blue, alpha
			// i += 4 because 4 colours for every pixel
			for (var i = 0, n = pixelArraySize; i < n; i += itemsPerPixel) {
				sampleCounter++;
				alpha = pix[i + 3] / 255;
				totalAlpha += alpha;
				if (Math.ceil(alpha * 100) / 100 > alphaThreshold) {
					red = pix[i];
					green = pix[i + 1];
					blue = pix[i + 2];
					// Luminance formula from
					// http://stackoverflow.com/questions/596216/formula-to-determine-brightness-of-rgb-color
					lum = (red + red + green + green + green + blue) / 6;
					//lum = (red * 0.299 + green * 0.587 + blue * 0.114 );
					totalLuminance += lum * alpha;
					sampleNumber++;
				}
			}

			// Deletes the canvas
			lumiCanvas = null;

			// Calculate the optimum background colour for this image
			averageLuminance = Math.ceil((totalLuminance / sampleNumber) * 100) / 100;
			alphaLevel = Math.ceil((totalAlpha / numberOfSamples) * 100);

			if (averageLuminance < 60 && alphaLevel < 90) {
				isMainlyDark = true;
			}

			return isMainlyDark;
		},

		/**
		 * Stops the slideshow
		 */
		stop: function () {
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
			var newBackgroundColor;

			// Grey #363636
			if (hex === this.darkBackgroundColour) {
				newBackgroundColor = this.lightBackgroundColour;
			} else {
				newBackgroundColor = this.darkBackgroundColour;
			}

			container.css('background-color', newBackgroundColor);
			if (this.backgroundToggle === true) {
				container.css('outline', $border + 'px solid ' + newBackgroundColor);
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
			this.controls.hideButton('.changeBackground');
		},

		/**
		 * Hides the error notification
		 */
		hideErrorNotification: function () {
			this.container.find('.notification').hide();
			this.container.find('.notification').html('');
		},

		/**
		 * Removes a specific button from the interface
		 *
		 * @param button
		 */
		removeButton: function (button) {
			this.controls.removeButton(button);
		},

		/**
		 * Deletes an image from the slideshow
		 *
		 * @param {object} image
		 * @param {number} currentIndex
		 */
		deleteImage: function (image, currentIndex) {
			// These are Gallery specific commands to be replaced
			// which should sit somewhere else
			if (!window.galleryFileAction) {
				delete Gallery.imageMap[image.path];
				delete Thumbnails.map[image.file];
				Gallery.albumMap[Gallery.currentAlbum].images.splice(currentIndex, 1);
				Gallery.view.init(Gallery.currentAlbum);
			}
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
		 * Simplest way to detect if image is transparent.
		 *
		 * That's very inaccurate since it doesn't include images which support transparency
		 *
		 * @param mimeType
		 * @returns {boolean}
		 * @private
		 */
		_isTransparent: function (mimeType) {
			return !(mimeType === 'image/jpeg'
				|| mimeType === 'image/x-dcraw'
				|| mimeType === 'application/font-sfnt'
				|| mimeType === 'application/x-font'
			);
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
			this.controls.hideActionButtons();
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
			// DOMPurify only works with IE10+ and we load SVGs in the IMG tag
			if (window.btoa &&
				document.implementation.hasFeature("http://www.w3.org/TR/SVG11/feature#Image",
					"1.1")) {
				var xmlHttp = new XMLHttpRequest();
				xmlHttp.open("GET", source, false);
				xmlHttp.send(null);
				if (xmlHttp.status === 200) {
					var pureSvg = DOMPurify.sanitize(xmlHttp.responseText, {ADD_TAGS: ['filter']});
					// Remove XML comment garbage left in the purified data
					var badTag = pureSvg.indexOf(']&gt;');
					var fixedPureSvg = pureSvg.substring(badTag < 0 ? 0 : 5, pureSvg.length);
					svgPreview = "data:image/svg+xml;base64," + window.btoa(fixedPureSvg);
				}
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
						var buttonsArray = [
							{
								el: '.next',
								trans: t('gallery', 'Next')
							},
							{
								el: '.play',
								trans: t('gallery', 'Play'),
								toolTip: true
							},
							{
								el: '.pause',
								trans: t('gallery', 'Pause'),
								toolTip: true
							},
							{
								el: '.previous',
								trans: t('gallery', 'Previous')
							},
							{
								el: '.exit',
								trans: t('gallery', 'Close'),
								toolTip: true
							},
							{
								el: '.downloadImage',
								trans: t('gallery', 'Download'),
								toolTip: true
							},
							{
								el: '.changeBackground',
								trans: t('gallery', 'Toggle background'),
								toolTip: true
							},
							{
								el: '.deleteImage',
								trans: t('gallery', 'Delete'),
								toolTip: true
							},
							{
								el: '.shareImage',
								trans: t('gallery', 'Share'),
								toolTip: true
							}
						];
						for (var i = 0; i < buttonsArray.length; i++) {
							var button = buttonsArray[i];

							tmplButton = template.find(button.el);
							tmplButton.val(button.trans);
							if (button.toolTip) {
								tmplButton.attr("title", button.trans);
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
