/* global SlideShow, bigshot*/
(function ($, SlideShow, bigshot) {
	"use strict";
	/**
	 * Creates a zoomable preview
	 *
	 * @param {*} container
	 * @constructor
	 */
	var ZoomablePreview = function (container) {
		this.container = container;
		this.element = this.container.get(0);
		var bigshotContainer = container.find('.bigshotContainer');
		this.bigshotElement = bigshotContainer.get(0);

		this._detectFullscreen();
		this._setupControls();

		$(window).resize(function () {
			this._zoomDecider();
		}.bind(this));
	};

	ZoomablePreview.prototype = {
		container: null,
		element: null,
		bigshotContainer: null,
		bigshotElement: null,
		zoomable: null,
		fullScreen: null,
		canFullScreen: false,
		currentImage: null,
		mimeType: null,
		maxZoom: 3,
		smallImageDimension: 200 / window.devicePixelRatio,
		smallImageScale: 2,

		/**
		 * Launches the Bigshot zoomable preview
		 *
		 * @param {*} image
		 * @param {number} currentImage
		 * @param {string} mimeType
		 */
		startBigshot: function (image, currentImage, mimeType) {
			this.currentImage = currentImage;
			this.mimeType = mimeType;
			if (this.zoomable !== null) {
				this.zoomable.dispose();
				this.zoomable = null;
			}
			var maxZoom = this.maxZoom;
			var imgWidth = image.naturalWidth / window.devicePixelRatio;
			var imgHeight = image.naturalHeight / window.devicePixelRatio;
			// Set arbitrary image dimension when we have a SVG
			if (imgWidth === 0 && mimeType === 'image/svg+xml') {
				imgWidth = 2048;
				imgHeight = 2048;
			}

			if (imgWidth < this.smallImageDimension &&
				imgHeight < this.smallImageDimension &&
				this.mimeType !== 'image/svg+xml') {
				maxZoom += 3;
				this.currentImage.isSmallImage = true;
			}
			this.zoomable = new bigshot.SimpleImage(new bigshot.ImageParameters({
				container: this.bigshotElement,
				maxZoom: maxZoom,
				minZoom: 0,
				touchUI: false,
				width: imgWidth,
				height: imgHeight
			}), image);

			// Reset our zoom based on image and window dimensions.
			this._resetZoom();

			// prevent zoom-on-doubleClick
			this.zoomable.addEventListener('dblclick', function (ie) {
				ie.preventDefault();
			});
			// Reset image position and size on orientation change
			var self = this;
			$(window).bind('orientationchange resize', function () {
				self._resetZoom();
			});
		},

		/**
		 * Resets the element for the next image to be displayed
		 */
		reset: function () {
			if (this.zoomable !== null) {
				this.zoomable.stopFlying();
				this._resetZoom();
			}
		},

		/**
		 * Throws away the zoomable preview
		 */
		stop: function () {
			if (this.fullScreen !== null) {
				this._fullScreenExit();
			}
			if (this.zoomable !== null) {
				this.zoomable.dispose();
				this.zoomable = null;
			}
		},

		/**
		 * Launches fullscreen mode if the browser supports it
		 */
		fullScreenToggle: function () {
			if (this.zoomable === null) {
				return;
			}
			if (this.fullScreen !== null) {
				this._fullScreenExit();
			} else {
				this._fullScreenStart();
			}
		},

		/**
		 * Resizes the image to its original size
		 */
		zoomToOriginal: function () {
			if (this.zoomable === null) {
				return;
			}
			if (this.currentImage.isSmallImage) {
				this.zoomable.flyTo(0, 0, this.smallImageScale, true);
			} else if ($(window).width() < this.zoomable.width ||
				$(window).height() < this.zoomable.height) {
				// The image is larger than the window.
				// Set minimum zoom and call flyZoomToFit.
				this.zoomable.setMinZoom(this.zoomable.getZoomToFitValue());
				this.zoomable.flyZoomToFit();
			} else {
				this.zoomable.setMinZoom(0);
				this.zoomable.flyTo(0, 0, 0, true);
			}
		},

		/**
		 * Fits the image in the browser window
		 */
		zoomToFit: function () {
			if (this.zoomable !== null) {
				this.zoomable.flyZoomToFit();
			}
		},

		/**
		 * Detect fullscreen capability
		 * @private
		 */
		_detectFullscreen: function () {
			this.canFullScreen = this.element.requestFullscreen !== undefined ||
				this.element.mozRequestFullScreen !== undefined ||
				this.element.webkitRequestFullscreen !== undefined ||
				this.element.msRequestFullscreen !== undefined;
		},

		/**
		 * Makes UI controls work on touchscreens. Pinch only works on iOS
		 * @private
		 */
		_setupControls: function () {
			var browser = new bigshot.Browser();
			this.container.children('input').each(function (i, e) {
				browser.registerListener(e, 'click', browser.stopEventBubblingHandler(), false);
				browser.registerListener(e, 'touchstart', browser.stopEventBubblingHandler(),
					false);
				browser.registerListener(e, 'touchend', browser.stopEventBubblingHandler(), false);
			});
		},

		/**
		 * Determines whether the image should be shown at its original size or if it should fill
		 * the screen
		 *
		 * @private
		 */
		_zoomDecider: function () {
			if (this.zoomable !== null) {
				if (this.fullScreen === null && this.mimeType !== 'image/svg+xml') {
					this.zoomToOriginal();
				} else {
					this.zoomToFit();
				}
			}
		},

		/**
		 * Resets the image to its original zoomed size
		 *
		 * @private
		 */
		_resetZoom: function () {
			if (this.zoomable === null) {
				return;
			}
			if (this.currentImage.isSmallImage) {
				this.zoomable.setZoom(this.smallImageScale, true);
			} else if ($(window).width() < this.zoomable.width ||
				$(window).height() < this.zoomable.height ||
				this.fullScreen !== null ||
				this.mimeType === 'image/svg+xml') {
				// The image is larger than the window, or we are fullScreen,
				// or this is an SVG. Set minimum zoom and call zoomToFit.
				this.zoomable.setMinZoom(this.zoomable.getZoomToFitValue());
				this.zoomable.zoomToFit();
			} else {
				// Zoom to the image size.
				this.zoomable.setMinZoom(0);
				this.zoomable.setZoom(0, true);
			}
		},

		/**
		 * Starts the fullscreen previews
		 *
		 * @private
		 */
		_fullScreenStart: function () {
			if (!this.canFullScreen) {
				return;
			}
			this.fullScreen = new bigshot.FullScreen(this.element);
			this.fullScreen.open();
			this.fullScreen.addOnClose(function () {
				this._fullScreenExit();
			}.bind(this));
		},

		/**
		 * Stops the fullscreen previews
		 *
		 * @private
		 */
		_fullScreenExit: function () {
			if (this.fullScreen === null) {
				return;
			}
			this.fullScreen.close();
			this.fullScreen = null;
			this._zoomDecider();

		}
	};

	SlideShow.ZoomablePreview = ZoomablePreview;
})(jQuery, SlideShow, bigshot);
